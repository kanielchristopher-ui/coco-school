<?php
declare(strict_types=1);

use CocoCore\Events\DomainEvent;
use CocoCore\Events\EventDispatcher;
use CocoCore\Exceptions\BusinessRuleException;
use CocoCore\Exceptions\NotFoundException;
use CocoCore\Exceptions\PermissionDeniedException;
use CocoCore\Exceptions\ValidationException;
use CocoCore\Modules\Finance\FeeStatusCalculator;
use CocoCore\Modules\Finance\PaymentService;
use CocoCore\Security\Actor;
use CocoCore\Security\AuthorizationService;

return function (): void {
    T::same('statut pending', 'pending', FeeStatusCalculator::status(100, 0));
    T::same('statut partial', 'partial', FeeStatusCalculator::status(100, 40));
    T::same('statut paid', 'paid', FeeStatusCalculator::status(100, 100));
    T::same('solde', 60.0, FeeStatusCalculator::remaining(100, 40));
    T::same('solde jamais négatif', 0.0, FeeStatusCalculator::remaining(100, 120));

    $ledger = new FakeLedger();
    $ledger->fees[7] = ['id' => 7, 'student_id' => 3, 'class_id' => 4, 'fee_type' => 'Minerval', 'amount' => 100.0, 'month' => null, 'status' => 'pending'];
    $audit = new FakeAudit();
    $tx = new FakeTx();
    $events = new EventDispatcher();
    $seen = [];
    $events->listen('payment.recorded', function (DomainEvent $e) use (&$seen) { $seen[] = $e; });
    $auth = new AuthorizationService(new FakePermissions(['cashier' => ['fees.manage'], 'teacher' => ['dashboard.view']]));
    $svc = new PaymentService($ledger, $auth, $tx, $events, $audit);
    $cashier = new Actor(14, 'cashier');

    $r = $svc->collect($cashier, 7, 40.0, 'cash', '2026-10-05');
    T::same('premier paiement : reçu', 'RCT-2026-0001', $r['receipt_no']);
    T::same('premier paiement : statut partial', 'partial', $r['status']);
    T::same('premier paiement : solde 60', 60.0, $r['remaining']);
    T::same('une transaction', 1, $tx->runs);
    T::same('audit enregistré', 'collect', $audit->entries[0]['action']);
    T::same('événement publié', 1, count($seen));

    $r2 = $svc->collect($cashier, 7, 60.0, 'mobile_money', '2026-10-06');
    T::same('solde soldé : paid', 'paid', $r2['status']);
    T::same('statut du frais mis à jour', 'paid', $ledger->fees[7]['status']);
    T::throws('déjà payé refusé', BusinessRuleException::class, fn() => $svc->collect($cashier, 7, 1.0, 'cash'));

    $ledger->fees[8] = ['id' => 8, 'student_id' => 4, 'class_id' => 4, 'fee_type' => 'Examen', 'amount' => 20.0, 'month' => null, 'status' => 'pending'];
    T::throws('montant > solde refusé', BusinessRuleException::class, fn() => $svc->collect($cashier, 8, 25.0, 'cash'));
    T::throws('montant nul refusé', ValidationException::class, fn() => $svc->collect($cashier, 8, 0.0, 'cash'));
    T::throws('mode invalide refusé', ValidationException::class, fn() => $svc->collect($cashier, 8, 5.0, 'troc'));
    T::throws('date invalide refusée', ValidationException::class, fn() => $svc->collect($cashier, 8, 5.0, 'cash', '2026-02-31'));
    T::throws('frais inconnu', NotFoundException::class, fn() => $svc->collect($cashier, 99, 5.0, 'cash'));
    T::throws('enseignant sans permission', PermissionDeniedException::class, fn() => $svc->collect(new Actor(9, 'teacher'), 8, 5.0, 'cash'));
    T::same('aucun encaissement créé par les refus', 2, count($ledger->collections));
};

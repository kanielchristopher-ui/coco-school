<?php
declare(strict_types=1);

namespace CocoCore\Modules\Finance;

use CocoCore\Contracts\AuditLogInterface;
use CocoCore\Contracts\EventDispatcherInterface;
use CocoCore\Contracts\FeeLedgerRepositoryInterface;
use CocoCore\Contracts\LicenseGuardInterface;
use CocoCore\Contracts\TransactionInterface;
use CocoCore\Events\DomainEvent;
use CocoCore\Exceptions\BusinessRuleException;
use CocoCore\Exceptions\NotFoundException;
use CocoCore\Exceptions\ValidationException;
use CocoCore\Security\Actor;
use CocoCore\Security\AuthorizationService;

/** Encaissement d'un frais d'élève : validation, autorisation, transaction, reçu, statut, audit, événement. */
final class PaymentService
{
    public const METHODS = ['cash', 'bank_transfer', 'cheque', 'mobile_money', 'card', 'online'];

    public function __construct(
        private FeeLedgerRepositoryInterface $ledger,
        private AuthorizationService $authorization,
        private TransactionInterface $tx,
        private EventDispatcherInterface $events,
        private AuditLogInterface $audit,
        private ?LicenseGuardInterface $license = null
    ) {
    }

    /**
     * @return array{collection_id:int,receipt_no:string,remaining:float,status:string}
     */
    public function collect(Actor $actor, int $feeId, float $amount, string $method, ?string $date = null, ?string $notes = null): array
    {
        $this->authorization->authorize($actor, 'fees.manage');
        $this->license?->requireWrite(null, 'fees');

        $errors = [];
        if ($amount <= 0) {
            $errors['amount'] = 'Le montant doit être supérieur à zéro.';
        }
        if (!in_array($method, self::METHODS, true)) {
            $errors['payment_method'] = 'Mode de paiement invalide.';
        }
        $date = $date ?: date('Y-m-d');
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            $errors['payment_date'] = 'Date invalide.';
        }
        if ($errors) {
            throw new ValidationException($errors);
        }

        $result = $this->tx->transaction(function () use ($actor, $feeId, $amount, $method, $date, $notes): array {
            $fee = $this->ledger->findStudentFee($feeId) ?? throw new NotFoundException('Frais introuvable.');
            $paid = $this->ledger->activePaid($fee);
            $remaining = FeeStatusCalculator::remaining((float) $fee['amount'], $paid);
            if ($remaining <= 0) {
                throw new BusinessRuleException('Ce frais est déjà entièrement payé.');
            }
            if ($amount > $remaining + 0.001) {
                throw new BusinessRuleException(sprintf('Le montant dépasse le solde restant dû (%.2f).', $remaining));
            }

            $receipt = $this->ledger->nextReceiptNo((int) substr($date, 0, 4));
            $id = $this->ledger->insertCollection([
                'student_id' => (int) $fee['student_id'], 'class_id' => $fee['class_id'], 'fee_type' => $fee['fee_type'],
                'amount_paid' => $amount, 'payment_date' => $date, 'payment_method' => $method,
                'collected_by' => $actor->id, 'notes' => $notes, 'receipt_no' => $receipt,
            ]);
            $newPaid = $paid + $amount;
            $status = FeeStatusCalculator::status((float) $fee['amount'], $newPaid);
            if ($status !== $fee['status']) {
                $this->ledger->setFeeStatus($feeId, $status);
            }
            $this->audit->record($actor->id, 'collect', 'fee_collection', $id, ['fee_id' => $feeId, 'amount' => $amount, 'receipt_no' => $receipt]);
            return ['collection_id' => $id, 'receipt_no' => $receipt, 'remaining' => FeeStatusCalculator::remaining((float) $fee['amount'], $newPaid), 'status' => $status,
                    'student_id' => (int) $fee['student_id']];
        });

        $this->events->dispatch(new DomainEvent('payment.recorded', $result + ['amount' => $amount, 'actor_id' => $actor->id]));
        unset($result['student_id']);
        return $result;
    }
}

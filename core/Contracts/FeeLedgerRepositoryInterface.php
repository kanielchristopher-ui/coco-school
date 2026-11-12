<?php
declare(strict_types=1);

namespace CocoCore\Contracts;

interface FeeLedgerRepositoryInterface
{
    /** @return array{id:int,student_id:int,class_id:?int,fee_type:string,amount:float,month:?string,status:string}|null */
    public function findStudentFee(int $feeId): ?array;

    /** Somme des encaissements ACTIFS (non annulés) imputables à ce frais. */
    public function activePaid(array $fee): float;

    public function nextReceiptNo(int $year): string;

    /** @param array<string,mixed> $collection @return int id */
    public function insertCollection(array $collection): int;

    public function setFeeStatus(int $feeId, string $status): void;
}

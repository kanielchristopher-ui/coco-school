<?php
declare(strict_types=1);

namespace CocoCore\Contracts;

interface TransactionInterface
{
    /**
     * Exécute $work dans une transaction : commit si OK, rollback + relance de l'exception sinon.
     * @param callable():mixed $work
     */
    public function transaction(callable $work): mixed;
}

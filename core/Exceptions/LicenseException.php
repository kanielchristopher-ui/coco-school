<?php
declare(strict_types=1);

namespace CocoCore\Exceptions;

/** Licence absente, invalide, expirée ou ne couvrant pas l'action demandée. */
final class LicenseException extends CoreException
{
    public function __construct(string $message, public readonly string $reason = 'license')
    {
        parent::__construct($message);
    }
}

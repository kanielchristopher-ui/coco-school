<?php
declare(strict_types=1);

namespace CocoCore\Exceptions;

/** Entrées invalides : errors = [champ => message]. */
final class ValidationException extends CoreException
{
    /** @param array<string,string> $errors */
    public function __construct(private array $errors, string $message = 'Données invalides.')
    {
        parent::__construct($message);
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }
}

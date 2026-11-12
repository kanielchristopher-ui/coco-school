<?php
declare(strict_types=1);

namespace CocoCore\Contracts;

interface UserRepositoryInterface
{
    /** @return array{id:int,password:string,status:string,role_slug:string,first_name:string,last_name:string}|null */
    public function findByLogin(string $login): ?array;

    public function updatePasswordHash(int $userId, string $hash): void;

    /** @return list<int> établissements gérés par l'utilisateur (vide = aucun rattachement) */
    public function establishmentIds(int $userId): array;
}

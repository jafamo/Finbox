<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Bank;
use Symfony\Component\Uid\Uuid;

interface BankRepositoryInterface
{
    public function save(Bank $bank): void;

    public function findById(Uuid $id): ?Bank;

    /**
     * @return Bank[]
     */
    public function findAll(): array;

    public function remove(Bank $bank): void;
}

<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Domain\Entity\Bank;
use App\Domain\Repository\BankRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class InMemoryBankRepository implements BankRepositoryInterface
{
    /** @var array<string, Bank> */
    private array $banks = [];

    public function save(Bank $bank): void
    {
        $this->banks[$bank->id()->toRfc4122()] = $bank;
    }

    public function findById(Uuid $id): ?Bank
    {
        return $this->banks[$id->toRfc4122()] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->banks);
    }

    public function remove(Bank $bank): void
    {
        unset($this->banks[$bank->id()->toRfc4122()]);
    }
}

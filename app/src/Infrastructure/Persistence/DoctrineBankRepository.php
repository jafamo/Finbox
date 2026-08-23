<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Bank;
use App\Domain\Repository\BankRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DoctrineBankRepository implements BankRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function save(Bank $bank): void
    {
        $this->entityManager->persist($bank);
        $this->entityManager->flush();
    }

    public function findById(Uuid $id): ?Bank
    {
        return $this->entityManager->find(Bank::class, $id);
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(Bank::class)->findAll();
    }

    public function remove(Bank $bank): void
    {
        $this->entityManager->remove($bank);
        $this->entityManager->flush();
    }
}

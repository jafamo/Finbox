<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\ImportProfile;
use App\Domain\Repository\ImportProfileRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DoctrineImportProfileRepository implements ImportProfileRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function save(ImportProfile $importProfile): void
    {
        $this->entityManager->persist($importProfile);
        $this->entityManager->flush();
    }

    public function findById(Uuid $id): ?ImportProfile
    {
        return $this->entityManager->find(ImportProfile::class, $id);
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(ImportProfile::class)->findAll();
    }

    public function remove(ImportProfile $importProfile): void
    {
        $this->entityManager->remove($importProfile);
        $this->entityManager->flush();
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\ImportProfile;
use Symfony\Component\Uid\Uuid;

interface ImportProfileRepositoryInterface
{
    public function save(ImportProfile $importProfile): void;

    public function findById(Uuid $id): ?ImportProfile;

    /**
     * @return ImportProfile[]
     */
    public function findAll(): array;

    public function remove(ImportProfile $importProfile): void;
}

<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Domain\Entity\ImportProfile;
use App\Domain\Repository\ImportProfileRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class InMemoryImportProfileRepository implements ImportProfileRepositoryInterface
{
    /** @var array<string, ImportProfile> */
    private array $importProfiles = [];

    public function save(ImportProfile $importProfile): void
    {
        $this->importProfiles[$importProfile->id()->toRfc4122()] = $importProfile;
    }

    public function findById(Uuid $id): ?ImportProfile
    {
        return $this->importProfiles[$id->toRfc4122()] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->importProfiles);
    }

    public function remove(ImportProfile $importProfile): void
    {
        unset($this->importProfiles[$importProfile->id()->toRfc4122()]);
    }
}

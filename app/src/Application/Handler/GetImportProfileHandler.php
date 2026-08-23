<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Query\GetImportProfileQuery;
use App\Domain\Entity\ImportProfile;
use App\Domain\Exception\ImportProfileNotFoundException;
use App\Domain\Repository\ImportProfileRepositoryInterface;

final class GetImportProfileHandler
{
    public function __construct(private readonly ImportProfileRepositoryInterface $importProfileRepository)
    {
    }

    public function __invoke(GetImportProfileQuery $query): ImportProfile
    {
        $importProfile = $this->importProfileRepository->findById($query->id);

        if ($importProfile === null) {
            throw ImportProfileNotFoundException::withId($query->id);
        }

        return $importProfile;
    }
}

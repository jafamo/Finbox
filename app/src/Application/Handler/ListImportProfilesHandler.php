<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Query\ListImportProfilesQuery;
use App\Domain\Repository\ImportProfileRepositoryInterface;

final class ListImportProfilesHandler
{
    public function __construct(private readonly ImportProfileRepositoryInterface $importProfileRepository)
    {
    }

    /**
     * @return \App\Domain\Entity\ImportProfile[]
     */
    public function __invoke(ListImportProfilesQuery $query): array
    {
        return $this->importProfileRepository->findAll();
    }
}

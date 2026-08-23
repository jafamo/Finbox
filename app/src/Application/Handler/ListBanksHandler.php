<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Query\ListBanksQuery;
use App\Domain\Repository\BankRepositoryInterface;

final class ListBanksHandler
{
    public function __construct(private readonly BankRepositoryInterface $bankRepository)
    {
    }

    /**
     * @return \App\Domain\Entity\Bank[]
     */
    public function __invoke(ListBanksQuery $query): array
    {
        return $this->bankRepository->findAll();
    }
}

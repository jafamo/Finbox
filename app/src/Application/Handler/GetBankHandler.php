<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Query\GetBankQuery;
use App\Domain\Entity\Bank;
use App\Domain\Exception\BankNotFoundException;
use App\Domain\Repository\BankRepositoryInterface;

final class GetBankHandler
{
    public function __construct(private readonly BankRepositoryInterface $bankRepository)
    {
    }

    public function __invoke(GetBankQuery $query): Bank
    {
        $bank = $this->bankRepository->findById($query->id);

        if ($bank === null) {
            throw BankNotFoundException::withId($query->id);
        }

        return $bank;
    }
}

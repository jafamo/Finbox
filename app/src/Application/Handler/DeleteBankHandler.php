<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\DeleteBankCommand;
use App\Domain\Exception\BankNotFoundException;
use App\Domain\Repository\BankRepositoryInterface;

final class DeleteBankHandler
{
    public function __construct(private readonly BankRepositoryInterface $bankRepository)
    {
    }

    public function __invoke(DeleteBankCommand $command): void
    {
        $bank = $this->bankRepository->findById($command->id);

        if ($bank === null) {
            throw BankNotFoundException::withId($command->id);
        }

        $this->bankRepository->remove($bank);
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\UpdateBankCommand;
use App\Domain\Entity\Bank;
use App\Domain\Exception\BankNotFoundException;
use App\Domain\Repository\BankRepositoryInterface;

final class UpdateBankHandler
{
    public function __construct(private readonly BankRepositoryInterface $bankRepository)
    {
    }

    public function __invoke(UpdateBankCommand $command): Bank
    {
        $bank = $this->bankRepository->findById($command->id);

        if ($bank === null) {
            throw BankNotFoundException::withId($command->id);
        }

        $bank->update($command->name, $command->internalCode, $command->notes);
        $this->bankRepository->save($bank);

        return $bank;
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\CreateBankCommand;
use App\Domain\Entity\Bank;
use App\Domain\Repository\BankRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class CreateBankHandler
{
    public function __construct(private readonly BankRepositoryInterface $bankRepository)
    {
    }

    public function __invoke(CreateBankCommand $command): Bank
    {
        $bank = new Bank(
            Uuid::v7(),
            $command->name,
            $command->internalCode,
            $command->notes,
        );

        $this->bankRepository->save($bank);

        return $bank;
    }
}

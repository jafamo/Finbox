<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\CreateImportProfileCommand;
use App\Domain\Entity\ImportProfile;
use App\Domain\Exception\BankNotFoundException;
use App\Domain\Repository\BankRepositoryInterface;
use App\Domain\Repository\ImportProfileRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class CreateImportProfileHandler
{
    public function __construct(
        private readonly ImportProfileRepositoryInterface $importProfileRepository,
        private readonly BankRepositoryInterface $bankRepository,
    ) {
    }

    public function __invoke(CreateImportProfileCommand $command): ImportProfile
    {
        $bank = $this->bankRepository->findById($command->bankId);

        if ($bank === null) {
            throw BankNotFoundException::withId($command->bankId);
        }

        $importProfile = new ImportProfile(
            Uuid::v7(),
            $bank,
            $command->name,
            $command->sourceFormat,
            $command->dateFormat,
            $command->decimalSeparator,
            $command->parserConfig,
            $command->encoding,
            $command->isActive,
        );

        $this->importProfileRepository->save($importProfile);

        return $importProfile;
    }
}

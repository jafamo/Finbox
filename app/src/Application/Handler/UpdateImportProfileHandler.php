<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\UpdateImportProfileCommand;
use App\Domain\Entity\ImportProfile;
use App\Domain\Exception\ImportProfileNotFoundException;
use App\Domain\Repository\ImportProfileRepositoryInterface;

final class UpdateImportProfileHandler
{
    public function __construct(private readonly ImportProfileRepositoryInterface $importProfileRepository)
    {
    }

    public function __invoke(UpdateImportProfileCommand $command): ImportProfile
    {
        $importProfile = $this->importProfileRepository->findById($command->id);

        if ($importProfile === null) {
            throw ImportProfileNotFoundException::withId($command->id);
        }

        $importProfile->update(
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

<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\DeleteImportProfileCommand;
use App\Domain\Exception\ImportProfileNotFoundException;
use App\Domain\Repository\ImportProfileRepositoryInterface;

final class DeleteImportProfileHandler
{
    public function __construct(private readonly ImportProfileRepositoryInterface $importProfileRepository)
    {
    }

    public function __invoke(DeleteImportProfileCommand $command): void
    {
        $importProfile = $this->importProfileRepository->findById($command->id);

        if ($importProfile === null) {
            throw ImportProfileNotFoundException::withId($command->id);
        }

        $this->importProfileRepository->remove($importProfile);
    }
}

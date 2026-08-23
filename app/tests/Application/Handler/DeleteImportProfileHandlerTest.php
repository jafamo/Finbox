<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateBankCommand;
use App\Application\Command\CreateImportProfileCommand;
use App\Application\Command\DeleteImportProfileCommand;
use App\Application\Handler\CreateBankHandler;
use App\Application\Handler\CreateImportProfileHandler;
use App\Application\Handler\DeleteImportProfileHandler;
use App\Domain\Exception\ImportProfileNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class DeleteImportProfileHandlerTest extends TestCase
{
    public function testDeletesExistingImportProfile(): void
    {
        $bankRepository = new InMemoryBankRepository();
        $bank = (new CreateBankHandler($bankRepository))(new CreateBankCommand('Open Bank'));

        $repository = new InMemoryImportProfileRepository();
        $importProfile = (new CreateImportProfileHandler($repository, $bankRepository))(
            new CreateImportProfileCommand($bank->id(), 'Open Bank - CSV', 'csv'),
        );

        (new DeleteImportProfileHandler($repository))(new DeleteImportProfileCommand($importProfile->id()));

        self::assertNull($repository->findById($importProfile->id()));
    }

    public function testThrowsWhenImportProfileDoesNotExist(): void
    {
        $repository = new InMemoryImportProfileRepository();
        $handler = new DeleteImportProfileHandler($repository);

        $this->expectException(ImportProfileNotFoundException::class);

        $handler(new DeleteImportProfileCommand(Uuid::v7()));
    }
}

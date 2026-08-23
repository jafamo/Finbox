<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateBankCommand;
use App\Application\Command\CreateImportProfileCommand;
use App\Application\Command\UpdateImportProfileCommand;
use App\Application\Handler\CreateBankHandler;
use App\Application\Handler\CreateImportProfileHandler;
use App\Application\Handler\UpdateImportProfileHandler;
use App\Domain\Exception\ImportProfileNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UpdateImportProfileHandlerTest extends TestCase
{
    public function testUpdatesExistingImportProfile(): void
    {
        $bankRepository = new InMemoryBankRepository();
        $bank = (new CreateBankHandler($bankRepository))(new CreateBankCommand('Open Bank'));

        $repository = new InMemoryImportProfileRepository();
        $importProfile = (new CreateImportProfileHandler($repository, $bankRepository))(
            new CreateImportProfileCommand($bank->id(), 'Open Bank - CSV', 'csv'),
        );

        $updated = (new UpdateImportProfileHandler($repository))(
            new UpdateImportProfileCommand($importProfile->id(), 'Open Bank - PDF', 'pdf_layout', 'Y-m-d', '.', null, 'UTF-8', false),
        );

        self::assertSame('Open Bank - PDF', $updated->name());
        self::assertFalse($updated->isActive());
    }

    public function testThrowsWhenImportProfileDoesNotExist(): void
    {
        $repository = new InMemoryImportProfileRepository();
        $handler = new UpdateImportProfileHandler($repository);

        $this->expectException(ImportProfileNotFoundException::class);

        $handler(new UpdateImportProfileCommand(Uuid::v7(), 'Open Bank - CSV', 'csv'));
    }
}

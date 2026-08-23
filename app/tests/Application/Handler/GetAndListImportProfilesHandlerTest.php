<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateBankCommand;
use App\Application\Command\CreateImportProfileCommand;
use App\Application\Handler\CreateBankHandler;
use App\Application\Handler\CreateImportProfileHandler;
use App\Application\Handler\GetImportProfileHandler;
use App\Application\Handler\ListImportProfilesHandler;
use App\Application\Query\GetImportProfileQuery;
use App\Application\Query\ListImportProfilesQuery;
use App\Domain\Exception\ImportProfileNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GetAndListImportProfilesHandlerTest extends TestCase
{
    public function testGetsExistingImportProfile(): void
    {
        $bankRepository = new InMemoryBankRepository();
        $bank = (new CreateBankHandler($bankRepository))(new CreateBankCommand('Open Bank'));

        $repository = new InMemoryImportProfileRepository();
        $importProfile = (new CreateImportProfileHandler($repository, $bankRepository))(
            new CreateImportProfileCommand($bank->id(), 'Open Bank - CSV', 'csv'),
        );

        $found = (new GetImportProfileHandler($repository))(new GetImportProfileQuery($importProfile->id()));

        self::assertSame($importProfile, $found);
    }

    public function testThrowsWhenImportProfileDoesNotExist(): void
    {
        $repository = new InMemoryImportProfileRepository();
        $handler = new GetImportProfileHandler($repository);

        $this->expectException(ImportProfileNotFoundException::class);

        $handler(new GetImportProfileQuery(Uuid::v7()));
    }

    public function testListsAllImportProfiles(): void
    {
        $bankRepository = new InMemoryBankRepository();
        $bank = (new CreateBankHandler($bankRepository))(new CreateBankCommand('Open Bank'));

        $repository = new InMemoryImportProfileRepository();
        $createHandler = new CreateImportProfileHandler($repository, $bankRepository);
        $createHandler(new CreateImportProfileCommand($bank->id(), 'Open Bank - CSV', 'csv'));
        $createHandler(new CreateImportProfileCommand($bank->id(), 'Open Bank - PDF', 'pdf_layout'));

        $importProfiles = (new ListImportProfilesHandler($repository))(new ListImportProfilesQuery());

        self::assertCount(2, $importProfiles);
    }
}

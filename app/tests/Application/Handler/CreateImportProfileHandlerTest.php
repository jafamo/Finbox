<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateBankCommand;
use App\Application\Command\CreateImportProfileCommand;
use App\Application\Handler\CreateBankHandler;
use App\Application\Handler\CreateImportProfileHandler;
use App\Domain\Exception\BankNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CreateImportProfileHandlerTest extends TestCase
{
    public function testCreatesImportProfileForExistingBank(): void
    {
        $bankRepository = new InMemoryBankRepository();
        $bank = (new CreateBankHandler($bankRepository))(new CreateBankCommand('Open Bank'));

        $repository = new InMemoryImportProfileRepository();
        $handler = new CreateImportProfileHandler($repository, $bankRepository);

        $importProfile = $handler(new CreateImportProfileCommand(
            $bank->id(),
            'Open Bank - extracto PDF',
            'pdf_layout',
            'Y-m-d',
            '.',
            ['row_start_pattern' => '^\\d{4}-\\d{2}-\\d{2} \\d{4}-\\d{2}-\\d{2}'],
            'UTF-8',
        ));

        self::assertSame('Open Bank - extracto PDF', $importProfile->name());
        self::assertSame($bank, $importProfile->bank());
        self::assertSame($importProfile, $repository->findById($importProfile->id()));
    }

    public function testRejectsImportProfileWithoutName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CreateImportProfileCommand(Uuid::v7(), '   ', 'csv');
    }

    public function testRejectsImportProfileWithInvalidSourceFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CreateImportProfileCommand(Uuid::v7(), 'BBVA - CSV', 'xml');
    }

    public function testRejectsImportProfileWithUnknownBank(): void
    {
        $repository = new InMemoryImportProfileRepository();
        $handler = new CreateImportProfileHandler($repository, new InMemoryBankRepository());

        $this->expectException(BankNotFoundException::class);

        $handler(new CreateImportProfileCommand(Uuid::v7(), 'BBVA - CSV', 'csv'));
    }
}

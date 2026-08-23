<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateBankCommand;
use App\Application\Command\UpdateBankCommand;
use App\Application\Handler\CreateBankHandler;
use App\Application\Handler\UpdateBankHandler;
use App\Domain\Exception\BankNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UpdateBankHandlerTest extends TestCase
{
    public function testUpdatesExistingBank(): void
    {
        $repository = new InMemoryBankRepository();
        $bank = (new CreateBankHandler($repository))(new CreateBankCommand('BBVA'));

        $updated = (new UpdateBankHandler($repository))(
            new UpdateBankCommand($bank->id(), 'BBVA España', '0182', 'Actualizado'),
        );

        self::assertSame('BBVA España', $updated->name());
        self::assertSame('0182', $updated->internalCode());
        self::assertSame('Actualizado', $updated->notes());
    }

    public function testThrowsWhenBankDoesNotExist(): void
    {
        $repository = new InMemoryBankRepository();
        $handler = new UpdateBankHandler($repository);

        $this->expectException(BankNotFoundException::class);

        $handler(new UpdateBankCommand(Uuid::v7(), 'BBVA'));
    }
}

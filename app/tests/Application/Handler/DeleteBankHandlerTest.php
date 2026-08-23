<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateBankCommand;
use App\Application\Command\DeleteBankCommand;
use App\Application\Handler\CreateBankHandler;
use App\Application\Handler\DeleteBankHandler;
use App\Domain\Exception\BankNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class DeleteBankHandlerTest extends TestCase
{
    public function testDeletesExistingBank(): void
    {
        $repository = new InMemoryBankRepository();
        $bank = (new CreateBankHandler($repository))(new CreateBankCommand('BBVA'));

        (new DeleteBankHandler($repository))(new DeleteBankCommand($bank->id()));

        self::assertNull($repository->findById($bank->id()));
    }

    public function testThrowsWhenBankDoesNotExist(): void
    {
        $repository = new InMemoryBankRepository();
        $handler = new DeleteBankHandler($repository);

        $this->expectException(BankNotFoundException::class);

        $handler(new DeleteBankCommand(Uuid::v7()));
    }
}

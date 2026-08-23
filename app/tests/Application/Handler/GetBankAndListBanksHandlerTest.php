<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateBankCommand;
use App\Application\Handler\CreateBankHandler;
use App\Application\Handler\GetBankHandler;
use App\Application\Handler\ListBanksHandler;
use App\Application\Query\GetBankQuery;
use App\Application\Query\ListBanksQuery;
use App\Domain\Exception\BankNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GetBankAndListBanksHandlerTest extends TestCase
{
    public function testGetsExistingBank(): void
    {
        $repository = new InMemoryBankRepository();
        $bank = (new CreateBankHandler($repository))(new CreateBankCommand('BBVA'));

        $found = (new GetBankHandler($repository))(new GetBankQuery($bank->id()));

        self::assertSame($bank, $found);
    }

    public function testThrowsWhenBankDoesNotExist(): void
    {
        $repository = new InMemoryBankRepository();
        $handler = new GetBankHandler($repository);

        $this->expectException(BankNotFoundException::class);

        $handler(new GetBankQuery(Uuid::v7()));
    }

    public function testListsAllBanks(): void
    {
        $repository = new InMemoryBankRepository();
        $createHandler = new CreateBankHandler($repository);
        $createHandler(new CreateBankCommand('BBVA'));
        $createHandler(new CreateBankCommand('Santander'));

        $banks = (new ListBanksHandler($repository))(new ListBanksQuery());

        self::assertCount(2, $banks);
    }
}

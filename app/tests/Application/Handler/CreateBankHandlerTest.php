<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateBankCommand;
use App\Application\Handler\CreateBankHandler;
use PHPUnit\Framework\TestCase;

final class CreateBankHandlerTest extends TestCase
{
    public function testCreatesBankWithValidName(): void
    {
        $repository = new InMemoryBankRepository();
        $handler = new CreateBankHandler($repository);

        $bank = $handler(new CreateBankCommand('BBVA', '0182', 'Cuenta familiar'));

        self::assertSame('BBVA', $bank->name());
        self::assertSame('0182', $bank->internalCode());
        self::assertSame($bank, $repository->findById($bank->id()));
    }

    public function testRejectsBankWithoutName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CreateBankCommand('   ');
    }
}

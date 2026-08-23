<?php

declare(strict_types=1);

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class DeleteBankCommand
{
    public function __construct(public readonly Uuid $id)
    {
    }
}

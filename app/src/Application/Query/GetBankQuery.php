<?php

declare(strict_types=1);

namespace App\Application\Query;

use Symfony\Component\Uid\Uuid;

final class GetBankQuery
{
    public function __construct(public readonly Uuid $id)
    {
    }
}

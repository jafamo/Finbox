<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\Uid\Uuid;

final class BankNotFoundException extends \RuntimeException
{
    public static function withId(Uuid $id): self
    {
        return new self(sprintf('No existe un banco con id "%s".', $id));
    }
}

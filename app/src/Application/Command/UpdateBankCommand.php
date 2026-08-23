<?php

declare(strict_types=1);

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class UpdateBankCommand
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly ?string $internalCode = null,
        public readonly ?string $notes = null,
    ) {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('El nombre del banco es obligatorio.');
        }
    }
}

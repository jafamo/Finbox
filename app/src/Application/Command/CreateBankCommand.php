<?php

declare(strict_types=1);

namespace App\Application\Command;

final class CreateBankCommand
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $internalCode = null,
        public readonly ?string $notes = null,
    ) {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('El nombre del banco es obligatorio.');
        }
    }
}

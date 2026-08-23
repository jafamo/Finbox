<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class ParsedMovement
{
    public function __construct(
        public readonly \DateTimeImmutable $date,
        public readonly ?\DateTimeImmutable $valueDate,
        public readonly string $originalConcept,
        public readonly float $amount,
        public readonly ?float $balance = null,
    ) {
    }
}

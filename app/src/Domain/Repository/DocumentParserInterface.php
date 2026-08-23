<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\ImportProfile;
use App\Domain\ValueObject\ParsedMovement;

interface DocumentParserInterface
{
    public function supports(ImportProfile $importProfile): bool;

    /**
     * @return ParsedMovement[]
     */
    public function parse(string $content, ImportProfile $importProfile): array;
}

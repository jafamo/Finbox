<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Enum\ImportSourceFormat;
use Symfony\Component\Uid\Uuid;

final class CreateImportProfileCommand
{
    public readonly ImportSourceFormat $sourceFormat;

    public function __construct(
        public readonly Uuid $bankId,
        public readonly string $name,
        string $sourceFormat,
        public readonly ?string $dateFormat = null,
        public readonly ?string $decimalSeparator = null,
        public readonly ?array $parserConfig = null,
        public readonly ?string $encoding = null,
        public readonly bool $isActive = true,
    ) {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('El nombre del perfil de importación es obligatorio.');
        }

        $format = ImportSourceFormat::tryFrom($sourceFormat);
        if ($format === null) {
            throw new \InvalidArgumentException(sprintf('El formato de origen "%s" no es válido.', $sourceFormat));
        }
        $this->sourceFormat = $format;
    }
}

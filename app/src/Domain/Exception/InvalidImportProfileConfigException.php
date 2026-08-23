<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidImportProfileConfigException extends \RuntimeException
{
    public static function missingColumnMappingKey(string $key): self
    {
        return new self(sprintf(
            'El parser_config del ImportProfile no incluye la clave "%s" en column_mapping.',
            $key,
        ));
    }
}

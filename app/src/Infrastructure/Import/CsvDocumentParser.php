<?php

declare(strict_types=1);

namespace App\Infrastructure\Import;

use App\Domain\Entity\ImportProfile;
use App\Domain\Enum\ImportSourceFormat;
use App\Domain\Exception\InvalidImportProfileConfigException;
use App\Domain\Repository\DocumentParserInterface;
use App\Domain\ValueObject\ParsedMovement;

final class CsvDocumentParser implements DocumentParserInterface
{
    public function supports(ImportProfile $importProfile): bool
    {
        return $importProfile->sourceFormat() === ImportSourceFormat::Csv;
    }

    public function parse(string $content, ImportProfile $importProfile): array
    {
        $config = $importProfile->parserConfig() ?? [];
        $columnMapping = $config['column_mapping'] ?? [];

        foreach (['fecha', 'concepto', 'importe'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $columnMapping)) {
                throw InvalidImportProfileConfigException::missingColumnMappingKey($requiredKey);
            }
        }

        $separator = $config['csv_separator'] ?? ',';
        $skipRows = $config['skip_rows'] ?? [];

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $movements = [];
        $rowIndex = 0;

        while (($row = fgetcsv($stream, separator: $separator, escape: '\\')) !== false) {
            if (!in_array($rowIndex, $skipRows, true)) {
                $movements[] = new ParsedMovement(
                    $this->parseDate($row[$columnMapping['fecha']], $importProfile->dateFormat()),
                    isset($columnMapping['fecha_valor'])
                        ? $this->parseDate($row[$columnMapping['fecha_valor']], $importProfile->dateFormat())
                        : null,
                    $row[$columnMapping['concepto']],
                    $this->parseAmount($row[$columnMapping['importe']], $importProfile->decimalSeparator()),
                    isset($columnMapping['saldo'])
                        ? $this->parseAmount($row[$columnMapping['saldo']], $importProfile->decimalSeparator())
                        : null,
                );
            }

            ++$rowIndex;
        }

        fclose($stream);

        return $movements;
    }

    private function parseDate(string $raw, ?string $dateFormat): \DateTimeImmutable
    {
        $format = $dateFormat ?? 'Y-m-d';
        $date = \DateTimeImmutable::createFromFormat($format, trim($raw));

        if ($date === false) {
            throw new \RuntimeException(sprintf('No se pudo interpretar la fecha "%s" con el formato "%s".', $raw, $format));
        }

        return $date;
    }

    private function parseAmount(string $raw, ?string $decimalSeparator): float
    {
        $normalized = $decimalSeparator !== null && $decimalSeparator !== '.'
            ? str_replace($decimalSeparator, '.', $raw)
            : $raw;

        return (float) $normalized;
    }
}

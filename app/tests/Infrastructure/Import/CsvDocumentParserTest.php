<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Import;

use App\Domain\Entity\Bank;
use App\Domain\Entity\ImportProfile;
use App\Domain\Enum\ImportSourceFormat;
use App\Domain\Exception\InvalidImportProfileConfigException;
use App\Infrastructure\Import\CsvDocumentParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CsvDocumentParserTest extends TestCase
{
    private const CSV_FIXTURE = <<<CSV
        fecha;concepto;importe
        21/08/2026;COMPRA MERCADONA;-84,30
        20/08/2026;NOMINA;1500,00
        CSV;

    public function testParsesValidCsvWithFullColumnMapping(): void
    {
        $movements = (new CsvDocumentParser())->parse(self::CSV_FIXTURE, $this->importProfile());

        self::assertCount(2, $movements);
        self::assertSame('COMPRA MERCADONA', $movements[0]->originalConcept);
        self::assertSame(-84.30, $movements[0]->amount);
        self::assertSame('NOMINA', $movements[1]->originalConcept);
        self::assertSame(1500.0, $movements[1]->amount);
    }

    public function testSkipsHeaderRow(): void
    {
        $movements = (new CsvDocumentParser())->parse(self::CSV_FIXTURE, $this->importProfile());

        self::assertSame('COMPRA MERCADONA', $movements[0]->originalConcept);
    }

    public function testInterpretsNonCommaSeparator(): void
    {
        $movements = (new CsvDocumentParser())->parse(self::CSV_FIXTURE, $this->importProfile(csvSeparator: ';'));

        self::assertCount(2, $movements);
    }

    public function testNormalizesDateAndAmount(): void
    {
        $movements = (new CsvDocumentParser())->parse(self::CSV_FIXTURE, $this->importProfile());

        self::assertSame('2026-08-21', $movements[0]->date->format('Y-m-d'));
        self::assertSame(-84.30, $movements[0]->amount);
    }

    public function testThrowsWhenColumnMappingIsIncomplete(): void
    {
        $importProfile = $this->importProfile(columnMapping: ['fecha' => 0, 'concepto' => 1]);

        $this->expectException(InvalidImportProfileConfigException::class);
        $this->expectExceptionMessage('importe');

        (new CsvDocumentParser())->parse(self::CSV_FIXTURE, $importProfile);
    }

    public function testSupportsOnlyCsvSourceFormat(): void
    {
        $parser = new CsvDocumentParser();

        self::assertTrue($parser->supports($this->importProfile()));
        self::assertFalse($parser->supports($this->importProfile(sourceFormat: ImportSourceFormat::PdfLayout)));
    }

    /**
     * @param array<string, int>|null $columnMapping
     */
    private function importProfile(
        ?array $columnMapping = null,
        string $csvSeparator = ';',
        ImportSourceFormat $sourceFormat = ImportSourceFormat::Csv,
    ): ImportProfile {
        return new ImportProfile(
            Uuid::v7(),
            new Bank(Uuid::v7(), 'BBVA'),
            'BBVA - CSV',
            $sourceFormat,
            'd/m/Y',
            ',',
            [
                'column_mapping' => $columnMapping ?? ['fecha' => 0, 'concepto' => 1, 'importe' => 2],
                'csv_separator' => $csvSeparator,
                'skip_rows' => [0],
            ],
        );
    }
}

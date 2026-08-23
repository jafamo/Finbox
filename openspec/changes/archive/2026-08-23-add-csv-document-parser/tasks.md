## 1. Dominio

- [x] 1.1 Crear `Domain/ValueObject/ParsedMovement.php` (inmutable: `date`, `valueDate` nullable, `originalConcept`, `amount`, `balance` nullable)
- [x] 1.2 Crear `Domain/Repository/DocumentParserInterface.php` (`supports(ImportProfile): bool`, `parse(string $content, ImportProfile): ParsedMovement[]`)
- [x] 1.3 Crear `Domain/Exception/InvalidImportProfileConfigException.php`

## 2. Infraestructura — parser CSV

- [x] 2.1 Crear `Infrastructure/Import/CsvDocumentParser.php` implementando `DocumentParserInterface`
- [x] 2.2 Implementar `supports()`: `true` cuando `ImportProfile.sourceFormat === ImportSourceFormat::Csv`
- [x] 2.3 Implementar `parse()`: validar `parser_config.column_mapping` (fecha/concepto/importe), aplicar `csv_separator` y `skip_rows`, parsear filas con `str_getcsv`
- [x] 2.4 Normalizar fecha con `date_format` y decimal con `decimal_separator` del `ImportProfile` al construir cada `ParsedMovement`

## 3. Tests

- [x] 3.1 Fixture CSV de ejemplo en `tests/Fixtures/` (o inline) con cabecera, separador `;` y formato de fecha `d/m/Y`
- [x] 3.2 Test: parseo válido con `column_mapping` completo produce los `ParsedMovement` esperados
- [x] 3.3 Test: `skip_rows` descarta las filas de cabecera indicadas
- [x] 3.4 Test: separador de campo distinto de coma se interpreta correctamente
- [x] 3.5 Test: normalización de fecha (`date_format` no ISO) y de importe (`decimal_separator` coma)
- [x] 3.6 Test: `column_mapping` incompleto lanza `InvalidImportProfileConfigException` con la clave que falta

## 4. Verificación

- [x] 4.1 Ejecutar `php-cs-fixer`/`phpcs` sobre el código nuevo (PSR-12)
- [x] 4.2 Ejecutar la suite de tests PHPUnit y confirmar que pasa

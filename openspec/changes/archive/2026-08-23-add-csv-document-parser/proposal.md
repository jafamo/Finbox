## Why

`ImportProfile` (`add-import-profile-entity`) ya guarda cómo interpretar el formato de un extracto por banco, pero nada lo consume todavía: no existe ningún parser real. Antes de abordar el caso complejo (PDF con tabla multipágina y celdas multilínea, previsto en 3 changes posteriores), hace falta fijar el contrato del puerto `DocumentParserInterface` con el caso más simple — CSV, una fila = un movimiento, sin problemas de layout — para no diseñar el puerto a ciegas y tener que romperlo cuando llegue el parser PDF.

## What Changes

- Crear el puerto `DocumentParserInterface` en `Domain/`, con un método que recibe el contenido de un documento + su `ImportProfile` y devuelve una lista de movimientos crudos extraídos.
- Crear el DTO `ParsedMovement` (`Domain/ValueObject/`) que representa un movimiento tal como sale del parser, **antes** de convertirse en la entidad `Movement` persistida: `date`, `valueDate` (nullable), `originalConcept`, `amount`, `balance` (nullable). Es un objeto de dominio inmutable, no una entidad con identidad ni persistencia propia.
- Implementar `CsvDocumentParser` (`Infrastructure/Import/`), primer adaptador de `DocumentParserInterface`, que interpreta `parser_config.column_mapping`, `parser_config.csv_separator` y `parser_config.skip_rows` de un `ImportProfile` con `source_format = csv`, y aplica `date_format`/`decimal_separator` para normalizar cada fila a un `ParsedMovement`.
- Tests unitarios del parser con fixtures CSV.

**BREAKING**: ninguno.

## Capabilities

### New Capabilities
- `csv-document-parsing`: interpretación de un fichero CSV según un `ImportProfile` para obtener la lista de movimientos crudos que contiene.

### Modified Capabilities

(ninguna)

## Impact

- **Código nuevo:** `Domain/Repository/DocumentParserInterface.php` (puerto — vive en `Domain/Repository/` junto a los demás puertos, análogo a `AiClassifierInterface`/`NotificationInterface` descritos en `docs/claude/arquitectura.md`), `Domain/ValueObject/ParsedMovement.php`, `Infrastructure/Import/CsvDocumentParser.php`.
- **Sin cambios en base de datos**: `ParsedMovement` no se persiste en este change — no hay entidad `Document` ni `Movement` todavía en código, y no se crean aquí. La persistencia real de un documento importado (crear `Document`, crear `Movement` por cada `ParsedMovement`, disparar el pipeline de clasificación) es un caso de uso completo con sus propias reglas de negocio (duplicados, estados, asociación a `BankAccount`/`Client`) y queda fuera de alcance — se abordará en un change posterior de "importar documento", una vez existan también los parsers PDF.
- **Dependencias:** ninguna nueva librería — CSV se parsea con las funciones nativas de PHP (`fgetcsv`/`str_getcsv`).
- Requiere `ImportProfile` (`add-import-profile-entity`, ya aplicado).
- Es el segundo change de una secuencia de 5; le seguirán `add-pdf-layout-text-extraction`, `add-pdf-row-reassembly` y `add-pdf-document-parser`.

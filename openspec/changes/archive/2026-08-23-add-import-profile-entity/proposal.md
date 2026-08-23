## Why

Para importar extractos bancarios (CSV o PDF) hace falta saber, por banco, cómo interpretar el formato del fichero. Hoy no existe ninguna entidad que guarde esa configuración. Antes de construir ningún parser (CSV o PDF), hace falta el modelo de datos que permita registrar una plantilla de importación por banco — sin eso, cualquier parser tendría el mapping de columnas hardcodeado en código, lo que rompería la promesa de "banco nuevo = datos nuevos, no despliegue".

El diseño de esa plantilla se ha validado contra tres documentos reales de distintos bancos (Open Bank, BBVA extracto) antes de este proposal: confirma que un extracto en tabla PDF necesita, además de lo que ya cubre `column_mapping` para CSV, un conjunto de patrones para reconocer dónde empieza cada fila lógica (las celdas de concepto envuelven a varias líneas y las cabeceras se repiten en cada página).

## What Changes

- Crear la entidad de dominio `ImportProfile` (`Domain/Entity/ImportProfile.php`) con los campos de `docs/claude/modelo-dominio.md` más un campo nuevo `source_format` (enum: `csv`, `pdf_layout`) que sustituye la asunción implícita de que todo import es CSV.
- Sustituir `column_mapping` (json) por `parser_config` (json), cuya forma depende de `source_format`:
  - `csv`: `column_mapping`, `csv_separator`, `skip_rows` (igual que en el modelo de dominio original).
  - `pdf_layout`: `row_start_pattern`, `value_date_pattern`, `ignore_patterns`, `amount_pattern`, `field_pattern` — sin lógica de parsing en este change, solo el hueco donde vivirán estos patrones cuando se implemente el parser PDF en changes posteriores.
- Crear el puerto `ImportProfileRepositoryInterface` (`Domain/Repository/`).
- Implementar el adaptador Doctrine `DoctrineImportProfileRepository` (`Infrastructure/Persistence/`).
- Casos de uso CRUD en `Application/`: crear, listar, obtener por id, actualizar, eliminar perfil de importación.
- Controller HTTP en `Infrastructure/Http/` exponiendo el CRUD como API REST, documentado con Swagger/OpenAPI.
- Colección `.http` en `http/import-profiles.http`.
- Migración de Doctrine para la tabla `import_profile`.
- Tests unitarios de los casos de uso en `tests/Application/`.

**BREAKING**: ninguno — es una entidad nueva, no hay consumidores previos de `ImportProfile`.

## Capabilities

### New Capabilities
- `import-profile-management`: alta, consulta, edición y baja de plantillas de importación (una por banco/formato) vía API REST.

### Modified Capabilities

(ninguna — no existen specs previas que cambien)

## Impact

- **Código nuevo:** `Domain/Entity/ImportProfile.php`, `Domain/Enum/ImportSourceFormat.php`, `Domain/Repository/ImportProfileRepositoryInterface.php`, `Infrastructure/Persistence/DoctrineImportProfileRepository.php`, `Application/Command|Query|Handler` para ImportProfile, `Infrastructure/Http/ImportProfileController.php`.
- **Base de datos:** nueva tabla `import_profile` (migración Doctrine), con FK a `bank`.
- **API:** nuevos endpoints REST bajo, p. ej., `/api/import-profiles`.
- **Dependencias:** ninguna nueva librería.
- **Fuera de alcance:** no se implementa ningún `DocumentParserInterface` ni lógica de parsing (CSV o PDF) en este change — es el primero de una secuencia de 5 changes; le seguirán `add-csv-document-parser`, `add-pdf-layout-text-extraction`, `add-pdf-row-reassembly` y `add-pdf-document-parser`.
- Requiere que `Bank` ya exista (`add-bank-entity`, ya aplicado).

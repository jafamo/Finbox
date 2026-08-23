## Context

`Bank` ya existe (`add-bank-entity`) y fija el patrón a seguir: entidad de dominio con atributos Doctrine, puerto de repositorio, adaptador Doctrine, casos de uso Command/Query+Handler en `Application/`, controller HTTP fino en `Infrastructure/Http/`. Este change replica ese mismo patrón para `ImportProfile`.

La necesidad de `parser_config` como json de forma variable (según `source_format`) viene de analizar documentos reales de dos bancos con tabla PDF:

- **Open Bank**: cada fila empieza con dos fechas ISO en la misma línea (`2026-08-21 2026-08-21`); el concepto envuelve a varias líneas; decimal con `.` sin símbolo de moneda.
- **BBVA**: cada fila empieza con una única fecha en su propia línea (`24/08/2026`); la fecha valor va en una línea aparte con etiqueta (`Fecha valor 22/08/2026`); decimal con `,` y símbolo `€`.

Ningún patrón fijo cubre ambos casos — de ahí que `row_start_pattern`, `value_date_pattern` y `amount_pattern` tengan que vivir en datos (por perfil), no en código.

## Goals / Non-Goals

**Goals:**
- Entidad `ImportProfile` en `Domain/Entity/` con CRUD completo vía API REST.
- `parser_config` como json flexible que ya prevé la forma que necesitarán tanto el futuro parser CSV como el futuro parser PDF, sin implementarlos.
- Documentación Swagger/OpenAPI y colección `.http` para cada endpoint.
- Test unitario de cada caso de uso en `Application/`.

**Non-Goals:**
- No se implementa `DocumentParserInterface` ni ningún adaptador de parsing (CSV o PDF) — changes posteriores.
- No se valida en este change que el contenido de `parser_config` sea sintácticamente correcto para su `source_format` (p. ej. que `row_start_pattern` sea un regex válido) — se persiste como json de forma libre; la validación semántica es responsabilidad del parser que lo consuma, cuando exista.
- No se implementa UI web (Twig) para gestionar perfiles — solo API REST.
- No se implementa `ImportProfile.encoding` como campo separado en este change si no está ya en `docs/claude/modelo-dominio.md`; se respeta el modelo de dominio documentado tal cual (`column_mapping` → `parser_config`, `date_format`, `decimal_separator`, `csv_separator` movido dentro de `parser_config.csv`, `skip_rows` movido dentro de `parser_config.csv`, `encoding`, `is_active`).

## Decisions

- **`source_format` como enum de dominio (`Domain/Enum/ImportSourceFormat.php`)**, valores `csv` y `pdf_layout`. Se añade `pdf_form` más adelante solo si el backlog de recibos-formulario se propone (ver `openspec/backlog.md`) — no se incluye ahora para no anticipar una forma de datos sin un consumidor real.
- **`parser_config` como una sola columna json**, en vez de columnas separadas por formato (`csv_config`, `pdf_config`, ...). Razón: solo un `source_format` aplica a la vez por perfil, y separar columnas obligaría a nulls cruzados sin aportar tipado real (sigue siendo json sin validar en ambos casos). La forma esperada por `source_format` se documenta como convención, no se fuerza con JSON Schema en este change.
- **`parser_config` sin validar contra un esquema formal.** Alternativa considerada: usar Symfony Validator con un esquema condicional según `source_format`. Se descarta para este change porque no hay todavía ningún parser que consuma esos campos — validar una forma que aún no se usa es prematuro y se congelaría antes de tener un caso de uso real (el parser PDF, en un change posterior, es quien mejor sabe qué necesita).
- **Relación con `Bank`**: `ImportProfile.bank_id` como FK obligatoria (un perfil pertenece siempre a un banco), igual que describe `docs/claude/modelo-dominio.md`.
- **Mapping Doctrine con atributos en la propia entidad**, UUID generado en la entidad, Command/Query+Handler por operación, controller HTTP fino — mismas decisiones que `add-bank-entity`, sin reabrir la discusión.

## Risks / Trade-offs

- [`parser_config` sin JSON Schema permite guardar configuraciones inconsistentes o incompletas para un `source_format` dado] → Mitigación: aceptable en este change porque nada lo consume todavía; se revisita cuando se implemente el primer parser (`add-csv-document-parser`), momento en el que sí importa validar la forma real que ese parser necesita.
- [Cambiar `column_mapping` por `parser_config` diverge del nombre de campo documentado en `docs/claude/modelo-dominio.md`] → Mitigación: se actualiza `docs/claude/modelo-dominio.md` como parte de la sincronización de specs de este change, documentando el nuevo nombre y su justificación (soporta CSV y PDF, no solo CSV).
- [Sin validación de unicidad de `name` por banco] → Mitigación: mismo criterio que `add-bank-entity`; fuera de alcance, se añade en un change futuro si hace falta.

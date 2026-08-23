## Context

`Document` y `Movement` no existen todavía en código — solo `Bank` e `ImportProfile`. Diseñar `DocumentParserInterface` devolviendo directamente entidades `Movement` obligaría a crear esas entidades ahora mismo, arrastrando decisiones que no tocan a este change (estados de la state machine, relación con `BankAccount`/`Client`, reglas de duplicados) solo para poder compilar el parser. Se opta por un DTO intermedio.

Este change también es el primero que produce código ejecutable de parsing — fija patrones (dónde vive el puerto, cómo se nombra el adaptador, cómo se testea) que reutilizarán los tres changes de PDF que le siguen.

## Goals / Non-Goals

**Goals:**
- Fijar la forma de `DocumentParserInterface`: entrada (contenido del documento + `ImportProfile`) y salida (`ParsedMovement[]`).
- Implementar el adaptador CSV completo y testeado, usando `parser_config` tal como quedó definido en `add-import-profile-entity`.
- Que la forma de `ParsedMovement` sea la misma que usarán los parsers PDF de los changes siguientes, para no tener que rediseñarla.

**Non-Goals:**
- No se crean las entidades `Document` ni `Movement`.
- No se implementa el caso de uso "importar documento" (crear `Document`, invocar el parser, persistir movimientos, disparar clasificación) — el parser en este change es una pieza aislada y testeada por sí misma, sin nada que la invoque desde `Application/` todavía.
- No se implementa el parser PDF (`pdf_layout`) — solo `csv`.
- No se valida `parser_config` contra un schema formal (ver `add-import-profile-entity`); el parser CSV simplemente falla con una excepción de dominio clara si `column_mapping` no tiene las claves esperadas.

## Decisions

- **`DocumentParserInterface` vive en `Domain/Repository/`**, junto a `BankRepositoryInterface`/`ImportProfileRepositoryInterface`. Alternativa considerada: `Domain/Service/` o un namespace `Domain/Port/` dedicado. Se descarta crear un namespace nuevo solo para un puerto — `arquitectura.md` ya lista `DocumentParserInterface` como uno de los "puertos principales" sin especificar subcarpeta, y `Domain/Repository/` es donde ya viven los otros puertos de esta naturaleza (aunque no sea estrictamente un repositorio, es el sitio establecido para interfaces que Infrastructure implementa).
- **Firma del puerto**:
  ```php
  interface DocumentParserInterface
  {
      public function supports(ImportProfile $importProfile): bool;

      /** @return ParsedMovement[] */
      public function parse(string $content, ImportProfile $importProfile): array;
  }
  ```
  `supports()` permite que un futuro `DocumentParserResolver` (fuera de alcance de este change) elija el adaptador correcto según `ImportProfile.sourceFormat`, sin acoplar el resolver a un enum-switch. `parse()` recibe el contenido ya leído (string), no una ruta de fichero — mantiene el parser ajeno a cómo se accede al fichero físico (coherente con que el acceso a `storage_path` pasa siempre por un adaptador de `Infrastructure/`, según `arquitectura.md`).
- **`ParsedMovement` como Value Object inmutable** en `Domain/ValueObject/`, con los campos mínimos que cualquier parser (CSV o PDF) puede producir: `date`, `valueDate` (nullable), `originalConcept`, `amount`, `balance` (nullable). No incluye `category`, `client` ni ningún campo que dependa de clasificación — eso es responsabilidad de un pipeline posterior, no del parser.
- **Excepción de dominio `InvalidImportProfileConfigException`** cuando `parser_config` no trae las claves que el parser CSV necesita (`column_mapping` con índices de `fecha`/`concepto`/`importe`), en vez de un error genérico de PHP (`undefined array key`). Da un mensaje accionable si alguien crea un `ImportProfile` mal configurado.
- **CSV parseado con `str_getcsv` nativo de PHP**, respetando `csv_separator` de `parser_config`. No se añade ninguna librería — el caso CSV es sencillo y no lo justifica.

## Risks / Trade-offs

- [Devolver un DTO (`ParsedMovement`) en vez de la entidad `Movement` obliga a un mapeo adicional cuando se implemente el caso de uso de importación] → Mitigación: aceptable — ese mapeo es responsabilidad natural del caso de uso que sí conozca `Document`/`Movement`/`BankAccount`, y mantiene el parser desacoplado de reglas de persistencia que todavía no existen.
- [`supports()` en la interfaz puede quedar sin usar hasta que exista un resolver] → Mitigación: es parte del contrato del puerto, no código muerto — cada adaptador (CSV, y los PDF que vendrán) lo implementa y se testea igual; el resolver que lo invoque es un change posterior de alcance mínimo.

## Why

Todo el modelo de dominio (`BankAccount`, `Movement`, `ImportProfile`) depende de `Bank`, pero hoy no existe ninguna entidad implementada en el proyecto — el scaffold hexagonal está vacío. `Bank` es la entidad más simple del dominio (sin relaciones entrantes, solo `id`, `name`, `internal_code`, `notes`), así que es el punto de partida natural: desbloquea `BankAccount` y el resto de entidades sin arrastrar complejidad de relaciones.

## What Changes

- Crear la entidad de dominio `Bank` (`Domain/Entity/Bank.php`) con sus campos según `docs/claude/modelo-dominio.md`.
- Crear el puerto `BankRepositoryInterface` (`Domain/Repository/`).
- Implementar el adaptador Doctrine `DoctrineBankRepository` (`Infrastructure/Persistence/`) y su mapping.
- Casos de uso CRUD en `Application/`: crear, listar, obtener por id, actualizar, eliminar banco.
- Controller HTTP en `Infrastructure/Http/` que expone el CRUD como API REST, documentado con Swagger/OpenAPI.
- Colección `.http` en `http/banks.http` con una petición por endpoint.
- Migración de Doctrine para la tabla `bank`.
- Tests unitarios de los casos de uso en `tests/Application/`.

## Capabilities

### New Capabilities
- `bank-management`: alta, consulta, edición y baja de bancos (entidad financiera) vía API REST.

### Modified Capabilities

(ninguna — no existen specs previas que cambien)

## Impact

- **Código nuevo:** `Domain/Entity/Bank.php`, `Domain/Repository/BankRepositoryInterface.php`, `Infrastructure/Persistence/DoctrineBankRepository.php`, `Application/Command|Query|Handler` para Bank, `Infrastructure/Http/BankController.php`.
- **Base de datos:** nueva tabla `bank` (migración Doctrine).
- **API:** nuevos endpoints REST bajo, p. ej., `/api/banks`.
- **Dependencias:** ninguna nueva librería; usa el stack ya definido (Symfony 8, Doctrine, PostgreSQL).
- No afecta a Telegram ni a otras entidades — es la base sobre la que se construirá `BankAccount` en un change posterior.

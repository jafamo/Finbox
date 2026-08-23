## Context

El proyecto tiene el scaffold hexagonal creado (`app/src/Domain`, `Application`, `Infrastructure`) pero sin ninguna entidad de dominio implementada todavía. `Bank` es la primera entidad real que se añade, así que este change fija también el patrón a seguir para el resto de entidades (`Client`, `BankAccount`, etc.): entidad de dominio pura, puerto de repositorio, adaptador Doctrine, casos de uso en `Application`, controller HTTP fino en `Infrastructure`.

## Goals / Non-Goals

**Goals:**
- Entidad `Bank` en `Domain/Entity/` sin dependencias de infraestructura (sin anotaciones Doctrine en la clase de dominio; mapping vía `#[ORM\Entity]`+`#[ORM\Column]` está permitido si se mantiene el atributo únicamente como metadato de persistencia, o vía mapping XML/PHP separado — se decide en la sección Decisions).
- CRUD completo (crear, listar, obtener, actualizar, eliminar) expuesto como API REST.
- Documentación Swagger/OpenAPI y colección `.http` para cada endpoint.
- Test unitario de cada caso de uso en `Application/`.

**Non-Goals:**
- No se implementa `BankAccount` ni su relación con `Bank` (change posterior).
- No se implementa UI web (Twig) para gestionar bancos — solo API REST en este change.
- No se implementa autenticación/autorización específica de este endpoint (se apoya en lo que exista a nivel de aplicación, sin añadir lógica nueva de seguridad).

## Decisions

- **Mapping Doctrine con atributos en la propia entidad de dominio.** Symfony/Doctrine moderno usa atributos PHP (`#[ORM\Entity]`, `#[ORM\Column]`) directamente en la clase. Mantener el dominio 100% ignorante de Doctrine (mapping XML separado) añade complejidad sin beneficio real en este proyecto (no hay planes de cambiar de ORM). Se acepta el atributo de Doctrine en la entidad como pragmatismo, tal como es común en apps Symfony con arquitectura hexagonal ligera; la interfaz de repositorio (`BankRepositoryInterface`) sigue viviendo en `Domain/Repository/` y es lo que separa el dominio del acceso a datos real.
- **UUID generado en la entidad, no en la base de datos.** Consistente con "Identificadores" de `arquitectura.md`: se genera antes de persistir (facilita Messenger más adelante). Se usa `Symfony\Component\Uid\Uuid`.
- **Casos de uso como Command/Query + Handler separados**, uno por operación (`CreateBankCommand`/`CreateBankHandler`, `ListBanksQuery`/`ListBanksHandler`, etc.), siguiendo la convención ya fijada en `arquitectura.md` (`Application/Command`, `Application/Query`, `Application/Handler`).
- **Controller HTTP fino**: `BankController` en `Infrastructure/Http/` solo traduce petición HTTP ↔ Command/Query, sin lógica de negocio.
- **Validación de duplicados**: no se añade regla de unicidad de `name` en este change (no está en el modelo de dominio); si se necesita, será un change posterior.

## Risks / Trade-offs

- [Atributos Doctrine en la entidad de dominio acoplan levemente el dominio a Doctrine] → Mitigación: el acoplamiento es solo a nivel de metadata/atributos, no de comportamiento; el dominio sigue siendo testable sin base de datos real (tests unitarios no instancian Doctrine).
- [Sin regla de unicidad de `name`] → Mitigación: documentado como fuera de alcance; se puede añadir con una migración y una regla de validación en un change futuro sin romper compatibilidad.

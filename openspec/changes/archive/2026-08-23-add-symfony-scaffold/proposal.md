## Why

La infraestructura Docker ya está operativa (`add-docker-infra`, mergeado), pero `app/` está vacío: no existe proyecto Symfony. Nada del resto del roadmap (entidades de dominio, bot de Telegram, clasificación IA, API web) puede empezar hasta tener un esqueleto de aplicación reproducible, con la arquitectura hexagonal definida en `docs/claude/arquitectura.md` y los bundles base ya instalados.

## What Changes

- Crear el proyecto Symfony en `app/` vía `composer create-project symfony/skeleton` (PHP 8.3+, Symfony 8.x).
- Crear la estructura de carpetas hexagonal en `app/src/`: `Domain/{Entity,ValueObject,Repository,Enum}`, `Application/{Command,Query,Handler,Service}`, `Infrastructure/{Persistence,Ai,Import,Telegram,Http,EventSubscriber}` (carpetas vacías con `.gitkeep` donde aplique, sin entidades de dominio todavía).
- Instalar y configurar bundles base:
  - `doctrine/orm` + `doctrine/doctrine-bundle` (Postgres)
  - `symfony/messenger` (transporte async ya definido en el `worker` del compose)
  - `symfony/workflow`
  - `symfony/uid` (UUIDs como PK en todas las entidades futuras)
  - `symfony/security-bundle` + `lexik/jwt-authentication-bundle` (auth JWT para la API web)
  - `symfony/monolog-bundle` (logging, especialmente para el worker de Messenger)
  - `symfony/http-client` (llamadas HTTP a Ollama y a la API de Telegram)
  - `symfony/validator`
  - `symfony/serializer`
  - `nelmio/api-doc-bundle` (Swagger/OpenAPI, obligatorio por CLAUDE.md para cada endpoint)
- Configurar `app/.env` para apuntar al Postgres y al transporte de Messenger ya definidos en `docker-compose.yml`.
- Configurar PSR-12 desde el inicio (`php-cs-fixer` o `phpcs`).
- Añadir una ruta `/health` de humo para verificar que `nginx` → `php-fpm` → Symfony funciona end-to-end sobre el compose existente.
- Crear `http/` con un fichero `.http` inicial (convención del proyecto para documentar endpoints).

Fuera de alcance: cualquier entidad de dominio (`Client`, `Bank`, `Movement`, etc.), casos de uso, integración real con Ollama/Telegram, generación de claves JWT de producción. Este change solo deja el esqueleto arrancado y verificado.

## Capabilities

### New Capabilities
- `symfony-scaffold`: esqueleto de la aplicación Symfony en `app/` — estructura hexagonal, bundles base instalados y configurados, autenticación JWT disponible, logging, y una ruta `/health` que verifica el stack completo (nginx/php-fpm/Symfony/Postgres) funcionando sobre el docker-compose existente.

### Modified Capabilities
(ninguna)

## Impact

- Ficheros nuevos: todo el árbol generado por `composer create-project` en `app/` (`composer.json`, `bin/`, `config/`, `public/`, `src/`, `app/.env`, etc.), carpetas hexagonales vacías en `src/`, `http/health.http`.
- Ficheros modificados: `app/.env` (conexión Postgres/Messenger), posiblemente `.gitignore` si `composer create-project` no lo cubre ya.
- Dependencias nuevas: todos los bundles Composer listados arriba.
- No afecta a `docker-compose.yml` ni Dockerfiles (ya definidos en `add-docker-infra`), salvo verificación de que sirven correctamente el nuevo `public/index.php`.

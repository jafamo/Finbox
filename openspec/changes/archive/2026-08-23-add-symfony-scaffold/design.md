## Context

`docker-compose.yml` ya define `postgres`, `php`, `nginx` y `worker`, con `./app` montado como bind mount en `php`/`worker` y en modo read-only en `nginx`. `app/` está vacío, así que los servicios arrancan pero no sirven nada. Este change crea el proyecto Symfony ahí dentro y deja instalados los bundles que el resto del roadmap (entidades de dominio, bot de Telegram, clasificación IA) va a necesitar desde el primer commit, para no tener que revisar dependencias en cada change posterior.

## Goals / Non-Goals

**Goals:**
- Proyecto Symfony 8.x / PHP 8.3+ arrancando correctamente sobre el docker-compose existente.
- Estructura de carpetas hexagonal (`Domain/Application/Infrastructure`) creada y vacía, lista para que el siguiente change añada la primera entidad.
- Bundles base instalados y con configuración mínima funcional (no producción): Doctrine/Postgres, Messenger, Workflow, UID, Security+JWT, Monolog, HttpClient, Validator, Serializer, Nelmio API Doc.
- Una ruta `/health` que demuestre el pipeline completo: nginx → php-fpm → Symfony → conexión a Postgres.
- PSR-12 verificable con `php-cs-fixer` o `phpcs`.

**Non-Goals:**
- Ninguna entidad de dominio, caso de uso o migración de base de datos con tablas reales.
- Integración real con Ollama o la API de Telegram (solo queda instalado `http-client`, sin llamadas).
- Generación/gestión de claves JWT de producción — basta con un par de claves de desarrollo generado localmente y documentado.
- CI/CD.

## Decisions

- **`composer create-project symfony/skeleton` en vez de `symfony/webapp`**: el proyecto es API-first (web + bot Telegram como adaptadores de entrada), no necesita Twig/Webpack Encore desde el arranque salvo cuando se construya la interfaz web. Se añadirá `symfony/twig-bundle` en el change que implemente las primeras vistas, no aquí.
- **Estructura `Domain/Application/Infrastructure` como carpetas vacías con `.gitkeep`**: refleja `docs/claude/arquitectura.md` desde el primer commit, evitando que el siguiente change tenga que decidir la disposición de carpetas.
- **`lexik/jwt-authentication-bundle` sobre `symfony/security-bundle`**: es el estándar de facto en el ecosistema Symfony para JWT stateless, encaja con el uso dual web/bot (bot no usa sesión, solo `telegram_chat_id`; la API web sí necesita tokens). Se generan claves de desarrollo (`bin/console lexik:jwt:generate-keypair`) documentadas en el README, no en el repo.
- **`nelmio/api-doc-bundle`**: exigido explícitamente por CLAUDE.md para documentar cada endpoint con `#[OA\...]`. Se instala y configura ahora aunque no haya endpoints de negocio, solo el de `/health`, para fijar la convención desde el principio.
- **Ruta `/health` como smoke test, no como endpoint de negocio**: comprueba conectividad a Postgres (`SELECT 1` vía Doctrine DBAL) y devuelve JSON simple. No lleva autenticación JWT (debe responder incluso si la auth falla) ni se documenta con Swagger como si fuera un endpoint del dominio — es infraestructura, no un caso de uso del modelo.
- **`app/.env` reutiliza las variables del `.env` raíz de docker-compose vía las mismas credenciales de Postgres**, pero define su propia `DATABASE_URL` (Symfony no lee el `.env` raíz directamente). Se documentará la relación en el README para evitar desincronización.
- **PSR-12 con `php-cs-fixer`**: más extendido que `phpcs` en el ecosistema Symfony reciente y con soporte directo de reglas PSR-12 out of the box.

## Risks / Trade-offs

- [Duplicación de credenciales Postgres entre `.env` raíz y `app/.env`] → Mitigación: documentar explícitamente en el README que `app/.env` debe reflejar `POSTGRES_USER`/`POSTGRES_PASSWORD`/`POSTGRES_DB` del `.env` raíz; no se automatiza en este change para no acoplar la config de Symfony al docker-compose.
- [Bundles instalados sin uso real todavía (Messenger, Workflow, HttpClient) pueden quedar mal configurados y no detectarse hasta el primer change que los use] → Mitigación: cada bundle se verifica con un comando de sanity (`bin/console messenger:consume --help`, `bin/console workflow:dump`, etc.) como parte de las tasks, no solo con la instalación de Composer.
- [Claves JWT de desarrollo commiteadas por error] → Mitigación: añadir `config/jwt/` al `.gitignore` de `app/` explícitamente.

## Migration Plan

No aplica migración de datos (no hay entidades). Pasos de despliegue: `docker compose build php nginx worker` tras crear el proyecto (para que la imagen `php` instale las dependencias vía `composer install` en el Dockerfile o se ejecuten en el volumen montado), luego `docker compose up -d` y verificación de `/health`. Sin rollback especial: si algo falla, `app/` puede vaciarse y recrearse sin afectar a Postgres ni a los volúmenes.

## Open Questions

- ¿El `composer install` se ejecuta dentro del Dockerfile de `docker/php/` (build-time) o como paso manual tras montar el volumen? Dado que `app/` es un bind mount vacío en el momento del build de la imagen, probablemente deba ejecutarse en un `docker compose exec php composer install` post-creación, o añadir un `command`/entrypoint que lo haga en arranque. Se decide durante la implementación (tasks.md lo deja explícito).

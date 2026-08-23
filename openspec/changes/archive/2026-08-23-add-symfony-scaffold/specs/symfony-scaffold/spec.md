## ADDED Requirements

### Requirement: Esqueleto de aplicación Symfony
El sistema SHALL disponer de un proyecto Symfony 8.x sobre PHP 8.3+ en `app/`, servido por el `nginx`/`php-fpm` ya definidos en `docker-compose.yml`, con estructura de carpetas hexagonal (`src/Domain`, `src/Application`, `src/Infrastructure`) conforme a `docs/claude/arquitectura.md`.

#### Scenario: El stack sirve la aplicación Symfony
- **WHEN** se ejecuta `docker compose up -d` con el proyecto Symfony creado en `app/`
- **THEN** una petición HTTP a `nginx` en el puerto configurado responde con una respuesta válida de Symfony (no un error 502/404 de fichero inexistente)

#### Scenario: Estructura hexagonal presente
- **WHEN** se inspecciona `app/src/`
- **THEN** existen los directorios `Domain/{Entity,ValueObject,Repository,Enum}`, `Application/{Command,Query,Handler,Service}` e `Infrastructure/{Persistence,Ai,Import,Telegram,Http,EventSubscriber}`, vacíos o con `.gitkeep`

### Requirement: Bundles base instalados y operativos
El sistema SHALL tener instalados y mínimamente configurados los bundles: Doctrine ORM (Postgres), Messenger, Workflow, UID, Security + JWT (Lexik), Monolog, HttpClient, Validator, Serializer y Nelmio API Doc.

#### Scenario: Conexión a base de datos verificable
- **WHEN** se ejecuta un comando Doctrine de comprobación de conexión (p. ej. `bin/console dbal:run-sql "SELECT 1"`)
- **THEN** el comando se ejecuta sin error contra el `postgres` del docker-compose

#### Scenario: Autenticación JWT disponible
- **WHEN** se generan las claves de desarrollo con `bin/console lexik:jwt:generate-keypair`
- **THEN** el bundle queda operativo para emitir tokens, sin que las claves se incluyan en el control de versiones

#### Scenario: Documentación Swagger accesible
- **WHEN** se accede a la ruta de documentación expuesta por `nelmio/api-doc-bundle`
- **THEN** se devuelve la especificación OpenAPI generada, aunque solo describa el endpoint `/health`

### Requirement: Endpoint de comprobación de salud
El sistema SHALL exponer una ruta `/health` que verifique end-to-end que nginx, php-fpm, Symfony y la conexión a Postgres funcionan correctamente, sin requerir autenticación.

#### Scenario: Health check exitoso
- **WHEN** se hace una petición GET a `/health` con el stack completo levantado
- **THEN** se responde con estado HTTP 200 y un cuerpo JSON indicando que la aplicación y la base de datos están operativas

#### Scenario: Health check no requiere autenticación
- **WHEN** se hace una petición GET a `/health` sin token JWT
- **THEN** la petición no es rechazada por el firewall de seguridad

### Requirement: Estándar de estilo de código
El sistema SHALL verificar el cumplimiento de PSR-12 en `app/src/` mediante una herramienta automatizada (`php-cs-fixer` o `phpcs`) ejecutable desde el propio proyecto.

#### Scenario: Verificación de estilo ejecutable
- **WHEN** se ejecuta el comando de verificación de estilo configurado (p. ej. `vendor/bin/php-cs-fixer check`)
- **THEN** el comando corre sin errores de configuración sobre `app/src/`

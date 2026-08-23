## 1. Scaffold del proyecto Symfony

- [x] 1.1 Ejecutar `composer create-project symfony/skeleton app-tmp` (o equivalente) y mover el contenido a `app/`, respetando el bind mount existente
- [x] 1.2 Verificar que `docker compose build php nginx worker` y `docker compose up -d` levantan el stack sin errores con el nuevo `app/public/index.php`
- [x] 1.3 Crear la estructura de carpetas hexagonal vacía: `src/Domain/{Entity,ValueObject,Repository,Enum}`, `src/Application/{Command,Query,Handler,Service}`, `src/Infrastructure/{Persistence,Ai,Import,Telegram,Http,EventSubscriber}` con `.gitkeep`

## 2. Persistencia y async

- [x] 2.1 Instalar `doctrine/orm` + `doctrine/doctrine-bundle`, configurar `DATABASE_URL` en `app/.env` contra el `postgres` del docker-compose
- [x] 2.2 Verificar conexión con `bin/console dbal:run-sql "SELECT 1"`
- [x] 2.3 Instalar `symfony/uid`
- [x] 2.4 Instalar `symfony/messenger`, configurar transporte async apuntando a lo que ya consume el servicio `worker` del compose
- [x] 2.5 Instalar `symfony/workflow`, verificar con `bin/console workflow:dump` (aunque no haya workflows definidos todavía)

## 3. Seguridad, logging y HTTP

- [x] 3.1 Instalar `symfony/security-bundle` + `lexik/jwt-authentication-bundle`
- [x] 3.2 Generar par de claves de desarrollo con `bin/console lexik:jwt:generate-keypair` y añadir `config/jwt/` al `.gitignore` de `app/`
- [x] 3.3 Configurar firewall mínimo: rutas protegidas por JWT por defecto, con excepción explícita para `/health`
- [x] 3.4 Instalar `symfony/monolog-bundle`, verificar que el worker de Messenger escribe logs
- [x] 3.5 Instalar `symfony/http-client`
- [x] 3.6 Instalar `symfony/validator` y `symfony/serializer`

## 4. Documentación de API

- [x] 4.1 Instalar y configurar `nelmio/api-doc-bundle`
- [x] 4.2 Verificar que la ruta de documentación Swagger responde correctamente
- [x] 4.3 Crear `http/health.http` con la petición de ejemplo al endpoint `/health`

## 5. Endpoint de salud

- [x] 5.1 Crear controller `Infrastructure/Http/HealthController` con ruta `GET /health`
- [x] 5.2 Implementar comprobación de conexión a Postgres dentro del health check
- [x] 5.3 Devolver JSON con estado 200 y detalle de estado (app + bd)
- [x] 5.4 Confirmar que `/health` es accesible sin token JWT

## 6. Calidad de código

- [x] 6.1 Instalar y configurar `php-cs-fixer` con reglas PSR-12
- [x] 6.2 Ejecutar `vendor/bin/php-cs-fixer check` sobre `src/` y corregir hallazgos iniciales
- [x] 6.3 Documentar en el README de `app/` (o el README raíz) cómo levantar el stack, generar claves JWT y ejecutar el linter

## 7. Verificación final

- [x] 7.1 `docker compose up -d` desde cero y comprobar `GET /health` devuelve 200
- [x] 7.2 Comprobar que `nginx`/`php-fpm`/`worker` arrancan sin errores en los logs
- [x] 7.3 Confirmar que ninguna clave/secreto (JWT, credenciales) quedó commiteado

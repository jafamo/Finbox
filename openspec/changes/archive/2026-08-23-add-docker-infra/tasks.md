## 1. Configuración de entorno (.env)

- [x] 1.1 Crear `.env.example` con todas las variables: `PHP_VERSION` (8.4), `NGINX_VERSION`, `POSTGRES_VERSION` (17), `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD`, `POSTGRES_PORT`, `NGINX_PORT`, `OLLAMA_BASE_URL` (URL del Ollama externo, p. ej. `http://host.docker.internal:11434`), nombres de volúmenes si aplica
- [x] 1.2 Copiar `.env.example` a `.env` con valores de desarrollo
- [x] 1.3 Añadir/actualizar `.gitignore` para excluir `.env` (manteniendo `.env.example` versionado)

## 2. Dockerfile de php

- [x] 2.1 Crear `docker/php/Dockerfile` con `ARG PHP_VERSION`, imagen base `php:${PHP_VERSION}-fpm`, extensiones necesarias (`pdo_pgsql`, `intl`, `zip`, `opcache`) y Composer instalado
- [x] 2.2 Verificar que el Dockerfile no hace `COPY` de código de aplicación (el contenido de `app/` se monta como bind mount desde Compose)

## 3. Dockerfile y configuración de nginx

- [x] 3.1 Crear `docker/nginx/Dockerfile` con `ARG NGINX_VERSION`, imagen base `nginx:${NGINX_VERSION}`
- [x] 3.2 Crear `docker/nginx/default.conf` con proxy `fastcgi_pass` hacia `php:9000` y `root` apuntando al `public/` de Symfony (para cuando exista)

## 4. docker-compose.yml

- [x] 4.1 Definir servicio `postgres` (imagen `postgres:${POSTGRES_VERSION}`, variables de entorno desde `.env`, volumen `postgres_data`)
- [x] 4.2 Definir servicio `php` (build `docker/php`, bind mount `./app:/var/www/html`, volumen `documents` montado en la ruta de almacenamiento de ficheros, variable de entorno `OLLAMA_BASE_URL` desde `.env`)
- [x] 4.3 Definir servicio `nginx` (build `docker/nginx`, puerto publicado `${NGINX_PORT}:80`, bind mount `./app:/var/www/html` de solo lectura, depende de `php`)
- [x] 4.4 Definir servicio `worker` (mismo build que `php`, `command` de `messenger:consume`, mismo bind mount de `app/`, variable de entorno `OLLAMA_BASE_URL`, depende de `postgres`)
- [x] 4.5 Declarar los volúmenes nombrados `postgres_data`, `documents` en la sección `volumes`
- [x] 4.6 Confirmar que no existe ningún servicio ni Dockerfile de `ollama` en el compose, y que ninguna versión, credencial, puerto o nombre de volumen queda escrito literalmente en `docker-compose.yml` (todo vía `${VARIABLE}`)

## 5. Verificación

- [x] 5.1 Ejecutar `docker compose config` para validar sintaxis e interpolación de variables
- [x] 5.2 Ejecutar `docker compose build` y confirmar que `php` y `nginx` construyen sin error con `app/` vacío o inexistente
- [x] 5.3 Ejecutar `docker compose up -d postgres` y confirmar que arranca correctamente (sin depender de `app/`)
- [x] 5.4 Confirmar que desde el contenedor `php` se puede resolver/alcanzar `OLLAMA_BASE_URL` (p. ej. `curl -s ${OLLAMA_BASE_URL}/api/tags` contra el Ollama externo ya en marcha)
- [x] 5.5 Ejecutar `docker compose down` y confirmar que los volúmenes nombrados persisten (`docker volume ls`)

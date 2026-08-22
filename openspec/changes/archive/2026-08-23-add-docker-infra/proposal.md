## Why

El proyecto no tiene todavía entorno de desarrollo definido: no existe `docker-compose.yml` ni Dockerfiles. README y CLAUDE.md ya asumen `docker compose up -d` como primer paso de arranque y un stack concreto (Symfony/PHP, PostgreSQL, worker Messenger, IA vía Ollama), pero nada de eso existe aún. Ollama ya corre en un docker-compose independiente en la misma máquina (junto a OpenWebUI), con modelos ya descargados, así que no se gestiona aquí — solo se consume por HTTP. Se necesita levantar la infraestructura base propia del proyecto, con todas las versiones de imagen y configuración parametrizadas en un `.env` en vez de hardcodeadas, para poder empezar a desarrollar el resto del sistema sobre una base reproducible.

## What Changes

- Añadir `docker-compose.yml` con los servicios: `php` (php-fpm), `nginx`, `postgres`, `worker` (misma imagen que `php`, comando `messenger:consume`).
- Añadir Dockerfiles en `docker/php/` y `docker/nginx/`, parametrizados con `ARG` de versión (`PHP_VERSION`, `NGINX_VERSION`) leídos desde `.env`.
- Añadir `docker/nginx/default.conf` con configuración básica de proxy hacia php-fpm.
- **No se despliega Ollama en este compose**: ya existe un stack Ollama + OpenWebUI corriendo en la misma máquina, en otro docker-compose, con el puerto de Ollama publicado en el host. Este proyecto solo necesita saber a qué URL conectarse (`OLLAMA_BASE_URL`), configurable vía `.env`.
- Añadir `.env.example` (versionado) y `.env` (no versionado) en la raíz con todas las versiones de imagen, credenciales de PostgreSQL, puertos expuestos, nombres de volumen y `OLLAMA_BASE_URL` — nada de eso queda hardcodeado en `docker-compose.yml`.
- Añadir volúmenes nombrados: `postgres_data`, `documents` (dedicado, independiente de `postgres_data`, para `Document.storage_path`).
- El servicio `php` monta `app/` como bind mount (no `COPY`), de forma que el stack arranca aunque `app/` esté vacío o no exista todavía.
- Actualizar `.gitignore` para excluir `.env` (mantener `.env.example`).

## Capabilities

### New Capabilities
- `docker-infra`: entorno de desarrollo Docker Compose del proyecto (servicios, versiones parametrizadas por `.env`, volúmenes, conexión a un Ollama externo ya existente vía `OLLAMA_BASE_URL`).

### Modified Capabilities
(ninguna — no existen specs previas en `openspec/specs/`)

## Impact

- Ficheros nuevos: `docker-compose.yml`, `docker/php/Dockerfile`, `docker/nginx/Dockerfile`, `docker/nginx/default.conf`, `.env`, `.env.example`.
- Ficheros modificados: `.gitignore` (si no existe, se crea).
- Fuera de alcance: proyecto Symfony (`composer.json`, `src/`, `app/.env`), despliegue/gestión de Ollama (ya corre en otro docker-compose de la misma máquina, con modelos ya descargados), CI/CD. El servicio `php`/`nginx` no tendrá aplicación real que servir hasta un change posterior que haga el scaffold de Symfony en `app/`.

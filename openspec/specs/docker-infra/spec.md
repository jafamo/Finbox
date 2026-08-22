# docker-infra

## Purpose

Entorno de desarrollo Docker Compose del proyecto: servicios `php`, `nginx`, `postgres` y `worker` con todas las versiones de imagen, credenciales y configuración parametrizadas vía `.env` (nada hardcodeado en `docker-compose.yml`). Ollama no se despliega aquí — se consume por HTTP desde un stack externo ya existente en la misma máquina.

## Requirements

### Requirement: Orquestación de servicios vía Docker Compose
El sistema SHALL definir un `docker-compose.yml` en la raíz del repositorio con los servicios `php`, `nginx`, `postgres` y `worker`, de forma que `docker compose up -d` levante todo el entorno de desarrollo propio del proyecto. Ollama SHALL NOT formar parte de este `docker-compose.yml`.

#### Scenario: Arranque del stack completo
- **WHEN** se ejecuta `docker compose up -d` en la raíz del repositorio con un `.env` válido presente
- **THEN** los cuatro servicios (`php`, `nginx`, `postgres`, `worker`) se crean y arrancan sin error de configuración de Compose

#### Scenario: Worker reutiliza la imagen de php
- **WHEN** se inspecciona la definición del servicio `worker` en `docker-compose.yml`
- **THEN** usa el mismo contexto de build que el servicio `php` (`docker/php`) y solo difiere en el `command`, ejecutando el consumidor de Messenger

### Requirement: Ninguna versión ni credencial hardcodeada en el Compose
El sistema SHALL parametrizar mediante variables de entorno (leídas de un fichero `.env` en la raíz) todas las versiones de imagen, credenciales de PostgreSQL, puertos publicados y nombres de volúmenes usados en `docker-compose.yml` y en los `ARG` de los Dockerfiles.

#### Scenario: Cambiar la versión de PostgreSQL sin editar el compose
- **WHEN** se modifica el valor de `POSTGRES_VERSION` en `.env` y se ejecuta `docker compose up -d --build`
- **THEN** el servicio `postgres` se reconstruye/reinicia usando la nueva versión de imagen, sin necesidad de editar `docker-compose.yml`

#### Scenario: .env.example documenta todas las variables
- **WHEN** se compara el contenido de `.env.example` con las variables `${...}` referenciadas en `docker-compose.yml` y en los Dockerfiles de `docker/`
- **THEN** cada variable referenciada tiene una entrada correspondiente en `.env.example` con un valor de ejemplo

### Requirement: El servicio php arranca sin necesitar el proyecto Symfony
El servicio `php` SHALL montar el directorio `app/` como bind mount (no copiarlo en la imagen), de forma que el contenedor construya y arranque correctamente aunque `app/` esté vacío o no exista todavía.

#### Scenario: Build de la imagen php sin código de aplicación presente
- **WHEN** se ejecuta `docker compose build php` sin que exista contenido de aplicación Symfony dentro de `app/`
- **THEN** la imagen se construye correctamente, sin fallar por ausencia de `composer.json` u otros ficheros de la app

### Requirement: Volumen dedicado para documentos originales
El sistema SHALL definir un volumen nombrado independiente de `postgres_data` para el almacenamiento de los ficheros originales de `Document`, de forma que ambos volúmenes puedan respaldarse o gestionarse por separado.

#### Scenario: Volúmenes de datos y documentos son independientes
- **WHEN** se inspecciona la sección `volumes` de `docker-compose.yml`
- **THEN** existen al menos dos volúmenes nombrados distintos, uno para los datos de PostgreSQL (`postgres_data`) y otro para los documentos originales (`documents`), sin que uno dependa del otro

### Requirement: Conexión a un Ollama externo vía URL configurable
El sistema SHALL NOT desplegar ni gestionar Ollama dentro de este `docker-compose.yml`. En su lugar, SHALL exponer la URL de un servicio Ollama externo (que corre en otro docker-compose de la misma máquina, con el puerto publicado en el host) a través de la variable `OLLAMA_BASE_URL` en `.env`, disponible como variable de entorno en los servicios `php` y `worker`.

#### Scenario: OLLAMA_BASE_URL disponible en los contenedores de la app
- **WHEN** se inspecciona el entorno del contenedor `php` o `worker` en ejecución
- **THEN** la variable de entorno `OLLAMA_BASE_URL` está presente con el valor definido en `.env`

#### Scenario: No existe servicio ollama en el compose
- **WHEN** se inspecciona la sección `services` de `docker-compose.yml`
- **THEN** no hay ningún servicio llamado `ollama` ni ningún Dockerfile bajo `docker/ollama/`

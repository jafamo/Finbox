## Context

El repo tiene documentación completa (arquitectura hexagonal, modelo de dominio, flujo Telegram) pero ningún código ni infraestructura todavía: no hay `docker-compose.yml`, Dockerfiles, ni proyecto Symfony. `arquitectura.md` exige que el fichero original de cada `Document` se guarde en un volumen dedicado, separado del volumen de PostgreSQL. `CLAUDE.md`/README asumen `docker compose up -d` como primer paso de arranque.

Ollama ya está desplegado y en marcha: corre en un docker-compose independiente en la misma máquina, junto a OpenWebUI, con modelos ya descargados. Ese stack publica el puerto de Ollama en el host. Este proyecto no gestiona Ollama en absoluto — solo lo consume por HTTP desde el host.

Esta change cubre solo la infraestructura Docker propia del proyecto: no crea el proyecto Symfony (`composer.json`, `src/`), ni despliega Ollama. El servicio `php` debe poder arrancar igualmente aunque `app/` no exista aún, para que esta change sea mergeable de forma independiente.

## Goals / Non-Goals

**Goals:**
- Levantar `docker compose up -d` con `php`, `nginx`, `postgres`, `worker` funcionando (sin app real todavía en `php`/`nginx`/`worker`).
- Ninguna versión de imagen, credencial, puerto o nombre de volumen hardcodeada en `docker-compose.yml`: todo parametrizado vía `.env`.
- La app puede resolver la URL de un Ollama externo (otro docker-compose en el mismo host) vía la variable `OLLAMA_BASE_URL`, sin desplegar ni gestionar Ollama en este proyecto.
- `.env.example` versionado y documentado, `.env` real ignorado por git.

**Non-Goals:**
- Scaffold del proyecto Symfony (`composer.json`, `src/`, `app/.env`) — change futura.
- Despliegue, configuración o gestión de modelos de Ollama — ya corre en un stack externo (con OpenWebUI) en la misma máquina.
- Perfil de producción (multi-stage build optimizado, secretos gestionados, TLS) — esto es entorno de desarrollo.
- CI/CD.

## Decisions

**Servidor web: php-fpm + nginx separados (no FrankenPHP)**
Decisión explícita del usuario. Dos servicios (`php`, `nginx`) en vez de uno solo; nginx hace de proxy hacia `php-fpm:9000` vía `fastcgi_pass`.

**`app/` como bind mount, no `COPY` en el Dockerfile**
El Dockerfile de `php` instala PHP, extensiones (`pdo_pgsql`, `intl`, `zip`, `opcache`...) y Composer, pero no copia código de aplicación. `docker-compose.yml` monta `./app:/var/www/html` como bind mount. Esto permite que el contenedor arranque y quede en espera aunque `app/` esté vacío, y que un change futuro añada el proyecto Symfony sin tocar el Dockerfile.

**`worker` reutiliza la imagen de `php`, solo cambia el comando**
En vez de un Dockerfile separado, `docker-compose.yml` define el servicio `worker` con `build: docker/php` (misma imagen) y `command: php bin/console messenger:consume async -vv`. Evita duplicar la definición de imagen; cuando `app/` no exista, este comando fallará al arrancar (esperado — sin Symfony no hay `bin/console` — se documenta como comportamiento conocido de esta fase).

**Todas las versiones y configuración sensible en `.env`, con `.env.example` versionado**
`docker-compose.yml` no lleva ninguna versión ni credencial literal — todo son `${VARIABLE}`. `.env.example` documenta cada variable con su valor de ejemplo/dev; `.env` real se añade a `.gitignore`. Las versiones (`PHP_VERSION=8.4`, `POSTGRES_VERSION=17`, `NGINX_VERSION` estable, `OLLAMA_VERSION` última estable) se fijan como valores por defecto razonables en `.env.example`, no como hardcode en el compose ni en los Dockerfiles (que las reciben vía `ARG`).

**Credenciales de PostgreSQL no se sincronizan automáticamente con Symfony**
`app/.env.local` con `DATABASE_URL` se creará y mantendrá manualmente en un change futuro, duplicando a mano los valores de `.env` raíz. Esta change no genera ni gestiona ese fichero.

**Volumen `documents` separado de `postgres_data`**
Cumple el requisito de `arquitectura.md`: el volumen de documentos originales no comparte volumen con la base de datos, para poder tener política de backup independiente.

**Ollama no se despliega en este compose: conexión al stack externo vía puerto publicado en el host**
Ya existe un docker-compose independiente en la misma máquina con Ollama + OpenWebUI, con modelos ya descargados. Ese stack publica el puerto de Ollama (por defecto `11434`) en el host. Este proyecto no crea ningún servicio ni Dockerfile para Ollama; en su lugar, define `OLLAMA_BASE_URL` en `.env` (p. ej. `http://host.docker.internal:11434` en Docker Desktop, o la IP del host/`172.17.0.1` en Linux con el driver de red por defecto) y los servicios `php`/`worker` la leen como variable de entorno para que el futuro `AiClassifierInterface`/adapter de Ollama la use. No se gestiona el ciclo de vida, la disponibilidad ni los modelos de ese Ollama externo desde este repositorio.

## Risks / Trade-offs

- [`worker` y `php` fallarán al arrancar de forma útil sin `app/` con Symfony dentro] → Esperado y documentado; el objetivo de esta change es la infraestructura, no un entorno funcional end-to-end. Se deja constancia en el README/proposal.
- [Duplicación manual de credenciales entre `.env` raíz y `app/.env.local` futuro] → Riesgo de desincronización; se acepta como trade-off consciente frente a la complejidad de inyectar `DATABASE_URL` compuesta desde Compose. Si se vuelve un problema, se puede revisar en una change posterior.
- [`.env.example` con versiones "más recientes estables a fecha de hoy" quedará desactualizado con el tiempo] → Mitigación: son solo valores por defecto de desarrollo, se pueden bumpear en changes puntuales sin tocar la estructura del compose.
- [`OLLAMA_BASE_URL` vía puerto publicado en el host es frágil: depende de que el otro docker-compose siga publicando ese puerto, y `host.docker.internal`/la IP del gateway pueden variar según SO y configuración de red] → Se acepta porque es la opción más simple y no requiere coordinar con el otro stack; si da problemas de resolución de host, la alternativa (red Docker externa compartida) queda documentada como opción para una change futura.

## Migration Plan

No aplica (infraestructura nueva, no hay estado previo que migrar). Pasos de despliegue: copiar `.env.example` a `.env`, ajustar credenciales si procede, `docker compose up -d --build`.

## Open Questions

- Ninguna pendiente para esta change; el scaffold de Symfony y la selección de modelos de Ollama se abordarán en changes futuras.

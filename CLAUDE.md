# CLAUDE.md — Gestión de recibos y movimientos bancarios

## Qué es este proyecto

Aplicación para centralizar, importar y clasificar recibos y movimientos bancarios de múltiples bancos, cuentas y personas. Usa IA local (Ollama) para extraer, clasificar y detectar duplicados, con revisión humana sobre todas las decisiones de la IA.

El canal principal de entrada es un **bot de Telegram**: el usuario envía fotos de recibos o ficheros (PDF, CSV) al bot, el sistema los procesa y devuelve un resumen en el chat. La **interfaz web** es el centro de control para consultas, informes, estadísticas, correcciones y gestión.

## Stack tecnológico

- **Backend:** Symfony 8.x, PHP 8.3+
- **Bot Telegram:** Webhook en Symfony, adaptador en Infrastructure/Telegram
- **Base de datos:** PostgreSQL (con soporte JSON nativo y futura extensión pgvector para embeddings)
- **IA:** Ollama en contenedor Docker separado, comunicación vía HTTP
- **Procesamiento asíncrono:** Symfony Messenger
- **State Machine:** Symfony Workflow (estados de documentos y movimientos)
- **Auditoría:** EventSubscriber de Doctrine (preUpdate/postUpdate)
- **Entorno:** Docker Compose (Symfony, PostgreSQL, Ollama, worker Messenger)

## Documentación del proyecto

@docs/claude/arquitectura.md
@docs/claude/modelo-dominio.md
@docs/claude/flujo-telegram.md
@docs/claude/git-openspec.md

Especificación y modelo de dominio detallados (documentos de referencia, no se cargan automáticamente):

- `/docs/spec_mvp_recibos_v0.3.md` — Especificación funcional completa
- `/docs/domain_model_v0.3.md` — Modelo de dominio con diagrama ERD y detalle de entidades

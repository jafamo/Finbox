# 🧾 Gestor de Recibos

> Centraliza, importa y clasifica recibos y movimientos bancarios de múltiples bancos, cuentas y personas, con ayuda de IA local y revisión humana sobre cada decisión automática.

<p>
  <img src="https://img.shields.io/badge/Symfony-8.x-000000?style=for-the-badge&logo=symfony&logoColor=white" alt="Symfony 8.x">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/PostgreSQL-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/Ollama-000000?style=for-the-badge&logo=ollama&logoColor=white" alt="Ollama">
  <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/Telegram-Bot-26A5E4?style=for-the-badge&logo=telegram&logoColor=white" alt="Telegram Bot">
  <img src="https://img.shields.io/badge/Twig-B41717?style=for-the-badge&logo=symfony&logoColor=white" alt="Twig">
  <img src="https://img.shields.io/badge/PHPUnit-3776AB?style=for-the-badge&logo=php&logoColor=white" alt="PHPUnit">
  <img src="https://img.shields.io/badge/Swagger-85EA2D?style=for-the-badge&logo=swagger&logoColor=black" alt="Swagger / OpenAPI">
</p>

<p>
  <img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="License">
  <img src="https://img.shields.io/badge/status-en%20desarrollo-yellow.svg?style=flat-square" alt="Status">
  <img src="https://img.shields.io/badge/arquitectura-hexagonal-informational?style=flat-square" alt="Arquitectura hexagonal">
</p>

---

## 📋 Qué es

El canal principal de entrada es un **bot de Telegram**: envías una foto de un recibo o un extracto (PDF/CSV) y el sistema lo procesa, clasifica y devuelve un resumen en el chat. La **interfaz web** es el centro de control para consultar, corregir, generar informes y gestionar reglas.

```
Recibo / Extracto  →  Telegram Bot  →  Parser / OCR (Ollama)  →  Reglas → IA  →  Revisión humana  →  ✅
```

## 🛠️ Stack

| Capa | Tecnología |
|---|---|
| Backend | Symfony 8.x · PHP 8.3+ |
| Base de datos | PostgreSQL (JSON nativo, futuro pgvector) |
| IA | Ollama (contenedor Docker separado, HTTP) |
| Async | Symfony Messenger |
| Estados | Symfony Workflow |
| Auditoría | EventSubscriber de Doctrine |
| Entrada | Bot de Telegram (webhook) + Web |
| Frontend | Twig (sin framework externo) |
| Entorno | Docker Compose |
| Estilo de código | PSR-12 |
| Documentación API | Swagger / OpenAPI |
| Testing | PHPUnit |
| Pruebas de API | Colección `.http` en `http/` |

## 🏗️ Arquitectura

Arquitectura hexagonal (puertos y adaptadores): el dominio no depende de infraestructura, y Telegram/Web son dos adaptadores de entrada que comparten los mismos casos de uso.

```
src/
├── Domain/          # Entidades, Value Objects, interfaces de repositorio, enums
├── Application/     # Casos de uso, commands, queries, handlers
└── Infrastructure/  # Doctrine, Ollama, Telegram, Http, EventSubscriber
```

📖 Detalle completo en [docs/claude/arquitectura.md](docs/claude/arquitectura.md) y [docs/claude/modelo-dominio.md](docs/claude/modelo-dominio.md).

## 🚀 Puesta en marcha

```bash
docker compose up -d
composer install
php bin/console doctrine:migrations:migrate
```

## 📚 Documentación

- [docs/spec_mvp_recibos_v0.3.md](docs/spec_mvp_recibos_v0.3.md) — especificación funcional completa
- [docs/domain_model_v0.3.md](docs/domain_model_v0.3.md) — modelo de dominio con ERD
- [docs/claude/](docs/claude/) — arquitectura, modelo de dominio, flujo de Telegram y flujo de git/OpenSpec (contexto para desarrollo asistido por IA)

## 🌳 Desarrollo

El proyecto usa **Git Flow** + **OpenSpec** para cambios spec-driven. Ver [docs/claude/git-openspec.md](docs/claude/git-openspec.md) para el flujo completo y las reglas de alcance de cambios.

## 📄 Licencia

MIT

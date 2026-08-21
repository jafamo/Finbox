# CLAUDE.md — Gestión de recibos y movimientos bancarios

## Qué es este proyecto

Aplicación para centralizar, importar y clasificar recibos y movimientos bancarios de múltiples bancos, cuentas y personas. Usa IA local (Ollama) para extraer, clasificar y detectar duplicados, con revisión humana sobre todas las decisiones de la IA.

El canal principal de entrada es un **bot de Telegram**: el usuario envía fotos de recibos o ficheros (PDF, CSV) al bot, el sistema los procesa y devuelve un resumen en el chat. La **interfaz web** es el centro de control para consultas, informes, estadísticas, correcciones y gestión.

## Stack tecnológico

- **Backend:** Symfony 7.x, PHP 8.3+
- **Bot Telegram:** Webhook en Symfony, adaptador en Infrastructure/Telegram
- **Base de datos:** PostgreSQL (con soporte JSON nativo y futura extensión pgvector para embeddings)
- **IA:** Ollama en contenedor Docker separado, comunicación vía HTTP
- **Procesamiento asíncrono:** Symfony Messenger
- **State Machine:** Symfony Workflow (estados de documentos y movimientos)
- **Auditoría:** EventSubscriber de Doctrine (preUpdate/postUpdate)
- **Entorno:** Docker Compose (Symfony, PostgreSQL, Ollama, worker Messenger)

## Arquitectura

Arquitectura hexagonal (puertos y adaptadores).

```
src/
├── Domain/              # Entidades, Value Objects, interfaces de repositorio, enums
│   ├── Entity/
│   ├── ValueObject/
│   ├── Repository/      # Interfaces (puertos)
│   └── Enum/
├── Application/         # Casos de uso, commands, queries, handlers
│   ├── Command/
│   ├── Query/
│   ├── Handler/
│   └── Service/
└── Infrastructure/      # Implementaciones concretas (adaptadores)
    ├── Persistence/     # Repositorios Doctrine
    ├── Ai/              # Adapter Ollama (implementa AiClassifierInterface)
    ├── Import/          # Parsers CSV, lectores PDF
    ├── Telegram/        # Webhook controller, bot service, notificaciones
    ├── Http/            # Controllers API web
    └── EventSubscriber/ # Auditoría, feedback
```

El dominio no depende de infraestructura. Telegram y la web son dos adaptadores de entrada que comparten los mismos casos de uso. Los puertos principales son:
- `AiClassifierInterface` — abstrae el modelo de IA (hoy Ollama, mañana otro)
- `DocumentParserInterface` — abstrae la extracción de movimientos (CSV, PDF, imagen)
- `NotificationInterface` — abstrae el envío de notificaciones (Telegram, y potencialmente otros canales)
- Interfaces de repositorio por entidad

## Modelo de dominio

### Entidades y relaciones

```
User →manages→ Client ↔holds↔ BankAccount ←owns← Bank
                                    ↓                  ↓
                                Movement          ImportProfile
                                    ↓
                     Category, ClassificationFeedback
```

### User
Quién opera la aplicación. Autenticación, permisos, preferencias. Vinculación con Telegram.
- `id` uuid PK, `username` string, `email` string, `password_hash` string
- `role` enum(admin, user), `is_active` boolean, `preferences` json, `created_at` datetime
- `telegram_chat_id` string nullable unique — vincula al usuario con el bot de Telegram

### Client
Titular de cuentas bancarias. NO es usuario de la aplicación. Es un dato del dominio.
- `id` uuid PK, `first_name` string, `last_name` string, `alias` string nullable
- `notes` text nullable, `created_at` datetime
- Relación many-to-many con BankAccount (tabla pivot `client_bank_account`)

### Bank
Entidad financiera.
- `id` uuid PK, `name` string, `internal_code` string nullable, `notes` text nullable

### BankAccount
Cuenta bancaria. Entidad central que conecta bancos, clientes y movimientos.
- `id` uuid PK, `bank_id` uuid FK, `iban` string, `alias` string nullable
- `is_active` boolean, `notes` text nullable, `created_at` datetime

### Document
Origen de movimientos. El fichero original se conserva siempre.
- `id` uuid PK, `bank_account_id` uuid FK nullable, `import_profile_id` uuid FK nullable
- `uploaded_by` uuid FK — usuario que subió el documento
- `filename` string, `type` enum(csv, pdf, image, scan, other)
- `source` enum(telegram, web) — canal por el que se subió
- `status` enum(pending, processing, processed, needs_review, reviewed, error)
- `imported_at` datetime, `processed_at` datetime nullable
- `movements_count` integer, `processing_result` text nullable, `storage_path` string
- `telegram_message_id` string nullable — referencia al mensaje original en Telegram

### Movement
Unidad principal. Un movimiento bancario o recibo individual.
- `id` uuid PK, `document_id` uuid FK, `bank_account_id` uuid FK nullable
- `client_id` uuid FK nullable, `category_id` uuid FK nullable
- `date` date, `value_date` date nullable, `amount` decimal(12,2), `currency` string(3)
- `original_concept` string (NUNCA se modifica), `normalized_concept` string nullable
- `original_description` text nullable
- `status` enum(pending, auto_classified, manually_reviewed, discarded, duplicate)
- `bank_reference` string nullable, `counterpart_iban` string nullable
- `counterpart_name` string nullable, `notes` text nullable, `tags` json nullable

State Machine del movimiento:
```
pending → auto_classified → manually_reviewed
pending → manually_reviewed
pending → discarded
pending → duplicate
auto_classified → manually_reviewed
auto_classified → discarded
auto_classified → duplicate
```

### Category
Clasificación temática. Soporta subcategorías con `parent_id`.
- `id` uuid PK, `name` string, `slug` string, `parent_id` uuid FK nullable, `sort_order` integer

### ImportProfile
Define cómo interpretar el formato de un extracto bancario.
- `id` uuid PK, `bank_id` uuid FK, `name` string
- `column_mapping` json (ej: `{"fecha": 0, "concepto": 2, "importe": 3}`)
- `date_format` string, `decimal_separator` string(1), `csv_separator` string(1)
- `encoding` string, `skip_rows` json, `is_active` boolean

### ClassificationRule
Regla determinista. Prioridad sobre la IA.
- `id` uuid PK, `name` string
- `field` enum(concept, iban, counterpart_name, amount, ...) — qué evaluar
- `operator` enum(equals, contains, starts_with, greater_than, ...) — cómo evaluar
- `value` string — contra qué comparar
- `target_field` enum(category, client, bank_account, ...) — qué asignar
- `target_value` string — valor a asignar
- `priority` integer (menor = mayor prioridad), `is_active` boolean
- `origin` enum(manual, auto), `created_at` datetime

### ClassificationFeedback
Correcciones del usuario sobre propuestas de la IA. Alimenta el few-shot prompting.
- `id` uuid PK, `movement_id` uuid FK, `original_concept` string
- `ai_proposed_category` / `user_final_category` string nullable
- `ai_proposed_client` / `user_final_client` string nullable
- `ai_proposed_concept` / `user_final_concept` string nullable
- `ai_confidence` float nullable, `created_at` datetime

### AuditLog
Registro polimórfico de cambios. Cualquier entidad, cualquier campo.
- `id` uuid PK, `entity_type` string, `entity_id` uuid, `field` string
- `old_value` / `new_value` string nullable
- `changed_by` uuid FK (User), `changed_at` datetime
- `origin` enum(manual, rule, ai)

## Principios clave del dominio

### Tres tipos de información
Cada dato tiene un origen que debe mantenerse diferenciado:
1. **Conocida** — introducida por el usuario, fiable
2. **Extraída** — obtenida por OCR, CSV parser, PDF reader
3. **Inferida** — propuesta por reglas o IA, requiere confirmación

### Pipeline de clasificación
```
Reglas deterministas → IA (Ollama) → Revisión humana
```
Las reglas se evalúan primero. La IA solo interviene cuando las reglas no cubren el caso. El usuario siempre tiene la última palabra.

### Aprendizaje por feedback
Cuando el usuario corrige una clasificación de la IA:
1. Se registra en `ClassificationFeedback`
2. Futuras clasificaciones usan few-shot prompting: se buscan los N movimientos revisados más similares y se incluyen como ejemplos en el prompt a Ollama
3. Si un patrón se repite con frecuencia, puede generar una `ClassificationRule` automática con `origin=auto`

### Detección de duplicados
Criterios iniciales: fecha + importe + cuenta bancaria. Nunca se eliminan automáticamente, solo se marcan. El usuario decide.

### Concepto original intocable
`original_concept` NUNCA se modifica. `normalized_concept` es la versión limpia/legible. Ambos coexisten siempre.

## Flujo principal: bot de Telegram

1. Usuario envía foto/PDF/CSV al bot de Telegram.
2. Webhook en Symfony recibe la actualización, identifica al usuario por `telegram_chat_id`.
3. Descarga el fichero vía API de Telegram, crea `Document` con `source=telegram`.
4. Despacha command asíncrono vía Messenger.
5. Worker: procesa documento (OCR vía Ollama para imágenes, parser para CSV/PDF).
6. Worker: aplica pipeline de clasificación (reglas → IA).
7. Worker: al terminar, envía resumen al chat de Telegram via `NotificationInterface`.

Formato del resumen para recibo individual:
```
📄 Recibo procesado
🏦 BBVA — Cuenta familiar
👤 María
📅 15/08/2026
💰 -45,30 €
🏷 Mercadona → Alimentación
✅ Clasificado automáticamente (confianza: 97%)
```

Formato para extractos con múltiples movimientos:
```
📄 Extracto procesado
🏦 BBVA — Cuenta familiar
📊 47 movimientos importados
✅ 38 clasificados automáticamente
⚠️ 7 requieren revisión
🔄 2 posibles duplicados
🔗 Ver detalle en la web: [enlace]
```

**Evolución futura:** botones inline de Telegram para correcciones (cambiar categoría, confirmar, descartar, marcar duplicado). Se implementará cuando el flujo básico esté consolidado.

## Identificadores

Todas las entidades usan UUID (no autoincrement). Se generan antes de persistir, lo que facilita el procesamiento asíncrono con Messenger.

## Convenciones de código

- Arquitectura hexagonal: dominio limpio, sin dependencias de infraestructura
- Telegram y web son adaptadores de entrada que comparten los mismos casos de uso
- UUIDs como identificadores en todas las entidades
- Symfony Workflow para state machines
- Symfony Messenger para procesamiento asíncrono (imports de CSV, clasificación IA, notificaciones)
- EventSubscriber de Doctrine para auditoría automática
- Adapter pattern para Ollama y Telegram (implementan interfaces de dominio)
- Campos nullable cuando el dato puede no conocerse al momento de la importación

## Control de versiones

El proyecto usa **Git Flow**. Ramas principales:

- `main` — código en producción, solo recibe merges desde `release/*` o `hotfix/*`
- `develop` — rama de integración, base de todo el trabajo en curso

Ramas de soporte (prefijos configurados con `git flow init`):

- `feature/<nombre>` — nueva funcionalidad, sale de `develop` y vuelve a `develop`
- `bugfix/<nombre>` — corrección de bugs no urgentes, sale de `develop` y vuelve a `develop`
- `release/<version>` — preparación de una versión, sale de `develop` y se fusiona en `main` y `develop`
- `hotfix/<version>` — corrección urgente en producción, sale de `main` y se fusiona en `main` y `develop`

Uso típico con el CLI de `git flow`:

```bash
git flow feature start nombre-funcionalidad
git flow feature finish nombre-funcionalidad

git flow release start 1.2.0
git flow release finish 1.2.0

git flow hotfix start 1.2.1
git flow hotfix finish 1.2.1
```

Los mensajes de commit siguen Conventional Commits (ver skill `codely-git-conventional_commit`).

## Documentación

- `/docs/spec_mvp_recibos_v0.3.md` — Especificación funcional completa
- `/docs/domain_model_v0.3.md` — Modelo de dominio con diagrama ERD y detalle de entidades

# Modelo de dominio

## Entidades y relaciones

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
Define cómo interpretar el formato de un extracto bancario. Un mismo banco puede tener varios perfiles (uno por formato de fichero soportado).
- `id` uuid PK, `bank_id` uuid FK, `name` string
- `source_format` enum(csv, pdf_layout) — determina la forma que debe tener `parser_config`
- `parser_config` json, cuya forma depende de `source_format`:
  - `csv`: `{"column_mapping": {"fecha": 0, "concepto": 2, "importe": 3}, "csv_separator": ";", "skip_rows": [0]}`
  - `pdf_layout` (extracto en tabla PDF, posiblemente multipágina con celdas multilínea): `{"row_start_pattern": "...", "value_date_pattern": "...", "ignore_patterns": ["..."], "amount_pattern": "..."}` — patrones que permiten reagrupar líneas envueltas en filas lógicas y descartar cabeceras/pies repetidos por página, ya que el mapping por índice de columna (propio de CSV) no aplica a un PDF con layout
- `date_format` string, `decimal_separator` string(1)
- `encoding` string, `is_active` boolean

> Nota: `column_mapping`, `csv_separator` y `skip_rows` (los tres específicos de CSV) viven ahora dentro de `parser_config.csv` en vez de ser columnas propias, para poder acomodar también `pdf_layout` sin campos que queden `null` para el otro formato.

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

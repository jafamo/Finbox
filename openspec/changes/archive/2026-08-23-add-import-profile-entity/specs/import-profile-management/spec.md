## ADDED Requirements

### Requirement: Crear perfil de importación
El sistema SHALL permitir crear un `ImportProfile` indicando al menos `bank_id`, `name` y `source_format` (`csv` o `pdf_layout`). `date_format`, `decimal_separator`, `parser_config`, `encoding` y `is_active` son opcionales en la petición, con valores por defecto razonables cuando no se indiquen.

#### Scenario: Alta de perfil válida
- **WHEN** se envía una petición de creación con `bank_id` de un banco existente, `name = "Open Bank - extracto PDF"` y `source_format = "pdf_layout"`
- **THEN** el sistema crea un `ImportProfile` con un `id` UUID nuevo y responde con el recurso creado

#### Scenario: Alta de perfil sin bank_id
- **WHEN** se envía una petición de creación sin `bank_id`
- **THEN** el sistema rechaza la petición con un error de validación y no crea ningún registro

#### Scenario: Alta de perfil con bank_id inexistente
- **WHEN** se envía una petición de creación con un `bank_id` que no corresponde a ningún banco existente
- **THEN** el sistema rechaza la petición con un error de validación y no crea ningún registro

#### Scenario: Alta de perfil con source_format inválido
- **WHEN** se envía una petición de creación con `source_format` distinto de `csv` o `pdf_layout`
- **THEN** el sistema rechaza la petición con un error de validación y no crea ningún registro

### Requirement: Listar perfiles de importación
El sistema SHALL permitir obtener el listado completo de perfiles de importación registrados.

#### Scenario: Listado de perfiles existentes
- **WHEN** se solicita el listado de perfiles de importación
- **THEN** el sistema responde con todos los perfiles existentes, incluyendo `id`, `bank_id`, `name`, `source_format`, `date_format`, `decimal_separator`, `parser_config`, `encoding` e `is_active`

### Requirement: Consultar perfil de importación por id
El sistema SHALL permitir obtener el detalle de un perfil de importación a partir de su `id`.

#### Scenario: Perfil existente
- **WHEN** se solicita un perfil de importación con un `id` que existe
- **THEN** el sistema responde con los datos completos de ese perfil

#### Scenario: Perfil inexistente
- **WHEN** se solicita un perfil de importación con un `id` que no existe
- **THEN** el sistema responde con un error 404

### Requirement: Editar perfil de importación
El sistema SHALL permitir modificar `name`, `source_format`, `date_format`, `decimal_separator`, `parser_config`, `encoding` e `is_active` de un perfil de importación existente. `bank_id` no se puede modificar tras la creación.

#### Scenario: Edición válida
- **WHEN** se envía una petición de edición sobre un perfil existente con nuevos valores válidos de `parser_config`
- **THEN** el sistema actualiza el perfil y responde con los datos actualizados

#### Scenario: Edición de perfil inexistente
- **WHEN** se envía una petición de edición sobre un `id` que no existe
- **THEN** el sistema responde con un error 404 y no modifica ningún registro

### Requirement: Eliminar perfil de importación
El sistema SHALL permitir eliminar un perfil de importación existente.

#### Scenario: Eliminación válida
- **WHEN** se solicita eliminar un perfil de importación existente
- **THEN** el sistema elimina el registro y responde confirmando la eliminación

#### Scenario: Eliminación de perfil inexistente
- **WHEN** se solicita eliminar un `id` que no existe
- **THEN** el sistema responde con un error 404

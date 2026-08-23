## Purpose

Gestión de bancos (entidad financiera): alta, consulta, edición y baja vía API REST. Es la base sobre la que se construyen `BankAccount` y el resto de entidades relacionadas con cuentas bancarias.

## Requirements

### Requirement: Crear banco
El sistema SHALL permitir crear un banco indicando al menos su `name`. `internal_code` y `notes` son opcionales.

#### Scenario: Alta de banco válida
- **WHEN** se envía una petición de creación con `name = "BBVA"`
- **THEN** el sistema crea un `Bank` con un `id` UUID nuevo y responde con el recurso creado

#### Scenario: Alta de banco sin nombre
- **WHEN** se envía una petición de creación sin `name`
- **THEN** el sistema rechaza la petición con un error de validación y no crea ningún registro

### Requirement: Listar bancos
El sistema SHALL permitir obtener el listado completo de bancos registrados.

#### Scenario: Listado de bancos existentes
- **WHEN** se solicita el listado de bancos
- **THEN** el sistema responde con todos los bancos existentes, incluyendo `id`, `name`, `internal_code` y `notes`

### Requirement: Consultar banco por id
El sistema SHALL permitir obtener el detalle de un banco a partir de su `id`.

#### Scenario: Banco existente
- **WHEN** se solicita un banco con un `id` que existe
- **THEN** el sistema responde con los datos completos de ese banco

#### Scenario: Banco inexistente
- **WHEN** se solicita un banco con un `id` que no existe
- **THEN** el sistema responde con un error 404

### Requirement: Editar banco
El sistema SHALL permitir modificar `name`, `internal_code` y `notes` de un banco existente.

#### Scenario: Edición válida
- **WHEN** se envía una petición de edición sobre un banco existente con nuevos valores válidos
- **THEN** el sistema actualiza el banco y responde con los datos actualizados

#### Scenario: Edición de banco inexistente
- **WHEN** se envía una petición de edición sobre un `id` que no existe
- **THEN** el sistema responde con un error 404 y no modifica ningún registro

### Requirement: Eliminar banco
El sistema SHALL permitir eliminar un banco existente.

#### Scenario: Eliminación válida
- **WHEN** se solicita eliminar un banco existente
- **THEN** el sistema elimina el registro y responde confirmando la eliminación

#### Scenario: Eliminación de banco inexistente
- **WHEN** se solicita eliminar un `id` que no existe
- **THEN** el sistema responde con un error 404

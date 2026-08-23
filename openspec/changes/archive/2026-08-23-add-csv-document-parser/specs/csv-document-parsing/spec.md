## ADDED Requirements

### Requirement: Parsear un CSV según su ImportProfile
El sistema SHALL permitir obtener la lista de movimientos crudos (`ParsedMovement`) contenidos en un fichero CSV, interpretándolo según el `column_mapping`, `csv_separator` y `skip_rows` definidos en `parser_config` de un `ImportProfile` con `source_format = csv`.

#### Scenario: CSV válido con mapping completo
- **WHEN** se parsea un CSV cuyas columnas coinciden con el `column_mapping` del `ImportProfile` (índices de `fecha`, `concepto`, `importe`)
- **THEN** el sistema devuelve un `ParsedMovement` por cada fila de datos, con `date`, `originalConcept` y `amount` extraídos de las columnas indicadas

#### Scenario: CSV con filas de cabecera a saltar
- **WHEN** el `ImportProfile` indica `skip_rows` con las primeras N filas del fichero
- **THEN** el sistema ignora esas filas y no genera ningún `ParsedMovement` a partir de ellas

#### Scenario: Separador de campo distinto de coma
- **WHEN** el `ImportProfile` indica `csv_separator = ";"` y el fichero usa `;` como separador
- **THEN** el sistema interpreta correctamente cada campo de cada fila

### Requirement: Normalizar fecha e importe según el ImportProfile
El sistema SHALL convertir el texto de fecha e importe de cada fila a los tipos normalizados de `ParsedMovement`, usando `date_format` y `decimal_separator` del `ImportProfile`.

#### Scenario: Fecha en formato distinto de ISO
- **WHEN** el `ImportProfile` indica `date_format = "d/m/Y"` y una fila trae `"21/08/2026"`
- **THEN** el `ParsedMovement` resultante tiene la fecha interpretada correctamente como 21 de agosto de 2026

#### Scenario: Importe con separador decimal distinto del punto
- **WHEN** el `ImportProfile` indica `decimal_separator = ","` y una fila trae `"-84,30"`
- **THEN** el `ParsedMovement` resultante tiene `amount = -84.30`

### Requirement: Rechazar un ImportProfile mal configurado
El sistema SHALL rechazar el parseo con un error de dominio explícito cuando `parser_config` de un `ImportProfile` con `source_format = csv` no incluye las claves de `column_mapping` necesarias (`fecha`, `concepto`, `importe`).

#### Scenario: column_mapping incompleto
- **WHEN** se intenta parsear un CSV con un `ImportProfile` cuyo `parser_config.column_mapping` no incluye la clave `importe`
- **THEN** el sistema lanza una excepción de dominio indicando qué clave falta, sin intentar leer el fichero

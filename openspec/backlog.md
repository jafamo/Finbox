# Backlog — ideas futuras sin proponer todavía

Ideas identificadas pero fuera de alcance del trabajo actual. No son changes activos; cuando se aborden, pasan por `/opsx:propose` normal.

## Parsing de recibos individuales en PDF tipo formulario (`pdf_form`)

Detectado al analizar un "Cargo por adeudo directo" real (BBVA/MAPFRE): a diferencia de los extractos bancarios (tablas, donde el orden de lectura del texto coincide con el orden visual), este tipo de documento es un formulario con casillas donde **el orden de extracción de texto no coincide con el orden visual** — etiqueta y valor pueden aparecer en cualquier posición del texto extraído.

Esto exige una tercera estrategia de parsing, distinta de `csv` y `pdf_layout` (tabla con `row_start_pattern`):

- **`pdf_form`**: extracción por búsqueda de etiquetas conocidas (`ACREEDOR:`, `REF. MANDATO:`, `VENCIMIENTO:`, `IMPORTE TOTAL:`, `DEUDOR:`...) en cualquier parte del texto, no por posición ni orden secuencial.
- Produce **un solo movimiento**, no una lista — forma de retorno distinta a la de `DocumentParserInterface` diseñada para extractos.
- Es probable que su propósito real no sea crear un `Movement` nuevo, sino **conciliar/enriquecer** uno ya importado desde un extracto (mismo REF. MANDATO / importe / fecha), aportando detalle del concepto que el extracto no trae.

Antes de proponer este change hay que decidir: ¿el retorno de `DocumentParserInterface` admite desde el diseño "N movimientos" y "1 movimiento con posible conciliación", o se modela como un puerto/caso de uso distinto (`ReconcileReceiptDocument` en vez de `ImportDocument`)?

Contexto completo de la conversación que lo originó: análisis de tres PDFs reales (Open Bank, BBVA extracto, BBVA recibo de adeudo directo) el 2026-08-23.

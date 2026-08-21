# Especificación funcional — MVP Gestión de recibos y movimientos bancarios

**Versión:** 0.3
**Estado:** Borrador funcional
**Fecha:** Agosto 2026

---

## 1. Objetivo de la aplicación

La aplicación permitirá centralizar información procedente de diferentes bancos, cuentas bancarias, personas y documentos financieros.

Su objetivo principal será facilitar la incorporación y clasificación de recibos y movimientos bancarios, reduciendo al mínimo el trabajo manual mediante el uso de IA local (Ollama).

### 1.1. Canales de entrada

La aplicación dispondrá de dos canales de entrada, cada uno con un rol diferente:

**Bot de Telegram (canal principal de entrada).** La forma habitual de incorporar recibos y documentos será mediante un bot de Telegram. El usuario envía una fotografía, un PDF o un fichero CSV al bot, y el sistema lo procesa automáticamente, devolviendo un resumen del resultado directamente en el chat.

**Interfaz web (centro de control).** La web permite también subir documentos, pero su función principal es la gestión, consulta y revisión: informes, estadísticas, corrección de clasificaciones, gestión de reglas, cuentas, clientes y categorías.

### 1.2. Tipos de documentos

Independientemente del canal, la aplicación acepta dos tipos de entrada:

1. **Documentos individuales**: recibos escaneados, fotografías o PDFs.
2. **Extractos bancarios**: ficheros CSV o PDF con múltiples movimientos.

Independientemente del origen y del canal, toda la información deberá terminar representada internamente de una forma homogénea.

### Funciones de la IA

- Extraer información de documentos.
- Identificar movimientos.
- Asociar movimientos a cuentas bancarias.
- Asociar cuentas a clientes.
- Identificar y normalizar conceptos.
- Clasificar movimientos por categoría.
- Detectar posibles duplicados.
- Proponer correcciones o clasificaciones basándose en historial y feedback previo.

El usuario deberá poder revisar y corregir todas las decisiones de la IA.

---

## 2. Principio fundamental de funcionamiento

La aplicación diferenciará entre tres tipos de información según su origen y fiabilidad:

### 2.1. Información conocida

Información introducida explícitamente por el usuario y que se considera fiable.

- Una cuenta bancaria pertenece a un banco.
- Una cuenta bancaria está asociada a un cliente.
- Una determinada descripción suele corresponder a una compañía.
- Un determinado IBAN identifica una cuenta concreta.

### 2.2. Información extraída

Información obtenida de un documento mediante OCR, lectura de PDF, CSV u otros mecanismos.

- Fecha, importe, concepto, IBAN, nombre, número de recibo.

### 2.3. Información inferida

Información que la aplicación propone basándose en reglas, historial o IA.

- "Este movimiento probablemente pertenece a María."
- "Este recibo corresponde a electricidad."
- "Este movimiento pertenece a la cuenta ESXX...1234."

La aplicación deberá mantener diferenciadas estas tres situaciones en todo momento, asegurando la trazabilidad del origen de cada dato.

---

## 3. Modelo de usuarios y clientes

### 3.1. Usuarios

Los usuarios son las personas que operan la aplicación. Tienen acceso al sistema, pueden iniciar sesión, importar documentos, revisar clasificaciones y gestionar la configuración.

**Información del usuario:**

- Nombre de usuario
- Email
- Contraseña (hash)
- Rol (administrador, usuario estándar)
- Fecha de alta
- Estado: activo/inactivo
- Preferencias de la aplicación

La aplicación es multiusuario. Cada usuario puede gestionar las finanzas de uno o varios clientes.

### 3.2. Clientes

Los clientes representan a los titulares o personas relacionadas con las cuentas bancarias. No son usuarios de la aplicación y no tienen acceso al sistema.

Un cliente es un dato del dominio, equivalente en importancia a un banco o una categoría. Representa a quién pertenece una cuenta y sus recibos.

**Información del cliente:**

- Nombre
- Apellidos
- Alias opcional
- Notas
- Cuentas asociadas

Ejemplo: "Juan Pérez" puede estar asociado a una cuenta BBVA y una cuenta Santander. Otra persona puede compartir una de esas cuentas con Juan.

### 3.3. Relaciones

- Un usuario puede gestionar varios clientes.
- Un cliente puede tener varias cuentas bancarias.
- Una cuenta bancaria puede tener varios clientes (cotitularidad).
- Relación: **Usuario → Cliente ↔ Cuenta bancaria**.

---

## 4. Entidades principales

El MVP estará compuesto por las siguientes entidades:

- Usuario
- Cliente (persona/titular)
- Banco
- Cuenta bancaria
- Documento
- Movimiento / Recibo
- Concepto
- Categoría
- Regla de clasificación
- Perfil de importación
- Registro de auditoría
- Feedback de clasificación

---

## 5. Bancos

Un banco representa una entidad financiera.

**Información:**

- Nombre
- Identificador interno
- Información adicional opcional
- Perfiles de importación asociados

Ejemplos: BBVA, CaixaBank, Banco Santander.

Una misma aplicación podrá gestionar cuentas de múltiples bancos.

---

## 6. Cuentas bancarias

La cuenta bancaria es una de las entidades centrales de la aplicación.

**Información:**

- Banco
- IBAN
- Nombre o alias de la cuenta
- Clientes asociados
- Fecha de alta
- Estado: activa/inactiva
- Notas

> Ejemplo: Banco BBVA, IBAN ESXX XXXX XXXX XXXX XXXX, Alias: Cuenta familiar.

Una cuenta bancaria podrá estar asociada a uno o varios clientes.

---

## 7. Documentos

Un documento representa el origen de uno o varios movimientos. Puede proceder de:

- PDF bancario
- CSV bancario
- Fotografía
- Escaneo
- PDF de un recibo
- Otro documento compatible

El documento original deberá conservarse siempre como referencia.

**Información:**

- Nombre del fichero
- Tipo (CSV, PDF, imagen, etc.)
- Fecha de importación
- Banco de origen (si se conoce)
- Cuenta de origen (si se conoce)
- Perfil de importación utilizado
- Número de movimientos detectados
- Estado del procesamiento
- Fecha de procesamiento
- Resultado del procesamiento

### 7.1. Estados del documento

| Estado | Descripción |
|---|---|
| **Pendiente** | El documento ha sido subido pero no procesado. |
| **Procesando** | La IA o el parser están extrayendo movimientos. |
| **Procesado** | Movimientos extraídos correctamente. |
| **Requiere revisión** | Se han detectado problemas o ambigüedades. |
| **Revisado** | Un usuario ha verificado el resultado. |
| **Error** | No se pudo procesar el documento. |

---

## 8. Movimientos / Recibos

El movimiento es la unidad principal de información que la aplicación gestiona.

Un documento puede contener uno o muchos movimientos. Un CSV bancario puede contener cientos de movimientos. Un recibo individual normalmente contendrá uno solo.

### 8.1. Información básica

- Fecha
- Fecha de valor (si existe)
- Importe
- Moneda
- Concepto original
- Concepto normalizado
- Descripción original
- Cuenta bancaria
- Cliente
- Categoría
- Documento de origen

### 8.2. Información adicional

- Identificador del movimiento en el banco (si existe)
- Referencia
- Contrapartida
- IBAN de origen/destino (si aparece)
- Nombre de la contrapartida
- Observaciones
- Etiquetas

### 8.3. Ciclo de vida del movimiento

Cada movimiento individual tendrá su propio estado, independiente del estado del documento que lo contiene:

| Estado | Descripción |
|---|---|
| **Pendiente de clasificación** | Recién importado, sin clasificar. |
| **Clasificado automáticamente** | Clasificado por reglas o IA, pendiente de revisión humana. |
| **Revisado manualmente** | Un usuario ha verificado y/o corregido la clasificación. |
| **Descartado** | Movimiento irrelevante o erróneo, excluido del análisis. |
| **Marcado como duplicado** | Se ha identificado como duplicado de otro movimiento. |

Las transiciones entre estados se gestionarán mediante un State Machine (Symfony Workflow component).

---

## 9. Concepto

El concepto representa la descripción del movimiento. Es importante conservar siempre el concepto original tal como aparece en el documento.

> Ejemplo original: "TRF SEPA RECIBIDA JUAN PEREZ"
>
> Concepto normalizado: "Transferencia Juan Pérez"

Nunca debería perderse el texto original. Esto permitirá mejorar las reglas de clasificación y alimentar el sistema de aprendizaje.

---

## 10. Categorías

Las categorías permiten organizar los movimientos.

**Categorías iniciales sugeridas:**

- Alimentación
- Electricidad
- Agua
- Gas
- Internet / Telecomunicaciones
- Seguros
- Impuestos
- Transporte
- Vivienda
- Salud
- Ocio
- Transferencias
- Ingresos
- Otros

El sistema deberá permitir crear, modificar y eliminar categorías. La categoría no debe confundirse con la cuenta bancaria.

> Ejemplo: Cuenta BBVA Juan/María → Concepto: Mercadona → Categoría: Alimentación.

---

## 11. Clasificación mediante IA

La IA actúa como asistente de clasificación y no como fuente definitiva de verdad. El servicio de IA se ejecuta en Ollama, desplegado en un contenedor Docker independiente en la misma máquina.

### 11.1. Campos que la IA puede proponer

- Cuenta bancaria
- Cliente
- Concepto normalizado
- Categoría
- Contrapartida
- Etiquetas
- Posible duplicado

### 11.2. Nivel de confianza

La IA deberá devolver un nivel de confianza para cada campo propuesto:

> Cuenta: ESXX...1234 → Confianza: 99%
>
> Cliente: María → Confianza: 96%
>
> Categoría: Alimentación → Confianza: 98%

### 11.3. Few-shot prompting contextual

Para mejorar las clasificaciones sin necesidad de fine-tuning del modelo, el sistema utilizará few-shot prompting contextual: antes de clasificar un movimiento nuevo, buscará en la base de datos los N movimientos más similares que ya hayan sido revisados manualmente y los incluirá como ejemplos en el prompt enviado a Ollama.

---

## 12. Reglas de clasificación

La aplicación distingue entre reglas deterministas e IA. Las reglas tienen prioridad cuando la información permite determinar algo de forma inequívoca.

### 12.1. Ejemplos de reglas

> Si el IBAN es ESXX...1234 → Cuenta "Cuenta familiar"
>
> Si el concepto contiene "MERCADONA" → Categoría "Alimentación"
>
> Si la cuenta es X → Cliente "Juan"

### 12.2. Flujo de clasificación

El flujo de clasificación sigue una cadena de prioridad:

**Reglas conocidas → IA (Ollama) → Revisión humana**

La IA se utiliza únicamente cuando las reglas existentes no son suficientes para determinar un campo.

### 12.3. Gestión de reglas

Las reglas podrán:

- Crearse manualmente por el usuario.
- Generarse automáticamente a partir de correcciones frecuentes del usuario.
- Tener un orden de prioridad para resolver conflictos entre reglas.
- Activarse o desactivarse individualmente.

---

## 13. Aprendizaje a partir de correcciones

Una de las funciones más importantes del sistema es aprovechar las correcciones realizadas por el usuario para mejorar futuras clasificaciones.

### 13.1. Registro de feedback

Cuando la IA propone una clasificación y el usuario la corrige, el sistema registra el feedback en una tabla específica (`classification_feedback`):

- Concepto original
- Concepto normalizado
- Categoría propuesta por la IA
- Categoría final del usuario
- Cliente propuesto por la IA
- Cliente final del usuario
- Embedding del concepto (para búsqueda semántica futura)

### 13.2. Uso del feedback

El feedback se utiliza de dos formas:

- Como ejemplos en el few-shot prompting de Ollama (corto plazo).
- Como base para generar nuevas reglas automáticas cuando un patrón se repite con frecuencia (medio plazo).

Esto permite que la aplicación construya progresivamente conocimiento sobre los datos del usuario sin necesidad de entrenar un modelo de IA.

---

## 14. Auditoría e historial de cambios

El sistema mantendrá un registro de auditoría completo e independiente del feedback de clasificación.

### 14.1. Registro de auditoría (`audit_log`)

Cada vez que una entidad cambie, se registrará:

- Tipo de entidad (movimiento, cuenta, cliente, etc.)
- ID de la entidad
- Campo modificado
- Valor anterior
- Valor nuevo
- Usuario que realizó el cambio
- Fecha y hora del cambio
- Origen del cambio (manual, regla, IA)

### 14.2. Implementación

Se implementará mediante un EventSubscriber de Doctrine que escuche los eventos `preUpdate` y `postUpdate`, generando registros de auditoría de forma transparente para el resto de la aplicación.

### 14.3. Diferencia con el feedback

El `audit_log` es para trazabilidad general de cualquier cambio en el sistema. El `classification_feedback` es específico para alimentar el sistema de aprendizaje de la IA. Ambos son complementarios.

---

## 15. Detección de duplicados

Al tratarse de recibos recurrentes (suministros, seguros, cuotas), muchos movimientos se repiten mensualmente con importes iguales o similares.

### 15.1. Criterios de detección

La detección de duplicados se basará inicialmente en la combinación de:

- Fecha del movimiento (mismo día o ventana temporal configurable)
- Importe exacto
- Cuenta bancaria

Estos criterios se irán refinando progresivamente según la experiencia de uso.

### 15.2. Casos especiales

- Importación del mismo fichero CSV dos veces.
- Un movimiento que aparece tanto en un recibo individual como en un extracto bancario.
- Movimientos recurrentes con el mismo importe en meses distintos (no son duplicados).

### 15.3. Comportamiento

Cuando se detecte un posible duplicado, el sistema lo marcará pero no lo eliminará automáticamente. El usuario decidirá si confirmar el duplicado o mantener ambos movimientos.

---

## 16. Perfiles de importación

Cada banco utiliza un formato diferente para sus extractos. Los perfiles de importación definen cómo interpretar cada formato.

### 16.1. Auto-detección

Al importar un documento, el sistema intentará detectar automáticamente el banco y formato de origen:

- Para CSV: análisis de cabeceras, separador, codificación.
- Para PDF: búsqueda de logos, texto del banco, patrones de IBAN.
- Ollama puede asistir analizando las primeras líneas del fichero.

Si el sistema no puede identificar el formato automáticamente, solicitará al usuario que lo seleccione de los perfiles existentes o que cree uno nuevo.

### 16.2. Información del perfil

- Banco asociado
- Cuenta asociada (opcional, si un banco usa formatos distintos por tipo de cuenta)
- Mapeo de columnas (qué columna corresponde a fecha, concepto, importe, etc.)
- Formato de fecha (`dd/mm/yyyy`, `yyyy-mm-dd`, etc.)
- Formato de importes (coma/punto decimal, signo negativo, columnas separadas de debe/haber)
- Separador de CSV (coma, punto y coma, tabulador)
- Codificación del fichero (UTF-8, ISO-8859-1, etc.)
- Filas a ignorar (cabeceras, pies, resúmenes)

### 16.3. Editor visual

Para formatos no reconocidos, la aplicación ofrecerá un editor visual donde el usuario verá las primeras filas del fichero y podrá asignar cada columna al campo correspondiente. Esto generará un nuevo perfil reutilizable en futuras importaciones.

---

## 17. Importación de CSV

El usuario podrá importar un CSV procedente de un banco.

El sistema utilizará el perfil de importación correspondiente para detectar o solicitar la correspondencia entre las columnas del fichero y los campos internos.

```
Fecha    → Fecha
Concepto → Concepto
Importe  → Importe
IBAN     → IBAN
```

El procesamiento de ficheros grandes (cientos de movimientos) se realizará de forma asíncrona mediante workers (Symfony Messenger) para no bloquear la interfaz de usuario.

---

## 18. Bot de Telegram

El bot de Telegram es el canal principal de entrada de documentos. Permite subir recibos y extractos de forma rápida desde el móvil y recibir un resumen del procesado directamente en el chat.

### 18.1. Flujo principal

1. El usuario envía una fotografía, PDF o fichero CSV al bot de Telegram.
2. Telegram envía un webhook al backend Symfony.
3. El controller del webhook identifica al usuario mediante el `telegram_chat_id`, descarga el fichero vía la API de Telegram y crea un `Document` con `source=telegram`.
4. Se despacha un command asíncrono vía Symfony Messenger para procesar el documento.
5. El worker procesa el documento: OCR vía Ollama (modelo de visión) para imágenes, parser para CSV/PDF.
6. Se aplica el pipeline de clasificación: reglas → IA → pendiente de revisión.
7. Al terminar, el sistema envía un mensaje de vuelta al chat de Telegram con el resumen.

### 18.2. Formato del resumen

El bot responde con un mensaje estructurado con la información del procesado:

```
📄 Recibo procesado
🏦 BBVA — Cuenta familiar
👤 María
📅 15/08/2026
💰 -45,30 €
🏷 Mercadona → Alimentación
✅ Clasificado automáticamente (confianza: 97%)
```

Para extractos con múltiples movimientos:

```
📄 Extracto procesado
🏦 BBVA — Cuenta familiar
📊 47 movimientos importados
✅ 38 clasificados automáticamente
⚠️ 7 requieren revisión
🔄 2 posibles duplicados
🔗 Ver detalle en la web: [enlace]
```

### 18.3. Vinculación Telegram ↔ Usuario

El usuario debe vincular su cuenta de Telegram con su usuario de la aplicación. Para el MVP, se almacena el `telegram_chat_id` en la entidad `User`. El proceso de vinculación se realiza desde la web (el usuario genera un código, lo envía al bot, y el bot asocia el `chat_id` al usuario).

### 18.4. Gestión de errores

Si el documento no se puede procesar, el bot notifica al usuario:

```
❌ No se pudo procesar el documento
📎 archivo_recibo.pdf
💬 Formato no reconocido. Puedes asignar un perfil de importación desde la web.
```

### 18.5. Evolución futura

En versiones posteriores, el bot podrá ofrecer botones inline de Telegram para que el usuario realice correcciones directamente desde el chat:

- Cambiar categoría (selector con las categorías más frecuentes).
- Confirmar o descartar un movimiento.
- Marcar como duplicado.
- Asignar a otro cliente.

Esto se implementará cuando el flujo básico de subida y resumen esté consolidado.

---

## 19. Arquitectura técnica

### 19.1. Stack tecnológico

- **Backend API:** Symfony (PHP) con arquitectura hexagonal.
- **Bot Telegram:** Webhook en Symfony, adaptador en Infrastructure/Telegram.
- **IA:** Ollama en contenedor Docker separado, comunicación vía HTTP.
- **Procesamiento asíncrono:** Symfony Messenger con workers.
- **Base de datos:** PostgreSQL.

### 19.2. Principios arquitectónicos

- Arquitectura hexagonal: dominio limpio desacoplado de la infraestructura.
- Puertos para clasificación y extracción.
- Adaptadores para Ollama, parsers de CSV, lectores de PDF, Telegram Bot API.
- El adaptador de IA abstrae el modelo concreto para facilitar cambios futuros.
- State Machine (Symfony Workflow) para gestionar transiciones de estado.
- Event Subscribers de Doctrine para auditoría transparente.
- Telegram y la web son dos adaptadores de entrada distintos que comparten los mismos casos de uso del dominio.

### 19.3. Servicios

- Servicio de clasificación: pipeline Reglas → IA → Revisión.
- Servicio de importación: detección de formato + parsing + creación de movimientos.
- Servicio de detección de duplicados.
- Servicio de auditoría.
- Servicio de feedback y aprendizaje.
- Servicio de notificación Telegram: envío de resúmenes y errores al chat del usuario.

---

## 20. Pendientes y decisiones futuras

- Definir el modelo de IA concreto para Ollama.
- Detallar la API REST (endpoints, DTOs, validaciones).
- Definir los flujos de usuario (pantallas, UX).
- Motor de búsqueda de movimientos y filtros avanzados.
- Conciliación: cruce de movimientos entre fuentes (extracto vs recibos).
- Exportación de datos y generación de informes.
- Refinamiento de criterios de detección de duplicados.
- Búsqueda semántica con embeddings para mejorar el few-shot prompting.
- Dashboard con resúmenes y estadísticas.
- Correcciones inline desde Telegram (botones para cambiar categoría, confirmar, descartar).
- Comandos del bot: `/status`, `/last`, `/help`, consultas rápidas.

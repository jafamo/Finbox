# Arquitectura

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

## Convenciones de código

- Arquitectura hexagonal: dominio limpio, sin dependencias de infraestructura
- Telegram y web son adaptadores de entrada que comparten los mismos casos de uso
- UUIDs como identificadores en todas las entidades
- Symfony Workflow para state machines
- Symfony Messenger para procesamiento asíncrono (imports de CSV, clasificación IA, notificaciones)
- EventSubscriber de Doctrine para auditoría automática
- Adapter pattern para Ollama y Telegram (implementan interfaces de dominio)
- Campos nullable cuando el dato puede no conocerse al momento de la importación
- Todo el código PHP sigue **PSR-12** (verificar con `php-cs-fixer`/`phpcs` antes de dar una tarea por terminada)
- Las vistas del frontal se hacen con **Twig**. No se introduce ningún framework de frontend externo (React, Vue, etc.); si hace falta interactividad, se usa JS del propio ecosistema Symfony (Stimulus/Turbo), nunca una SPA aparte

## API: documentación y pruebas

- Cada endpoint nuevo se documenta con **Swagger/OpenAPI** (atributos `#[OA\...]` en el controller, vía `nelmio/api-doc-bundle` o equivalente) — no se da un endpoint por terminado sin su documentación
- Cada endpoint se añade también a un fichero **`.http`** en `http/`, agrupado por recurso (p. ej. `http/movements.http`, `http/clients.http`), con una petición de ejemplo por endpoint separada por `###` — sirve como colección tipo Postman ejecutable desde el editor (REST Client / HTTP Client)

## Testing

- Cada caso de uso y regla de negocio no trivial lleva su **test unitario con PHPUnit**, en `tests/`, misma estructura de carpetas que `src/` y sufijo `*Test.php`
- Los tests se centran en `Domain/` y `Application/`, que no dependen de infraestructura y no necesitan mocks pesados

## Identificadores

Todas las entidades usan UUID (no autoincrement). Se generan antes de persistir, lo que facilita el procesamiento asíncrono con Messenger.

## Almacenamiento de documentos

Los ficheros originales (fotos, PDF, CSV) recibidos por Telegram o subidos por la web nunca se guardan en base de datos, ni se descartan tras procesarlos:

- El fichero físico se guarda en el **filesystem del servidor**, en un **volumen dedicado** (montado aparte en Docker Compose, independiente del volumen de PostgreSQL), sobre el que se hacen copias/backups del documento.
- `Document.storage_path` guarda solo la **referencia** a esa ruta — la base de datos nunca contiene el binario.
- El acceso a los ficheros pasa siempre por un adaptador de `Infrastructure/` (no se lee `storage_path` directamente desde el dominio); esto es lo que permitiría cambiar a almacenamiento tipo S3/object storage más adelante sin tocar `Domain/Application`.
- El documento original nunca se sobrescribe ni se borra automáticamente, igual que `original_concept` en `Movement` — es la fuente de verdad ante cualquier corrección o disputa.

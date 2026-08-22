# Control de versiones

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

## Spec-driven development (OpenSpec)

El proyecto usa **OpenSpec** para gestionar cambios significativos como propuestas escritas antes de implementar. Vive en `/openspec/`:

- `openspec/specs/` — especificaciones vigentes del comportamiento actual del sistema
- `openspec/changes/` — propuestas de cambio en curso (proposal, design, tasks)
- `openspec/changes/archive/` — cambios ya aplicados y archivados

Flujo con los slash commands (`.claude/commands/opsx/`):

```bash
/opsx:propose "descripción del cambio"   # crea la propuesta y sus artefactos (proposal, design, tasks)
/opsx:apply                              # implementa las tasks de un cambio ya propuesto
/opsx:sync                               # sincroniza specs con el código tras aplicar
/opsx:archive                            # archiva un cambio completado
/opsx:explore                            # explora specs y cambios existentes
```

Cada cambio significativo (nueva entidad, nuevo flujo, cambio de contrato) debe pasar primero por una propuesta OpenSpec antes de tocar código. Combínalo con Git Flow: la rama `feature/<nombre>` se abre para implementar un cambio ya propuesto en `openspec/changes/`.

## Alcance de los cambios: mantenerlos pequeños

Un `change` de OpenSpec, una rama y un PR deben coincidir 1:1. Antes de proponer o implementar, aplica estas reglas para evitar PRs gigantes:

- **Un change = un caso de uso o una entidad, no una épica.** "Añadir entidad Client con CRUD básico" es un change válido. "Sistema de gestión de clientes" no lo es — hay que trocearlo primero (p. ej. `add-client-entity`, luego `add-client-web-crud`, luego `add-client-account-linking`).
- **Verticalidad sobre completitud.** Prefiere un change que atraviese las capas (dominio → aplicación → infraestructura) para una sola funcionalidad, a uno que intente cubrir todas las variantes de un caso de uso de golpe.
- **Señal de que hay que dividir:** si `tasks.md` supera ~8-10 tareas, o el change toca más de ~10-15 ficheros nuevos/modificados, o mezcla más de una entidad de dominio sin relación directa, propón dividirlo en varios changes secuenciales antes de generar `tasks.md`.
- **Cada change debe ser mergeable de forma independiente.** Si una tarea deja el sistema en estado inconsistente sin la siguiente change, probablemente están mal cortadas — revisa las dependencias.
- **Antes de un change grande, usa `/opsx:explore`** para acotar el alcance y decidir el troceo, en vez de lanzar `/opsx:propose` directo sobre una idea amplia.
- Si el usuario pide algo grande, el primer paso es proponer la lista de changes pequeños en los que se va a dividir, no generar tasks.md de una vez.

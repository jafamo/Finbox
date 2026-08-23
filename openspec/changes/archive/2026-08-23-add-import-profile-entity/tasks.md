## 1. Dominio

- [x] 1.1 Crear `Domain/Enum/ImportSourceFormat.php` (`csv`, `pdf_layout`)
- [x] 1.2 Crear `Domain/Entity/ImportProfile.php` con `id` (UUID), `bank_id`, `name`, `source_format`, `date_format`, `decimal_separator`, `parser_config` (json, nullable), `encoding` (nullable), `is_active`
- [x] 1.3 Crear `Domain/Repository/ImportProfileRepositoryInterface.php` (save, findById, findAll, remove)

## 2. Infraestructura — persistencia

- [x] 2.1 Añadir mapping Doctrine (atributos `#[ORM\...]`) a `ImportProfile`, con FK a `bank`
- [x] 2.2 Crear `Infrastructure/Persistence/DoctrineImportProfileRepository.php` implementando `ImportProfileRepositoryInterface`
- [x] 2.3 Generar migración Doctrine para la tabla `import_profile`

## 3. Aplicación — casos de uso

- [x] 3.1 `CreateImportProfileCommand` + `CreateImportProfileHandler` (valida que `bank_id` exista y que `source_format` sea uno de los valores del enum)
- [x] 3.2 `UpdateImportProfileCommand` + `UpdateImportProfileHandler`
- [x] 3.3 `DeleteImportProfileCommand` + `DeleteImportProfileHandler`
- [x] 3.4 `GetImportProfileQuery` + `GetImportProfileHandler`
- [x] 3.5 `ListImportProfilesQuery` + `ListImportProfilesHandler`

## 4. Infraestructura — API HTTP

- [x] 4.1 Crear `Infrastructure/Http/ImportProfileController.php` con endpoints POST/GET/GET(id)/PUT/DELETE sobre `/api/import-profiles`
- [x] 4.2 Documentar cada endpoint con atributos `#[OA\...]` (Swagger/OpenAPI)
- [x] 4.3 Añadir `http/import-profiles.http` con una petición de ejemplo por endpoint

## 5. Tests

- [x] 5.1 Test unitario `CreateImportProfileHandler` (alta válida, sin `bank_id`, `bank_id` inexistente, `source_format` inválido)
- [x] 5.2 Test unitario `UpdateImportProfileHandler` (edición válida, perfil inexistente)
- [x] 5.3 Test unitario `DeleteImportProfileHandler` (eliminación válida, perfil inexistente)
- [x] 5.4 Test unitario `GetImportProfileHandler` y `ListImportProfilesHandler`

## 6. Documentación

- [x] 6.1 Actualizar `docs/claude/modelo-dominio.md`: renombrar `column_mapping` a `parser_config` en `ImportProfile` y documentar `source_format`, dejando constancia de que la forma de `parser_config` depende de `source_format` (`csv` vs `pdf_layout`)

## 7. Verificación

- [x] 7.1 Ejecutar `php-cs-fixer`/`phpcs` sobre el código nuevo (PSR-12)
- [x] 7.2 Ejecutar la suite de tests PHPUnit y confirmar que pasa
- [x] 7.3 Probar manualmente los endpoints con `http/import-profiles.http` (rutas verificadas con `debug:router`; el 401 JWT es preexistente e idéntico al de `/api/banks`, no hay claves JWT generadas en el entorno — fuera de alcance de este change)

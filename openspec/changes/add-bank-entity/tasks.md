## 1. Dominio

- [x] 1.1 Crear `Domain/Entity/Bank.php` con `id` (UUID), `name`, `internal_code` (nullable), `notes` (nullable)
- [x] 1.2 Crear `Domain/Repository/BankRepositoryInterface.php` (save, findById, findAll, remove)

## 2. Infraestructura — persistencia

- [x] 2.1 Añadir mapping Doctrine (atributos `#[ORM\...]`) a `Bank`
- [x] 2.2 Crear `Infrastructure/Persistence/DoctrineBankRepository.php` implementando `BankRepositoryInterface`
- [x] 2.3 Generar migración Doctrine para la tabla `bank`

## 3. Aplicación — casos de uso

- [x] 3.1 `CreateBankCommand` + `CreateBankHandler`
- [x] 3.2 `UpdateBankCommand` + `UpdateBankHandler`
- [x] 3.3 `DeleteBankCommand` + `DeleteBankHandler`
- [x] 3.4 `GetBankQuery` + `GetBankHandler`
- [x] 3.5 `ListBanksQuery` + `ListBanksHandler`

## 4. Infraestructura — API HTTP

- [x] 4.1 Crear `Infrastructure/Http/BankController.php` con endpoints POST/GET/GET(id)/PUT/DELETE sobre `/api/banks`
- [x] 4.2 Documentar cada endpoint con atributos `#[OA\...]` (Swagger/OpenAPI)
- [x] 4.3 Añadir `http/banks.http` con una petición de ejemplo por endpoint

## 5. Tests

- [x] 5.1 Test unitario `CreateBankHandler` (alta válida, alta sin nombre)
- [x] 5.2 Test unitario `UpdateBankHandler` (edición válida, banco inexistente)
- [x] 5.3 Test unitario `DeleteBankHandler` (eliminación válida, banco inexistente)
- [x] 5.4 Test unitario `GetBankHandler` y `ListBanksHandler`

## 6. Verificación

- [x] 6.1 Ejecutar `php-cs-fixer`/`phpcs` sobre el código nuevo (PSR-12)
- [x] 6.2 Ejecutar la suite de tests PHPUnit y confirmar que pasa
- [x] 6.3 Probar manualmente los endpoints con `http/banks.http`

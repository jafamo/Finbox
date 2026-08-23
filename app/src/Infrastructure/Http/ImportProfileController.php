<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Command\CreateImportProfileCommand;
use App\Application\Command\DeleteImportProfileCommand;
use App\Application\Command\UpdateImportProfileCommand;
use App\Application\Handler\CreateImportProfileHandler;
use App\Application\Handler\DeleteImportProfileHandler;
use App\Application\Handler\GetImportProfileHandler;
use App\Application\Handler\ListImportProfilesHandler;
use App\Application\Handler\UpdateImportProfileHandler;
use App\Application\Query\GetImportProfileQuery;
use App\Application\Query\ListImportProfilesQuery;
use App\Domain\Entity\ImportProfile;
use App\Domain\Exception\BankNotFoundException;
use App\Domain\Exception\ImportProfileNotFoundException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/import-profiles')]
final class ImportProfileController
{
    public function __construct(
        private readonly CreateImportProfileHandler $createImportProfileHandler,
        private readonly UpdateImportProfileHandler $updateImportProfileHandler,
        private readonly DeleteImportProfileHandler $deleteImportProfileHandler,
        private readonly GetImportProfileHandler $getImportProfileHandler,
        private readonly ListImportProfilesHandler $listImportProfilesHandler,
    ) {
    }

    #[Route('', name: 'import_profile_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/import-profiles',
        summary: 'Crear un perfil de importación',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['bankId', 'name', 'sourceFormat'],
                properties: [
                    new OA\Property(property: 'bankId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'name', type: 'string', example: 'Open Bank - extracto PDF'),
                    new OA\Property(property: 'sourceFormat', type: 'string', enum: ['csv', 'pdf_layout']),
                    new OA\Property(property: 'dateFormat', type: 'string', nullable: true, example: 'Y-m-d'),
                    new OA\Property(property: 'decimalSeparator', type: 'string', nullable: true, example: '.'),
                    new OA\Property(property: 'parserConfig', type: 'object', nullable: true),
                    new OA\Property(property: 'encoding', type: 'string', nullable: true, example: 'UTF-8'),
                    new OA\Property(property: 'isActive', type: 'boolean', nullable: true, example: true),
                ],
            ),
        ),
        tags: ['ImportProfiles'],
        responses: [
            new OA\Response(response: 201, description: 'Perfil de importación creado'),
            new OA\Response(response: 400, description: 'Datos inválidos'),
        ],
    )]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        try {
            $command = new CreateImportProfileCommand(
                Uuid::fromString((string) ($payload['bankId'] ?? '')),
                (string) ($payload['name'] ?? ''),
                (string) ($payload['sourceFormat'] ?? ''),
                $payload['dateFormat'] ?? null,
                $payload['decimalSeparator'] ?? null,
                $payload['parserConfig'] ?? null,
                $payload['encoding'] ?? null,
                $payload['isActive'] ?? true,
            );
            $importProfile = ($this->createImportProfileHandler)($command);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        } catch (BankNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        }

        return new JsonResponse($this->toArray($importProfile), 201);
    }

    #[Route('', name: 'import_profile_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/import-profiles',
        summary: 'Listar perfiles de importación',
        tags: ['ImportProfiles'],
        responses: [new OA\Response(response: 200, description: 'Listado de perfiles de importación')],
    )]
    public function list(): JsonResponse
    {
        $importProfiles = ($this->listImportProfilesHandler)(new ListImportProfilesQuery());

        return new JsonResponse(array_map($this->toArray(...), $importProfiles));
    }

    #[Route('/{id}', name: 'import_profile_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/import-profiles/{id}',
        summary: 'Obtener un perfil de importación por id',
        tags: ['ImportProfiles'],
        responses: [
            new OA\Response(response: 200, description: 'Perfil de importación encontrado'),
            new OA\Response(response: 404, description: 'Perfil de importación no encontrado'),
        ],
    )]
    public function get(string $id): JsonResponse
    {
        try {
            $importProfile = ($this->getImportProfileHandler)(new GetImportProfileQuery(Uuid::fromString($id)));
        } catch (ImportProfileNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        }

        return new JsonResponse($this->toArray($importProfile));
    }

    #[Route('/{id}', name: 'import_profile_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/import-profiles/{id}',
        summary: 'Editar un perfil de importación',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'sourceFormat'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Open Bank - extracto PDF'),
                    new OA\Property(property: 'sourceFormat', type: 'string', enum: ['csv', 'pdf_layout']),
                    new OA\Property(property: 'dateFormat', type: 'string', nullable: true, example: 'Y-m-d'),
                    new OA\Property(property: 'decimalSeparator', type: 'string', nullable: true, example: '.'),
                    new OA\Property(property: 'parserConfig', type: 'object', nullable: true),
                    new OA\Property(property: 'encoding', type: 'string', nullable: true, example: 'UTF-8'),
                    new OA\Property(property: 'isActive', type: 'boolean', nullable: true, example: true),
                ],
            ),
        ),
        tags: ['ImportProfiles'],
        responses: [
            new OA\Response(response: 200, description: 'Perfil de importación actualizado'),
            new OA\Response(response: 400, description: 'Datos inválidos'),
            new OA\Response(response: 404, description: 'Perfil de importación no encontrado'),
        ],
    )]
    public function update(string $id, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        try {
            $command = new UpdateImportProfileCommand(
                Uuid::fromString($id),
                (string) ($payload['name'] ?? ''),
                (string) ($payload['sourceFormat'] ?? ''),
                $payload['dateFormat'] ?? null,
                $payload['decimalSeparator'] ?? null,
                $payload['parserConfig'] ?? null,
                $payload['encoding'] ?? null,
                $payload['isActive'] ?? true,
            );
            $importProfile = ($this->updateImportProfileHandler)($command);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        } catch (ImportProfileNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        }

        return new JsonResponse($this->toArray($importProfile));
    }

    #[Route('/{id}', name: 'import_profile_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/import-profiles/{id}',
        summary: 'Eliminar un perfil de importación',
        tags: ['ImportProfiles'],
        responses: [
            new OA\Response(response: 204, description: 'Perfil de importación eliminado'),
            new OA\Response(response: 404, description: 'Perfil de importación no encontrado'),
        ],
    )]
    public function delete(string $id): JsonResponse
    {
        try {
            ($this->deleteImportProfileHandler)(new DeleteImportProfileCommand(Uuid::fromString($id)));
        } catch (ImportProfileNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * @return array{
     *     id: string,
     *     bankId: string,
     *     name: string,
     *     sourceFormat: string,
     *     dateFormat: ?string,
     *     decimalSeparator: ?string,
     *     parserConfig: ?array,
     *     encoding: ?string,
     *     isActive: bool,
     * }
     */
    private function toArray(ImportProfile $importProfile): array
    {
        return [
            'id' => $importProfile->id()->toRfc4122(),
            'bankId' => $importProfile->bank()->id()->toRfc4122(),
            'name' => $importProfile->name(),
            'sourceFormat' => $importProfile->sourceFormat()->value,
            'dateFormat' => $importProfile->dateFormat(),
            'decimalSeparator' => $importProfile->decimalSeparator(),
            'parserConfig' => $importProfile->parserConfig(),
            'encoding' => $importProfile->encoding(),
            'isActive' => $importProfile->isActive(),
        ];
    }
}

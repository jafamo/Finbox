<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Command\CreateBankCommand;
use App\Application\Command\DeleteBankCommand;
use App\Application\Command\UpdateBankCommand;
use App\Application\Handler\CreateBankHandler;
use App\Application\Handler\DeleteBankHandler;
use App\Application\Handler\GetBankHandler;
use App\Application\Handler\ListBanksHandler;
use App\Application\Handler\UpdateBankHandler;
use App\Application\Query\GetBankQuery;
use App\Application\Query\ListBanksQuery;
use App\Domain\Entity\Bank;
use App\Domain\Exception\BankNotFoundException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/banks')]
final class BankController
{
    public function __construct(
        private readonly CreateBankHandler $createBankHandler,
        private readonly UpdateBankHandler $updateBankHandler,
        private readonly DeleteBankHandler $deleteBankHandler,
        private readonly GetBankHandler $getBankHandler,
        private readonly ListBanksHandler $listBanksHandler,
    ) {
    }

    #[Route('', name: 'bank_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/banks',
        summary: 'Crear un banco',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'BBVA'),
                    new OA\Property(property: 'internalCode', type: 'string', nullable: true),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                ],
            ),
        ),
        tags: ['Banks'],
        responses: [
            new OA\Response(response: 201, description: 'Banco creado'),
            new OA\Response(response: 400, description: 'Datos inválidos'),
        ],
    )]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        try {
            $command = new CreateBankCommand(
                (string) ($payload['name'] ?? ''),
                $payload['internalCode'] ?? null,
                $payload['notes'] ?? null,
            );
            $bank = ($this->createBankHandler)($command);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        }

        return new JsonResponse($this->toArray($bank), 201);
    }

    #[Route('', name: 'bank_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/banks',
        summary: 'Listar bancos',
        tags: ['Banks'],
        responses: [new OA\Response(response: 200, description: 'Listado de bancos')],
    )]
    public function list(): JsonResponse
    {
        $banks = ($this->listBanksHandler)(new ListBanksQuery());

        return new JsonResponse(array_map($this->toArray(...), $banks));
    }

    #[Route('/{id}', name: 'bank_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/banks/{id}',
        summary: 'Obtener un banco por id',
        tags: ['Banks'],
        responses: [
            new OA\Response(response: 200, description: 'Banco encontrado'),
            new OA\Response(response: 404, description: 'Banco no encontrado'),
        ],
    )]
    public function get(string $id): JsonResponse
    {
        try {
            $bank = ($this->getBankHandler)(new GetBankQuery(Uuid::fromString($id)));
        } catch (BankNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        }

        return new JsonResponse($this->toArray($bank));
    }

    #[Route('/{id}', name: 'bank_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/banks/{id}',
        summary: 'Editar un banco',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'BBVA'),
                    new OA\Property(property: 'internalCode', type: 'string', nullable: true),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                ],
            ),
        ),
        tags: ['Banks'],
        responses: [
            new OA\Response(response: 200, description: 'Banco actualizado'),
            new OA\Response(response: 400, description: 'Datos inválidos'),
            new OA\Response(response: 404, description: 'Banco no encontrado'),
        ],
    )]
    public function update(string $id, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        try {
            $command = new UpdateBankCommand(
                Uuid::fromString($id),
                (string) ($payload['name'] ?? ''),
                $payload['internalCode'] ?? null,
                $payload['notes'] ?? null,
            );
            $bank = ($this->updateBankHandler)($command);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        } catch (BankNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        }

        return new JsonResponse($this->toArray($bank));
    }

    #[Route('/{id}', name: 'bank_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/banks/{id}',
        summary: 'Eliminar un banco',
        tags: ['Banks'],
        responses: [
            new OA\Response(response: 204, description: 'Banco eliminado'),
            new OA\Response(response: 404, description: 'Banco no encontrado'),
        ],
    )]
    public function delete(string $id): JsonResponse
    {
        try {
            ($this->deleteBankHandler)(new DeleteBankCommand(Uuid::fromString($id)));
        } catch (BankNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * @return array{id: string, name: string, internalCode: ?string, notes: ?string}
     */
    private function toArray(Bank $bank): array
    {
        return [
            'id' => $bank->id()->toRfc4122(),
            'name' => $bank->name(),
            'internalCode' => $bank->internalCode(),
            'notes' => $bank->notes(),
        ];
    }
}

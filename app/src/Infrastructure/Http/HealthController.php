<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController
{
    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $databaseOk = true;

        try {
            $this->connection->executeQuery('SELECT 1');
        } catch (\Throwable) {
            $databaseOk = false;
        }

        return new JsonResponse([
            'status' => $databaseOk ? 'ok' : 'error',
            'app' => 'ok',
            'database' => $databaseOk ? 'ok' : 'error',
        ], $databaseOk ? 200 : 503);
    }
}

<?php

namespace App\OpenApi\TaskController;

use OpenApi\Attributes as OA;

class DeleteTaskDoc
{
    #[OA\Delete(
        path: '/api/tasks/{id}',
        description: "Supprime définitivement une tâche",
        summary: "Supprimer une tâche",
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la tâche",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Tâche supprimée",
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: "status", type: "string", example: "success")]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tâche introuvable",
                content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
            ),
            new OA\Response(
                response: 401,
                description: "Non authentifié",
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
        ]
    )]
    public function __invoke(): void {}
}

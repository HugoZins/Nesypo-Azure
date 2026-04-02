<?php

namespace App\OpenApi\TaskController;

use OpenApi\Attributes as OA;

class UpdateTaskDoc
{
    #[OA\Patch(
        path: '/api/tasks/{id}',
        description: "Modifie une ou plusieurs propriétés d'une tâche existante",
        summary: "Modifier une tâche",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Nouveau titre"),
                    new OA\Property(property: "done", type: "boolean", example: true),
                    new OA\Property(property: "priority", type: "string", enum: ["Basse", "Moyenne", "Haute"], example: "Haute"),
                ]
            )
        ),
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
            new OA\Response(response: 200, description: "Tâche mise à jour"),
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

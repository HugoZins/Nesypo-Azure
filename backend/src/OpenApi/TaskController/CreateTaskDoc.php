<?php

namespace App\OpenApi\TaskController;

use OpenApi\Attributes as OA;

class CreateTaskDoc
{
    #[OA\Post(
        path: '/api/tasks',
        description: "Crée une nouvelle tâche associée à une todolist existante",
        summary: "Créer une tâche",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "todoListId"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Faire les courses"),
                    new OA\Property(property: "priority", type: "string", enum: ["Basse", "Moyenne", "Haute"], example: "Moyenne"),
                    new OA\Property(property: "todoListId", type: "integer", example: 1),
                ]
            )
        ),
        tags: ['Tasks'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Tâche créée",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 10),
                        new OA\Property(property: "title", type: "string", example: "Faire les courses"),
                        new OA\Property(property: "done", type: "boolean", example: false),
                        new OA\Property(property: "priority", type: "string", example: "Moyenne"),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides",
                content: new OA\JsonContent(ref: '#/components/schemas/BadRequestResponse')
            ),
            new OA\Response(
                response: 404,
                description: "Todolist introuvable",
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

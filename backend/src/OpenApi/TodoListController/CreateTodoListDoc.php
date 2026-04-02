<?php

namespace App\OpenApi\TodoListController;

use OpenApi\Attributes as OA;

class CreateTodoListDoc
{
    #[OA\Post(
        path: '/api/todo-lists',
        description: "Crée une nouvelle todo list associée à l'utilisateur connecté",
        summary: "Créer une todo list",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Courses de la semaine"),
                ]
            )
        ),
        tags: ['TodoLists'],
        responses: [
            new OA\Response(
                response: 201,
                description: "Todo list créée",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 3),
                        new OA\Property(property: "title", type: "string", example: "Courses de la semaine"),
                        new OA\Property(property: "progress", type: "integer", example: 0),
                        new OA\Property(property: "completedTasks", type: "integer", example: 0),
                        new OA\Property(property: "totalTasks", type: "integer", example: 0),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides",
                content: new OA\JsonContent(ref: '#/components/schemas/BadRequestResponse')
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

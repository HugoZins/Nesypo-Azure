<?php

namespace App\OpenApi\TodoListController;

use OpenApi\Attributes as OA;

class ShowTodoListDoc
{
    #[OA\Get(
        path: '/api/todo-lists/{id}',
        description: "Retourne une todo list spécifique avec sa progression",
        summary: "Récupérer une todo list",
        tags: ['TodoLists'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la todo list",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Todo list trouvée",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "title", type: "string", example: "Courses"),
                        new OA\Property(property: "progress", type: "integer", example: 75),
                        new OA\Property(property: "completedTasks", type: "integer", example: 3),
                        new OA\Property(property: "totalTasks", type: "integer", example: 4),
                        new OA\Property(property: "ownerEmail", type: "string", example: "alice@mail.com", nullable: true),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Todo list introuvable",
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

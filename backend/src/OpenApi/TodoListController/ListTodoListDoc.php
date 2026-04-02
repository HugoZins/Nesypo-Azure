<?php

namespace App\OpenApi\TodoListController;

use OpenApi\Attributes as OA;

class ListTodoListDoc
{
    #[OA\Get(
        path: '/api/todo-lists',
        description: "Retourne les todo lists paginées de l'utilisateur connecté",
        summary: "Liste des todo lists",
        tags: ['TodoLists'],
        parameters: [
            new OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 1, minimum: 1)),
            new OA\Parameter(name: "limit", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 10, maximum: 50, minimum: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste paginée",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "title", type: "string"),
                                new OA\Property(property: "progress", type: "integer"),
                                new OA\Property(property: "completedTasks", type: "integer"),
                                new OA\Property(property: "totalTasks", type: "integer"),
                            ]
                        )),
                        new OA\Property(property: "meta", properties: [
                            new OA\Property(property: "total", type: "integer", example: 42),
                            new OA\Property(property: "page", type: "integer", example: 1),
                            new OA\Property(property: "limit", type: "integer", example: 10),
                            new OA\Property(property: "pages", type: "integer", example: 5),
                        ], type: "object"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié", content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
        ]
    )]
    public function __invoke(): void {}
}

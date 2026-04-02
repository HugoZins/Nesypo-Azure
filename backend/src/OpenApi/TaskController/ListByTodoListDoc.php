<?php

namespace App\OpenApi\TaskController;

use OpenApi\Attributes as OA;

class ListByTodoListDoc
{
    #[OA\Get(
        path: '/api/todo-lists/{id}/tasks',
        description: "Retourne toutes les tâches associées à une todolist, triées par ordre de création",
        summary: "Liste des tâches d'une todolist",
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la todolist",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des tâches",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "title", type: "string", example: "Acheter du lait"),
                            new OA\Property(property: "done", type: "boolean", example: false),
                            new OA\Property(property: "priority", type: "string", enum: ["Basse", "Moyenne", "Haute"], example: "Moyenne"),
                        ]
                    )
                )
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

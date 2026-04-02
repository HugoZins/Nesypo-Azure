<?php

namespace App\OpenApi\TodoListController;

use OpenApi\Attributes as OA;

class UpdateTodoListDoc
{
    #[OA\Put(
        path: '/api/todo-lists/{id}',
        description: "Modifie le titre d'une todo list existante",
        summary: "Mettre à jour une todo list",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Nouveau titre"),
                ]
            )
        ),
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
            new OA\Response(response: 200, description: "Todo list mise à jour"),
            new OA\Response(
                response: 400,
                description: "Données invalides",
                content: new OA\JsonContent(ref: '#/components/schemas/BadRequestResponse')
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

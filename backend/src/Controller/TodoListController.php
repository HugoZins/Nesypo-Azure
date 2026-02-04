<?php

namespace App\Controller;

use App\DTO\TodoListRequest;
use App\Service\TodoListService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: "TodoLists")]
class TodoListController extends AbstractController
{
    public function __construct(private TodoListService $todoListService)
    {
    }

    // Lister toutes les todo lists
    #[Route('/api/todo-lists', methods: ['GET'])]
    #[OA\Get(
        summary: "Liste des todo lists",
        description: "Retourne toutes les todo lists de l'utilisateur connecté",
        security: [["cookieAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des todo lists",
                content: new OA\JsonContent(
                    example: [
                        [
                            "id" => 1,
                            "title" => "Travail"
                        ],
                        [
                            "id" => 2,
                            "title" => "Personnel"
                        ]
                    ]
                )
            )
        ]
    )]
    public function list(): JsonResponse
    {
        $todoLists = $this->todoListService->getAll($this->getUser());
        return $this->json($todoLists, 200, [], ['groups' => ['todo_list:read']]);
    }

    // Créer une todo list
    #[Route('/api/todo-lists', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer une todo list",
        description: "Crée une nouvelle todo list associée à l'utilisateur connecté",
        security: [["cookieAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title"],
                properties: [
                    new OA\Property(
                        property: "title",
                        type: "string",
                        example: "Organisation personnelle"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Todo list créée",
                content: new OA\JsonContent(
                    example: [
                        "id" => 3,
                        "title" => "Organisation personnelle"
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 400, description: "Données invalides")
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $dto = new TodoListRequest();
        $data = json_decode($request->getContent(), true);
        $dto->title = $data['title'] ?? null;

        return $this->json(
            $this->todoListService->create($this->getUser(), $dto),
            201,
            [],
            ['groups' => ['todo_list:read']]
        );
    }

    // Récupérer une todo list par ID
    #[Route('/api/todo-lists/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: "Récupérer une todo list",
        description: "Retourne une todo list spécifique avec ses tâches",
        security: [["cookieAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la todo list",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Todo list trouvée",
                content: new OA\JsonContent(
                    example: [
                        "id" => 1,
                        "title" => "Travail",
                        "tasks" => [
                            [
                                "id" => 10,
                                "title" => "Préparer la réunion",
                                "done" => false,
                                "priority" => "high"
                            ]
                        ]
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Todo list introuvable")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $todoList = $this->todoListService->getOne($this->getUser(), $id);
        return $this->json($todoList, 200, [], ['groups' => ['todo_list:read']]);
    }

    // Mettre à jour une todo list
    #[Route('/api/todo-lists/{id}', methods: ['PUT'])]
    #[OA\Put(
        summary: "Mettre à jour une todo list",
        description: "Modifie le titre d'une todo list existante",
        security: [["cookieAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la todo list",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title"],
                properties: [
                    new OA\Property(
                        property: "title",
                        type: "string",
                        example: "Nouveau nom de la todo list"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Todo list mise à jour",
                content: new OA\JsonContent(
                    example: [
                        "id" => 1,
                        "title" => "Nouveau nom de la todo list"
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Todo list introuvable")
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $dto = new TodoListRequest();
        $data = json_decode($request->getContent(), true);
        $dto->title = $data['title'] ?? null;

        return $this->json(
            $this->todoListService->update($this->getUser(), $id, $dto),
            200, [], ['groups' => ['todo_list:read']]
        );
    }

    // Supprimer une todo list
    #[Route('/api/todo-lists/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer une todo list",
        description: "Supprime définitivement une todo list et toutes ses tâches associées",
        security: [["cookieAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la todo list",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Todo list supprimée",
                content: new OA\JsonContent(
                    example: [
                        "status" => "deleted"
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Todo list introuvable")
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        return $this->json(
            $this->todoListService->delete($this->getUser(), $id)
        );
    }
}

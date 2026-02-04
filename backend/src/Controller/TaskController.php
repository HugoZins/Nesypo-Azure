<?php

namespace App\Controller;

use App\DTO\TaskRequest;
use App\Service\TaskService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: "Tasks")]
class TaskController extends AbstractController
{
    public function __construct(private TaskService $taskService)
    {
    }

    #[Route('/api/todo-lists/{id}/tasks', methods: ['GET'])]
    #[OA\Get(
        summary: "Liste des tâches d'une todolist",
        description: "Retourne toutes les tâches associées à une todolist",
        security: [["cookieAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la todolist",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des tâches",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "title", type: "string"),
                            new OA\Property(property: "done", type: "boolean"),
                            new OA\Property(property: "priority", type: "string")
                        ]
                    )
                )
            ),
            new OA\Response(response: 404, description: "Todolist introuvable")
        ]
    )]
    public function listByTodoList(int $id): JsonResponse
    {
        $tasks = $this->taskService->getByTodoList($this->getUser(), $id);

        return $this->json($tasks, 200, [], ['groups' => ['task:read']]);
    }


    #[Route('/api/tasks', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer une tâche",
        description: "Crée une nouvelle tâche associée à une todolist",
        security: [["cookieAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "todoListId"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Faire les courses"),
                    new OA\Property(property: "priority", type: "string", enum: ["low", "medium", "high"], example: "medium"),
                    new OA\Property(property: "todoListId", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Tâche créée",
                content: new OA\JsonContent(
                    example: [
                        "id" => 10,
                        "title" => "Faire les courses",
                        "done" => false,
                        "priority" => "medium"
                    ]
                )
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $dto = new TaskRequest();
        $data = json_decode($request->getContent(), true);

        $dto->title = $data['title'] ?? null;
        $dto->priority = $data['priority'] ?? null;
        $dto->todoListId = $data['todoListId'] ?? null;

        return $this->json(
            $this->taskService->create($this->getUser(), $dto),
            200,
            [],
            ['groups' => ['task:read']] // serializer group
        );
    }

    #[Route('/api/tasks/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Modifier une tâche",
        description: "Permet de modifier une ou plusieurs propriétés d'une tâche existante",
        security: [["cookieAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la tâche à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "title",
                        type: "string",
                        example: "Mettre à jour la documentation"
                    ),
                    new OA\Property(
                        property: "done",
                        type: "boolean",
                        example: true
                    ),
                    new OA\Property(
                        property: "priority",
                        type: "string",
                        enum: ["low", "medium", "high"],
                        example: "high"
                    )
                ],
                example: [
                    "title" => "Corriger le bug de sérialisation",
                    "done" => true,
                    "priority" => "high"
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Tâche mise à jour"),
            new OA\Response(response: 404, description: "Tâche introuvable")
        ],
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $dto = new TaskRequest();
        $data = json_decode($request->getContent(), true);

        $dto->title = $data['title'] ?? null;
        $dto->done = $data['done'] ?? null;
        $dto->priority = $data['priority'] ?? null;

        return $this->json(
            $this->taskService->update($this->getUser(), $id, $dto),
            200,
            [],
            ['groups' => ['task:read']]
        );
    }


    #[Route('/api/tasks/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Supprimer une tâche",
        description: "Supprime définitivement une tâche",
        security: [["cookieAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Tâche supprimée",
                content: new OA\JsonContent(example: ["status" => "deleted"])
            ),
            new OA\Response(response: 404, description: "Tâche introuvable")
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        return $this->json(
            $this->taskService->delete($this->getUser(), $id)
        );
    }
}

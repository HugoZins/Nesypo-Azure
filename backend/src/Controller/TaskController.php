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

    #[Route('/api/tasks', methods: ['GET'])]
    #[OA\Get(summary: "Liste des tâches", security: [["cookieAuth" => []]])]
    public function list(): JsonResponse
    {
        $tasks = $this->taskService->getAll($this->getUser());
        return $this->json($tasks, 200, [], ['groups' => ['task:read']]);
    }

    #[Route('/api/tasks', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer une tâche",
        security: [["cookieAuth" => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["title", "todoListId"],
                properties: [
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "priority", type: "string", enum: ["low", "medium", "high"]),
                    new OA\Property(property: "todoListId", type: "integer"),
                ]
            )
        )
    )]
    public function create(Request $request): JsonResponse
    {
        $dto = new TaskRequest();
        $data = json_decode($request->getContent(), true);

        $dto->title = $data['title'] ?? null;
        $dto->priority = $data['priority'] ?? null;
        $dto->todoListId = $data['todoListId'] ?? null;

        return $this->json(
            $this->taskService->create($this->getUser(), $dto)
        );
    }

    #[Route('/api/tasks/{id}', methods: ['PUT'])]
    #[OA\Put(summary: "Modifier une tâche", security: [["cookieAuth" => []]])]
    public function update(int $id, Request $request): JsonResponse
    {
        $dto = new TaskRequest();
        $data = json_decode($request->getContent(), true);

        $dto->title = $data['title'] ?? null;
        $dto->priority = $data['priority'] ?? null;

        return $this->json(
            $this->taskService->update($this->getUser(), $id, $dto)
        );
    }

    #[Route('/api/tasks/{id}', methods: ['DELETE'])]
    #[OA\Delete(summary: "Supprimer une tâche", security: [["cookieAuth" => []]])]
    public function delete(int $id): JsonResponse
    {
        return $this->json(
            $this->taskService->delete($this->getUser(), $id)
        );
    }
}

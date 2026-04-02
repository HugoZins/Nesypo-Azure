<?php

namespace App\Controller;

use App\DTO\CreateTaskRequest;
use App\DTO\UpdateTaskRequest;
use App\Entity\User;
use App\OpenApi\Attribute\OARouteSchema;
use App\OpenApi\TaskController\CreateTaskDoc;
use App\OpenApi\TaskController\DeleteTaskDoc;
use App\OpenApi\TaskController\ListByTodoListDoc;
use App\OpenApi\TaskController\UpdateTaskDoc;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TaskController extends AbstractController
{
    public function __construct(private readonly TaskService $taskService) {}

    #[Route('/api/todo-lists/{id}/tasks', methods: ['GET'])]
    #[OARouteSchema(schemaClass: ListByTodoListDoc::class)]
    public function listByTodoList(int $id, #[CurrentUser] User $user): JsonResponse
    {
        return $this->json(
            $this->taskService->getByTodoList($user, $id),
            200,
            [],
            ['groups' => ['task:read']]
        );
    }

    #[Route('/api/tasks', methods: ['POST'])]
    #[OARouteSchema(schemaClass: CreateTaskDoc::class)]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dto = new CreateTaskRequest();
        $dto->title = $data['title'] ?? null;
        $dto->priority = $data['priority'] ?? null;
        $dto->todoListId = $data['todoListId'] ?? null;

        return $this->json(
            $this->taskService->create($user, $dto),
            200,
            [],
            ['groups' => ['task:read']]
        );
    }

    #[Route('/api/tasks/{id}', methods: ['PATCH'])]
    #[OARouteSchema(schemaClass: UpdateTaskDoc::class)]
    public function update(int $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dto = new UpdateTaskRequest();
        $dto->title = $data['title'] ?? null;
        $dto->done = isset($data['done']) ? (bool) $data['done'] : null;
        $dto->priority = $data['priority'] ?? null;

        return $this->json(
            $this->taskService->update($user, $id, $dto),
            200,
            [],
            ['groups' => ['task:read']]
        );
    }

    #[Route('/api/tasks/{id}', methods: ['DELETE'])]
    #[OARouteSchema(schemaClass: DeleteTaskDoc::class)]
    public function delete(int $id, #[CurrentUser] User $user): JsonResponse
    {
        return $this->json($this->taskService->delete($user, $id));
    }
}

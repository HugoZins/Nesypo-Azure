<?php

namespace App\Controller;

use App\DTO\TaskRequest;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TaskController extends AbstractController
{
    public function __construct(private TaskService $taskService) {}

    #[Route('/api/tasks', name: 'tasks', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->taskService->getAll($this->getUser()));
    }

    #[Route('/api/tasks', name: 'tasks_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $dto = new TaskRequest();
        $data = json_decode($request->getContent(), true);

        $dto->title = $data['title'] ?? null;
        $dto->done = $data['done'] ?? null;
        $dto->todoListId = $data['todoListId'] ?? null;

        try {
            $task = $this->taskService->create($this->getUser(), $dto);
            return $this->json($task);
        } catch (BadRequestHttpException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/api/tasks/{id}', name: 'tasks_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $dto = new TaskRequest();
        $data = json_decode($request->getContent(), true);

        $dto->title = $data['title'] ?? null;
        $dto->done = $data['done'] ?? null;
        $dto->todoListId = $data['todoListId'] ?? null;

        try {
            $task = $this->taskService->update($this->getUser(), $id, $dto);
            return $this->json($task);
        } catch (BadRequestHttpException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/api/tasks/{id}', name: 'tasks_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        return $this->json($this->taskService->delete($this->getUser(), $id));
    }
}

<?php

namespace App\Controller;

use App\DTO\TodoListRequest;
use App\Service\TodoListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TodoListController extends AbstractController
{
    public function __construct(private TodoListService $todoListService) {}

    #[Route('/api/todo-lists', name: 'todo_lists', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $data = $this->todoListService->getAll($this->getUser());
        return $this->json($data);
    }

    #[Route('/api/todo-lists/{id}', name: 'todo_lists_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $todoList = $this->todoListService->getOne($this->getUser(), $id);
        return $this->json($todoList);
    }

    #[Route('/api/todo-lists', name: 'todo_lists_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $dto = new TodoListRequest();
        $data = json_decode($request->getContent(), true);
        $dto->title = $data['title'] ?? null;

        try {
            $todoList = $this->todoListService->create($this->getUser(), $dto);
            return $this->json($todoList);
        } catch (BadRequestHttpException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/api/todo-lists/{id}', name: 'todo_lists_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $dto = new TodoListRequest();
        $data = json_decode($request->getContent(), true);
        $dto->title = $data['title'] ?? null;

        try {
            $todoList = $this->todoListService->update($this->getUser(), $id, $dto);
            return $this->json($todoList);
        } catch (BadRequestHttpException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/api/todo-lists/{id}', name: 'todo_lists_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        return $this->json($this->todoListService->delete($this->getUser(), $id));
    }
}

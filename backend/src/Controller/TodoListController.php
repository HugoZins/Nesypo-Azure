<?php

namespace App\Controller;

use App\DTO\TodoListRequest;
use App\Entity\User;
use App\OpenApi\Attribute\OARouteSchema;
use App\OpenApi\TodoListController\CreateTodoListDoc;
use App\OpenApi\TodoListController\DeleteTodoListDoc;
use App\OpenApi\TodoListController\ListTodoListDoc;
use App\OpenApi\TodoListController\ShowTodoListDoc;
use App\OpenApi\TodoListController\UpdateTodoListDoc;
use App\Service\TodoListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TodoListController extends AbstractController
{
    public function __construct(private readonly TodoListService $todoListService) {}

    #[Route('/api/todo-lists', methods: ['GET'])]
    #[OARouteSchema(schemaClass: ListTodoListDoc::class)]
    public function list(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));

        return $this->json($this->todoListService->getAll($user, $page, $limit));
    }

    #[Route('/api/todo-lists', methods: ['POST'])]
    #[OARouteSchema(schemaClass: CreateTodoListDoc::class)]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $dto = new TodoListRequest();
        $data = json_decode($request->getContent(), true);
        $dto->title = $data['title'] ?? null;

        return $this->json($this->todoListService->create($user, $dto), 201);
    }

    #[Route('/api/todo-lists/{id}', methods: ['GET'])]
    #[OARouteSchema(schemaClass: ShowTodoListDoc::class)]
    public function show(int $id, #[CurrentUser] User $user): JsonResponse
    {
        return $this->json($this->todoListService->getOne($user, $id));
    }

    #[Route('/api/todo-lists/{id}', methods: ['PUT'])]
    #[OARouteSchema(schemaClass: UpdateTodoListDoc::class)]
    public function update(int $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $dto = new TodoListRequest();
        $data = json_decode($request->getContent(), true);
        $dto->title = $data['title'] ?? null;

        return $this->json($this->todoListService->update($user, $id, $dto));
    }

    #[Route('/api/todo-lists/{id}', methods: ['DELETE'])]
    #[OARouteSchema(schemaClass: DeleteTodoListDoc::class)]
    public function delete(int $id, #[CurrentUser] User $user): JsonResponse
    {
        return $this->json($this->todoListService->delete($user, $id));
    }
}

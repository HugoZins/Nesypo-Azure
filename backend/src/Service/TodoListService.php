<?php

namespace App\Service;

use App\Entity\TodoList;
use App\Entity\User;
use App\Repository\TodoListRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\DTO\TodoListRequest;
use App\DTO\PaginatedResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\DTO\TodoListResponse;

readonly class TodoListService
{
    public function __construct(
        private EntityManagerInterface     $em,
        private ValidatorInterface         $validator,
        private TodoListRepository         $todoListRepository,
        private AuthorizationService       $authorizationService,
        private TodoListProgressCalculator $progressCalculator
    )
    {
    }

    public function getAll(User $user, int $page = 1, int $limit = 10): PaginatedResponse
    {
        $isAdmin = $this->authorizationService->isAdmin($user);

        if ($isAdmin) {
            ['items' => $items, 'total' => $total] = $this->todoListRepository->findPaginatedAll($page, $limit);
        } else {
            ['items' => $items, 'total' => $total] = $this->todoListRepository->findPaginatedByUser($user, $page, $limit);
        }

        $data = array_map(fn(TodoList $list) => $this->toResponse($list), $items);

        return new PaginatedResponse(
            data: $data,
            total: $total,
            page: $page,
            limit: $limit,
            pages: (int) ceil($total / $limit),
        );
    }

    public function getOne(User $user, int $id): TodoListResponse
    {
        $todoList = $this->todoListRepository->find($id);

        if (!$todoList) {
            throw new NotFoundHttpException('TodoList not found');
        }

        if (!$this->authorizationService->canManageTodoList($user, $todoList)) {
            throw new AccessDeniedHttpException('Not allowed');
        }

        return $this->toResponse($todoList);
    }


    public function create(User $user, TodoListRequest $request): TodoListResponse
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(' | ', $messages));
        }

        $todoList = new TodoList();
        $todoList->setTitle($request->title);
        $todoList->setOwner($user);

        $this->em->persist($todoList);
        $this->em->flush();

        return $this->toResponse($todoList);
    }

    public function update(User $user, int $id, TodoListRequest $request): TodoListResponse
    {
        $todoList = $this->todoListRepository->find($id);

        if (!$todoList) {
            throw new NotFoundHttpException('TodoList not found');
        }

        if (!$this->authorizationService->canManageTodoList($user, $todoList)) {
            throw new AccessDeniedHttpException('Not allowed');
        }

        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(' | ', $messages));
        }

        $todoList->setTitle($request->title);
        $this->em->flush();

        return $this->toResponse($todoList);
    }

    public function delete(User $user, int $id): array
    {
        $todoList = $this->todoListRepository->find($id);

        if (!$todoList) {
            throw new NotFoundHttpException('TodoList not found');
        }

        if (!$this->authorizationService->canManageTodoList($user, $todoList)) {
            throw new AccessDeniedHttpException('Not allowed');
        }

        $this->em->remove($todoList);
        $this->em->flush();

        return ['status' => 'success'];
    }

    private function toResponse(TodoList $todoList): TodoListResponse
    {
        $tasks = $todoList->getTasks();
        $total = count($tasks);
        $completed = 0;

        foreach ($tasks as $task) {
            if ($task->isDone()) {
                $completed++;
            }
        }

        return new TodoListResponse(
            id: $todoList->getId(),
            title: $todoList->getTitle(),
            progress: $this->progressCalculator->calculate($todoList),
            completedTasks: $completed,
            totalTasks: $total,
            ownerEmail: $todoList->getOwner()?->getEmail()
        );
    }
}

<?php

namespace App\Service;

use App\Entity\TodoList;
use App\Entity\User;
use App\Repository\TodoListRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TodoListProgressCalculator;
use App\Service\AuthorizationService;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\DTO\TodoListResponse;

class TodoListService
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

    public function getAll(User $user): array
    {
        $todoLists = $this->authorizationService->isAdmin($user)
            ? $this->todoListRepository->findAll()
            : $this->todoListRepository->findBy(['owner' => $user]);

        return array_map(fn(TodoList $todoList) => $this->toResponse($todoList), $todoLists);
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


    public function create(User $user, TodoListRequest $request): TodoList
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

    public function update(User $user, int $id, TodoListRequest $request): TodoList
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
        return new TodoListResponse(
            id: $todoList->getId(),
            title: $todoList->getTitle(),
            progress: $this->progressCalculator->calculate($todoList),
            ownerEmail: $todoList->getOwner()?->getEmail()
        );
    }
}

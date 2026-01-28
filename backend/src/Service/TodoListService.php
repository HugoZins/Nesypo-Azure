<?php

namespace App\Service;

use App\DTO\TodoListRequest;
use App\Entity\TodoList;
use App\Entity\User;
use App\Repository\TodoListRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TodoListProgressCalculator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        if ($this->authorizationService->isAdmin($user)) {
            return $this->todoListRepository->findAll();
        }

        return $this->todoListRepository->findBy([
            'owner' => $user
        ]);
    }


    public function getOne(User $user, int $id): TodoList
    {
        $todoList = $this->todoListRepository->find($id);

        if (!$todoList) {
            throw new NotFoundHttpException('TodoList not found');
        }

        if (!$this->authorizationService->canManageTodoList($user, $todoList)) {
            throw new AccessDeniedHttpException('Not allowed');
        }

        return $todoList;
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

        return $todoList;
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

        return $todoList;
    }

    public
    function delete(User $user, int $id): array
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
}

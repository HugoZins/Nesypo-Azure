<?php

namespace App\Service;

use App\DTO\TodoListRequest;
use App\Entity\TodoList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TodoListService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface     $validator
    )
    {
    }

    public function getAll(User $user): array
    {
        return $this->em->getRepository(TodoList::class)->findBy(['owner' => $user]);
    }

    public function getOne(User $user, int $id): TodoList
    {
        $todoList = $this->em->getRepository(TodoList::class)->find($id);

        if (!$todoList) {
            throw new NotFoundHttpException("TodoList not found");
        }

        if ($todoList->getOwner()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException("Not allowed");
        }

        return $todoList;
    }

    public function create(User $user, TodoListRequest $request): TodoList
    {
        // validation
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(" | ", $messages));
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
        $todoList = $this->em->getRepository(TodoList::class)->find($id);

        if (!$todoList) {
            throw new NotFoundHttpException("TodoList not found");
        }

        if ($todoList->getOwner()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException("Not allowed");
        }

        // validation
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(" | ", $messages));
        }

        $todoList->setTitle($request->title);
        $this->em->flush();

        return $todoList;
    }

    public function delete(User $user, int $id): array
    {
        $todoList = $this->em->getRepository(TodoList::class)->find($id);

        if (!$todoList) {
            throw new NotFoundHttpException("TodoList not found");
        }

        if ($todoList->getOwner()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException("Not allowed");
        }

        $this->em->remove($todoList);
        $this->em->flush();

        return ['status' => 'success'];
    }
}

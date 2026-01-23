<?php

namespace App\Service;

use App\DTO\TaskRequest;
use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TaskService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator
    ) {}

    public function getAll(User $user): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t')
            ->from(Task::class, 't')
            ->join('t.todoList', 'l')
            ->where('l.owner = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    public function getByTodoList(User $user, int $todoListId): array
    {
        $todoList = $this->em->getRepository(TodoList::class)->find($todoListId);

        if (!$todoList) {
            throw new NotFoundHttpException("TodoList not found");
        }

        if ($todoList->getOwner()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException("Not allowed");
        }

        return $this->em->getRepository(Task::class)
            ->findBy(['todoList' => $todoList]);
    }

    public function create(User $user, TaskRequest $request): Task
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

        $todoList = $this->em->getRepository(TodoList::class)->find($request->todoListId);

        if (!$todoList) {
            throw new NotFoundHttpException("TodoList not found");
        }

        if ($todoList->getOwner()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException("Not allowed");
        }

        $task = new Task();
        $task->setTitle($request->title);
        $task->setDone($request->done);
        $task->setTodoList($todoList);

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

    public function update(User $user, int $id, TaskRequest $request): Task
    {
        $task = $this->em->getRepository(Task::class)->find($id);

        if (!$task) {
            throw new NotFoundHttpException("Task not found");
        }

        $todoList = $task->getTodoList();
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

        $task->setTitle($request->title);
        $task->setDone($request->done);

        $this->em->flush();

        return $task;
    }

    public function delete(User $user, int $id): array
    {
        $task = $this->em->getRepository(Task::class)->find($id);

        if (!$task) {
            throw new NotFoundHttpException("Task not found");
        }

        $todoList = $task->getTodoList();
        if ($todoList->getOwner()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException("Not allowed");
        }

        $this->em->remove($task);
        $this->em->flush();

        return ['status' => 'success'];
    }
}

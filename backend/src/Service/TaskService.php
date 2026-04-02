<?php

namespace App\Service;

use App\DTO\CreateTaskRequest;
use App\DTO\UpdateTaskRequest;
use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use App\Enum\TaskPriority;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class TaskService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface     $validator,
        private AuthorizationService   $authorizationService,
    ) {}

    public function getByTodoList(User $user, int $todoListId): array
    {
        $todoList = $this->em->getRepository(TodoList::class)->find($todoListId);

        if (!$todoList) {
            throw new NotFoundHttpException('TodoList not found');
        }

        if (!$this->authorizationService->canManageTodoList($user, $todoList)) {
            throw new AccessDeniedHttpException('Not allowed');
        }

        return $this->em->getRepository(Task::class)
            ->findBy(['todoList' => $todoList], ['id' => 'ASC']);
    }

    public function create(User $user, CreateTaskRequest $request): Task
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(' | ', $messages));
        }

        $todoList = $this->em->getRepository(TodoList::class)->find($request->todoListId);

        if (!$todoList) {
            throw new NotFoundHttpException('TodoList not found');
        }

        if (!$this->authorizationService->canManageTodoList($user, $todoList)) {
            throw new AccessDeniedHttpException('Not allowed');
        }

        $task = new Task();
        $task->setTitle($request->title);
        $task->setDone(false);
        $task->setPriority(
            $request->priority
                ? TaskPriority::from($request->priority)
                : TaskPriority::MEDIUM
        );
        $task->setTodoList($todoList);

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

    public function update(User $user, int $id, UpdateTaskRequest $request): Task
    {
        $task = $this->em->getRepository(Task::class)->find($id);

        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        if (!$this->authorizationService->canManageTask($user, $task)) {
            throw new AccessDeniedHttpException('Not allowed');
        }

        if ($request->title !== null) {
            $task->setTitle($request->title);
        }

        if ($request->done !== null) {
            $task->setDone($request->done);
        }

        if ($request->priority !== null) {
            $task->setPriority(TaskPriority::from($request->priority));
        }

        $this->em->flush();

        return $task;
    }

    public function delete(User $user, int $id): array
    {
        $task = $this->em->getRepository(Task::class)->find($id);

        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        if (!$this->authorizationService->canManageTask($user, $task)) {
            throw new AccessDeniedHttpException('Not allowed');
        }

        $this->em->remove($task);
        $this->em->flush();

        return ['status' => 'success'];
    }
}

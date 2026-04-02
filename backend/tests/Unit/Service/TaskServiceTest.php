<?php

namespace App\Tests\Unit\Service;

use App\DTO\CreateTaskRequest;
use App\DTO\UpdateTaskRequest;
use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use App\Enum\TaskPriority;
use App\Repository\TaskRepository;
use App\Service\AuthorizationService;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskServiceTest extends TestCase
{
    private EntityManagerInterface&Stub $em;
    private ValidatorInterface&Stub $validator;
    private AuthorizationService&Stub $authorizationService;

    protected function setUp(): void
    {
        $this->em = $this->createStub(EntityManagerInterface::class);
        $this->validator = $this->createStub(ValidatorInterface::class);
        $this->authorizationService = $this->createStub(AuthorizationService::class);
    }

    private function buildService(): TaskService
    {
        return new TaskService(
            $this->em,
            $this->validator,
            $this->authorizationService,
        );
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@mail.com');
        return $user;
    }

    private function createTodoList(User $owner): TodoList
    {
        $todoList = new TodoList();
        $todoList->setTitle('Liste test');
        $todoList->setOwner($owner);
        return $todoList;
    }

    private function createTask(TodoList $todoList, string $title = 'Tâche test'): Task
    {
        $task = new Task();
        $task->setTitle($title);
        $task->setDone(false);
        $task->setPriority(TaskPriority::MEDIUM);
        $task->setTodoList($todoList);
        return $task;
    }

    // --- getByTodoList ---

    public function testGetByTodoListEchouesSiTodoListInexistante(): void
    {
        $this->em
            ->method('getRepository')
            ->willReturnCallback(fn($class) => match ($class) {
                TodoList::class => $this->createConfiguredStub(EntityRepository::class, [
                    'find' => null,
                ]),
                default => $this->createStub(EntityRepository::class),
            });

        $this->expectException(NotFoundHttpException::class);

        $this->buildService()->getByTodoList($this->createUser(), 99999);
    }

    public function testGetByTodoListEchouesSiAccesRefuse(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user);

        $this->em
            ->method('getRepository')
            ->willReturnCallback(fn($class) => match ($class) {
                TodoList::class => $this->createConfiguredStub(EntityRepository::class, [
                    'find' => $todoList,
                ]),
                default => $this->createStub(EntityRepository::class),
            });

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->buildService()->getByTodoList($user, 1);
    }

    // --- create ---

    public function testCreerUneTacheAvecSucces(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user);

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->em
            ->method('getRepository')
            ->willReturnCallback(fn($class) => match ($class) {
                TodoList::class => $this->createConfiguredStub(EntityRepository::class, [
                    'find' => $todoList,
                ]),
                default => $this->createStub(EntityRepository::class),
            });

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnCallback(fn($class) => match ($class) {
            TodoList::class => $this->createConfiguredStub(EntityRepository::class, [
                'find' => $todoList,
            ]),
            default => $this->createStub(EntityRepository::class),
        });
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new TaskService($em, $this->validator, $this->authorizationService);

        $request = new CreateTaskRequest();
        $request->title = 'Nouvelle tâche';
        $request->priority = 'Haute';
        $request->todoListId = 1;

        $task = $service->create($user, $request);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertSame('Nouvelle tâche', $task->getTitle());
        $this->assertFalse($task->isDone());
        $this->assertSame(TaskPriority::HIGH, $task->getPriority());
    }

    public function testCreerUneTacheEchouesSiValidationEchoue(): void
    {
        $violation = $this->createStub(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('This value should not be blank.');

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(BadRequestHttpException::class);

        $request = new CreateTaskRequest();
        $request->title = '';
        $request->todoListId = 1;

        $this->buildService()->create($this->createUser(), $request);
    }

    public function testCreerUneTacheEchouesSiTodoListInexistante(): void
    {
        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->em
            ->method('getRepository')
            ->willReturnCallback(fn($class) => match ($class) {
                TodoList::class => $this->createConfiguredStub(EntityRepository::class, [
                    'find' => null,
                ]),
                default => $this->createStub(EntityRepository::class),
            });

        $this->expectException(NotFoundHttpException::class);

        $request = new CreateTaskRequest();
        $request->title = 'Tâche orpheline';
        $request->todoListId = 99999;

        $this->buildService()->create($this->createUser(), $request);
    }

    public function testCreerUneTacheAvecPrioriteParDefaut(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user);

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(true);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnCallback(fn($class) => match ($class) {
            TodoList::class => $this->createConfiguredStub(EntityRepository::class, [
                'find' => $todoList,
            ]),
            default => $this->createStub(EntityRepository::class),
        });

        $service = new TaskService($em, $this->validator, $this->authorizationService);

        $request = new CreateTaskRequest();
        $request->title = 'Tâche sans priorité';
        $request->todoListId = 1;
        $request->priority = null;

        $task = $service->create($user, $request);

        $this->assertSame(TaskPriority::MEDIUM, $task->getPriority());
    }

    // --- update ---

    public function testMettreAJourUneTacheAvecSucces(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user);
        $task = $this->createTask($todoList);

        $this->em
            ->method('getRepository')
            ->willReturnCallback(fn($class) => match ($class) {
                Task::class => $this->createConfiguredStub(EntityRepository::class, [
                    'find' => $task,
                ]),
                default => $this->createStub(EntityRepository::class),
            });

        $this->authorizationService
            ->method('canManageTask')
            ->willReturn(true);

        $request = new UpdateTaskRequest();
        $request->title = 'Titre modifié';
        $request->done = true;
        $request->priority = 'Haute';

        $result = $this->buildService()->update($user, 1, $request);

        $this->assertSame('Titre modifié', $result->getTitle());
        $this->assertTrue($result->isDone());
        $this->assertSame(TaskPriority::HIGH, $result->getPriority());
    }

    public function testMettreAJourUneTacheInexistanteEchoue(): void
    {
        $this->em
            ->method('getRepository')
            ->willReturnCallback(fn($class) => match ($class) {
                Task::class => $this->createConfiguredStub(EntityRepository::class, [
                    'find' => null,
                ]),
                default => $this->createStub(EntityRepository::class),
            });

        $this->expectException(NotFoundHttpException::class);

        $this->buildService()->update($this->createUser(), 99999, new UpdateTaskRequest());
    }

    public function testMettreAJourUneTacheEchouesSiAccesRefuse(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user);
        $task = $this->createTask($todoList);

        $this->em
            ->method('getRepository')
            ->willReturnCallback(fn($class) => match ($class) {
                Task::class => $this->createConfiguredStub(EntityRepository::class, [
                    'find' => $task,
                ]),
                default => $this->createStub(EntityRepository::class),
            });

        $this->authorizationService
            ->method('canManageTask')
            ->willReturn(false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->buildService()->update($user, 1, new UpdateTaskRequest());
    }

    // --- delete ---

    public function testSupprimerUneTacheAvecSucces(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user);
        $task = $this->createTask($todoList);

        $this->em
            ->method('getRepository')
            ->willReturnCallback(fn($class) => match ($class) {
                Task::class => $this->createConfiguredStub(EntityRepository::class, [
                    'find' => $task,
                ]),
                default => $this->createStub(EntityRepository::class),
            });

        $this->authorizationService
            ->method('canManageTask')
            ->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnCallback(fn($class) => match ($class) {
            Task::class => $this->createConfiguredStub(EntityRepository::class, [
                'find' => $task,
            ]),
            default => $this->createStub(EntityRepository::class),
        });
        $em->expects($this->once())->method('remove')->with($task);
        $em->expects($this->once())->method('flush');

        $service = new TaskService($em, $this->validator, $this->authorizationService);
        $result = $service->delete($user, 1);

        $this->assertSame(['status' => 'success'], $result);
    }

    public function testSupprimerUneTacheInexistanteEchoue(): void
    {
        $this->em
            ->method('getRepository')
            ->willReturnCallback(fn($class) => match ($class) {
                Task::class => $this->createConfiguredStub(EntityRepository::class, [
                    'find' => null,
                ]),
                default => $this->createStub(EntityRepository::class),
            });

        $this->expectException(NotFoundHttpException::class);

        $this->buildService()->delete($this->createUser(), 99999);
    }
}

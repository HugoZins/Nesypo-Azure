<?php

namespace App\Tests\Unit\Service;

use App\DTO\TodoListRequest;
use App\DTO\TodoListResponse;
use App\DTO\PaginatedResponse;
use App\Entity\TodoList;
use App\Entity\User;
use App\Repository\TodoListRepository;
use App\Service\AuthorizationService;
use App\Service\TodoListProgressCalculator;
use App\Service\TodoListService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TodoListServiceTest extends TestCase
{
    private EntityManagerInterface&Stub $em;
    private ValidatorInterface&Stub $validator;
    private TodoListRepository&Stub $todoListRepository;
    private AuthorizationService&Stub $authorizationService;
    private TodoListProgressCalculator $progressCalculator;

    protected function setUp(): void
    {
        $this->em = $this->createStub(EntityManagerInterface::class);
        $this->validator = $this->createStub(ValidatorInterface::class);
        $this->todoListRepository = $this->createStub(TodoListRepository::class);
        $this->authorizationService = $this->createStub(AuthorizationService::class);
        $this->progressCalculator = new TodoListProgressCalculator();
    }

    private function buildService(): TodoListService
    {
        return new TodoListService(
            $this->em,
            $this->validator,
            $this->todoListRepository,
            $this->authorizationService,
            $this->progressCalculator,
        );
    }

    private function createUser(string $email = 'test@mail.com'): User
    {
        $user = new User();
        $user->setEmail($email);
        return $user;
    }

    private function createTodoList(User $owner, string $title = 'Liste test', int $id = 1): TodoList
    {
        $todoList = new TodoList();
        $todoList->setTitle($title);
        $todoList->setOwner($owner);

        // Forcer l'ID car Doctrine le génère normalement en base
        $reflection = new \ReflectionProperty(TodoList::class, 'id');
        $reflection->setValue($todoList, $id);

        return $todoList;
    }

    // --- getAll ---

    public function testGetAllRetourneLesListesDeLUtilisateur(): void
    {
        $user = $this->createUser();
        $list1 = $this->createTodoList($user, 'Liste 1');
        $list2 = $this->createTodoList($user, 'Liste 2');

        $this->authorizationService
            ->method('isAdmin')
            ->willReturn(false);

        $this->todoListRepository
            ->method('findPaginatedByUser')
            ->willReturn(['items' => [$list1, $list2], 'total' => 2]);

        $result = $this->buildService()->getAll($user);

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertCount(2, $result->data);
        $this->assertSame(2, $result->total);
        $this->assertSame(1, $result->page);
    }

    public function testGetAllRetourneTouttesLesListesPourUnAdmin(): void
    {
        $admin = $this->createUser('admin@mail.com');
        $user = $this->createUser('user@mail.com');

        $list1 = $this->createTodoList($admin, 'Liste admin');
        $list2 = $this->createTodoList($user, 'Liste user');

        $this->authorizationService
            ->method('isAdmin')
            ->willReturn(true);

        $this->todoListRepository
            ->method('findPaginatedAll')
            ->willReturn(['items' => [$list1, $list2], 'total' => 2]);

        $result = $this->buildService()->getAll($admin);

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertCount(2, $result->data);
    }

    public function testGetAllRetourneUnTableauVideSiAucuneListe(): void
    {
        $user = $this->createUser();

        $this->authorizationService
            ->method('isAdmin')
            ->willReturn(false);

        $this->todoListRepository
            ->method('findPaginatedByUser')
            ->willReturn(['items' => [], 'total' => 0]);

        $result = $this->buildService()->getAll($user);

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertCount(0, $result->data);
        $this->assertSame(0, $result->total);
    }

    // --- getOne ---

    public function testGetOneRetourneLaBonneListe(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user, 'Ma liste');

        $this->todoListRepository
            ->method('find')
            ->willReturn($todoList);

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(true);

        $result = $this->buildService()->getOne($user, 1);

        $this->assertInstanceOf(TodoListResponse::class, $result);
        $this->assertSame('Ma liste', $result->title);
    }

    public function testGetOneEchouesSiListeInexistante(): void
    {
        $this->todoListRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->buildService()->getOne($this->createUser(), 99999);
    }

    public function testGetOneEchouesSiAccesRefuse(): void
    {
        $owner = $this->createUser('owner@mail.com');
        $autreUser = $this->createUser('autre@mail.com');
        $todoList = $this->createTodoList($owner);

        $this->todoListRepository
            ->method('find')
            ->willReturn($todoList);

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->buildService()->getOne($autreUser, 1);
    }

    // --- create ---

    public function testCreerUneListe(): void
    {
        $user = $this->createUser();

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (TodoList $todoList) {
                $reflection = new \ReflectionProperty(TodoList::class, 'id');
                $reflection->setValue($todoList, 1);
            });
        $em->expects($this->once())->method('flush');

        $service = new TodoListService(
            $em,
            $this->validator,
            $this->todoListRepository,
            $this->authorizationService,
            $this->progressCalculator,
        );

        $request = new TodoListRequest();
        $request->title = 'Nouvelle liste';

        $result = $service->create($user, $request);

        $this->assertInstanceOf(TodoListResponse::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame('Nouvelle liste', $result->title);
        $this->assertSame(0, $result->progress);
        $this->assertSame('test@mail.com', $result->ownerEmail);
    }

    public function testCreerUneListeEchouesSiValidationEchoue(): void
    {
        $violation = $this->createStub(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Le titre est requis.');

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(BadRequestHttpException::class);

        $request = new TodoListRequest();
        $request->title = '';

        $this->buildService()->create($this->createUser(), $request);
    }

    // --- update ---

    public function testMettreAJourUneListe(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user, 'Ancien titre');

        $this->todoListRepository
            ->method('find')
            ->willReturn($todoList);

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(true);

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $request = new TodoListRequest();
        $request->title = 'Nouveau titre';

        $result = $this->buildService()->update($user, 1, $request);

        $this->assertInstanceOf(TodoListResponse::class, $result);
        $this->assertSame('Nouveau titre', $result->title);
    }

    public function testMettreAJourUneListeInexistanteEchoue(): void
    {
        $this->todoListRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $request = new TodoListRequest();
        $request->title = 'Titre';

        $this->buildService()->update($this->createUser(), 99999, $request);
    }

    public function testMettreAJourUneListeEchouesSiAccesRefuse(): void
    {
        $owner = $this->createUser('owner@mail.com');
        $autreUser = $this->createUser('autre@mail.com');
        $todoList = $this->createTodoList($owner);

        $this->todoListRepository
            ->method('find')
            ->willReturn($todoList);

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(false);

        $this->expectException(AccessDeniedHttpException::class);

        $request = new TodoListRequest();
        $request->title = 'Titre';

        $this->buildService()->update($autreUser, 1, $request);
    }

    // --- delete ---

    public function testSupprimerUneListe(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user);

        $this->todoListRepository
            ->method('find')
            ->willReturn($todoList);

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('remove')->with($todoList);
        $em->expects($this->once())->method('flush');

        $service = new TodoListService(
            $em,
            $this->validator,
            $this->todoListRepository,
            $this->authorizationService,
            $this->progressCalculator,
        );

        $result = $service->delete($user, 1);

        $this->assertSame(['status' => 'success'], $result);
    }

    public function testSupprimerUneListeInexistanteEchoue(): void
    {
        $this->todoListRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->buildService()->delete($this->createUser(), 99999);
    }

    public function testSupprimerUneListeEchouesSiAccesRefuse(): void
    {
        $owner = $this->createUser('owner@mail.com');
        $autreUser = $this->createUser('autre@mail.com');
        $todoList = $this->createTodoList($owner);

        $this->todoListRepository
            ->method('find')
            ->willReturn($todoList);

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->buildService()->delete($autreUser, 1);
    }

    // --- progression ---

    public function testLaProgressionEstCalculeeCorrectement(): void
    {
        $user = $this->createUser();
        $todoList = $this->createTodoList($user);

        $task1 = new \App\Entity\Task();
        $task1->setTitle('Tâche 1');
        $task1->setDone(true);
        $task1->setPriority(\App\Enum\TaskPriority::MEDIUM);
        $todoList->addTask($task1);

        $task2 = new \App\Entity\Task();
        $task2->setTitle('Tâche 2');
        $task2->setDone(false);
        $task2->setPriority(\App\Enum\TaskPriority::MEDIUM);
        $todoList->addTask($task2);

        $this->todoListRepository
            ->method('find')
            ->willReturn($todoList);

        $this->authorizationService
            ->method('canManageTodoList')
            ->willReturn(true);

        $result = $this->buildService()->getOne($user, 1);

        $this->assertSame(50, $result->progress);
        $this->assertSame(1, $result->completedTasks);
        $this->assertSame(2, $result->totalTasks);
    }
}

<?php

namespace App\Tests\Unit\Service;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use App\Enum\TaskPriority;
use App\Service\AuthorizationService;
use PHPUnit\Framework\TestCase;

class AuthorizationServiceTest extends TestCase
{
    private AuthorizationService $service;

    protected function setUp(): void
    {
        $this->service = new AuthorizationService();
    }

    private function createUser(string $email = 'user@mail.com', array $roles = ['ROLE_USER'], int $id = 1): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);

        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setValue($user, $id);

        return $user;
    }

    private function createTodoList(User $owner): TodoList
    {
        $todoList = new TodoList();
        $todoList->setTitle('Liste test');
        $todoList->setOwner($owner);
        return $todoList;
    }

    private function createTask(TodoList $todoList): Task
    {
        $task = new Task();
        $task->setTitle('Tâche test');
        $task->setDone(false);
        $task->setPriority(TaskPriority::MEDIUM);
        $task->setTodoList($todoList);
        return $task;
    }

    public function testIsAdminRetourneTruePourUnAdmin(): void
    {
        $admin = $this->createUser('admin@mail.com', ['ROLE_ADMIN']);

        $this->assertTrue($this->service->isAdmin($admin));
    }

    public function testIsAdminRetourneFalsePourUnUtilisateurClassique(): void
    {
        $user = $this->createUser();

        $this->assertFalse($this->service->isAdmin($user));
    }

    public function testCanManageTodoListRetourneTruePourLeProprietaire(): void
    {
        $owner = $this->createUser();
        $todoList = $this->createTodoList($owner);

        $this->assertTrue($this->service->canManageTodoList($owner, $todoList));
    }

    public function testCanManageTodoListRetourneTruePourUnAdmin(): void
    {
        $admin = $this->createUser('admin@mail.com', ['ROLE_ADMIN']);
        $owner = $this->createUser('owner@mail.com');
        $todoList = $this->createTodoList($owner);

        $this->assertTrue($this->service->canManageTodoList($admin, $todoList));
    }

    public function testCanManageTodoListRetourneFalsePourUnAutreUtilisateur(): void
    {
        $owner = $this->createUser('owner@mail.com', ['ROLE_USER'], 1);
        $autreUser = $this->createUser('autre@mail.com', ['ROLE_USER'], 2);
        $todoList = $this->createTodoList($owner);

        $this->assertFalse($this->service->canManageTodoList($autreUser, $todoList));
    }

    public function testCanManageTaskRetourneTruePourLeProprietaireDeLaListe(): void
    {
        $owner = $this->createUser();
        $todoList = $this->createTodoList($owner);
        $task = $this->createTask($todoList);

        $this->assertTrue($this->service->canManageTask($owner, $task));
    }

    public function testCanManageTaskRetourneTruePourUnAdmin(): void
    {
        $admin = $this->createUser('admin@mail.com', ['ROLE_ADMIN']);
        $owner = $this->createUser('owner@mail.com');
        $todoList = $this->createTodoList($owner);
        $task = $this->createTask($todoList);

        $this->assertTrue($this->service->canManageTask($admin, $task));
    }

    public function testCanManageTaskRetourneFalsePourUnAutreUtilisateur(): void
    {
        $owner = $this->createUser('owner@mail.com', ['ROLE_USER'], 1);
        $autreUser = $this->createUser('autre@mail.com', ['ROLE_USER'], 2);
        $todoList = $this->createTodoList($owner);
        $task = $this->createTask($todoList);

        $this->assertFalse($this->service->canManageTask($autreUser, $task));
    }
}

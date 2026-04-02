<?php

namespace App\Tests\Unit\Service;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Service\TodoListProgressCalculator;
use PHPUnit\Framework\TestCase;

class TodoListProgressCalculatorTest extends TestCase
{
    private TodoListProgressCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new TodoListProgressCalculator();
    }

    public function testRetourneZeroSiAucuneTache(): void
    {
        $todoList = new TodoList();

        $result = $this->calculator->calculate($todoList);

        $this->assertSame(0, $result);
    }

    public function testRetourneZeroSiAucuneTacheTerminee(): void
    {
        $todoList = new TodoList();
        $todoList->addTask($this->createTask(false));
        $todoList->addTask($this->createTask(false));

        $result = $this->calculator->calculate($todoList);

        $this->assertSame(0, $result);
    }

    public function testRetourneCentSiToutesLestachesTerminees(): void
    {
        $todoList = new TodoList();
        $todoList->addTask($this->createTask(true));
        $todoList->addTask($this->createTask(true));
        $todoList->addTask($this->createTask(true));

        $result = $this->calculator->calculate($todoList);

        $this->assertSame(100, $result);
    }

    public function testCalculeCorrectementLaProgressionPartielle(): void
    {
        $todoList = new TodoList();
        $todoList->addTask($this->createTask(true));
        $todoList->addTask($this->createTask(true));
        $todoList->addTask($this->createTask(false));
        $todoList->addTask($this->createTask(false));

        $result = $this->calculator->calculate($todoList);

        $this->assertSame(50, $result);
    }

    public function testArronditCorrectement(): void
    {
        $todoList = new TodoList();
        $todoList->addTask($this->createTask(true));
        $todoList->addTask($this->createTask(false));
        $todoList->addTask($this->createTask(false));

        $result = $this->calculator->calculate($todoList);

        // 1/3 = 33.33... → arrondi à 33
        $this->assertSame(33, $result);
    }

    private function createTask(bool $done): Task
    {
        $task = new Task();
        $task->setDone($done);
        $task->setTitle('Tâche test');
        return $task;
    }
}

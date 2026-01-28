<?php

namespace App\Service;

use App\Entity\TodoList;

class TodoListProgressCalculator
{
    public function calculate(TodoList $todoList): int
    {
        $tasks = $todoList->getTasks();

        if (count($tasks) === 0) {
            return 0;
        }

        $done = 0;
        foreach ($tasks as $task) {
            if ($task->isDone()) {
                $done++;
            }
        }

        return (int)round(($done / count($tasks)) * 100);
    }
}

<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\TodoList;
use App\Entity\Task;

class AuthorizationService
{
    public function isAdmin(User $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    public function canManageTodoList(User $user, TodoList $todoList): bool
    {
        return $this->isAdmin($user)
            || $todoList->getOwner()->getId() === $user->getId();
    }

    public function canManageTask(User $user, Task $task): bool
    {
        return $this->canManageTodoList($user, $task->getTodoList());
    }
}

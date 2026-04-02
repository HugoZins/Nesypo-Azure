<?php

namespace App\DTO;

class TodoListResponse
{
    public function __construct(
        public int     $id,
        public string  $title,
        public int     $progress,
        public int     $completedTasks,
        public int     $totalTasks,
        public ?string $ownerEmail = null,
    ) {}
}

<?php

namespace App\DTO;

class TodoListResponse
{
    public function __construct(
        public int     $id,
        public string  $title,
        public int     $progress,
        public ?string $ownerEmail = null,
        public array   $tasks = [] // <-- inclure les tâches ici
    )
    {
    }
}

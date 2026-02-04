<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\TaskPriority;

class TaskRequest
{
    #[Assert\NotBlank]
    public ?string $title = null;

    #[Assert\Choice(callback: 'App\Enum\TaskPriority::values')]
    public ?string $priority = null;
    
    #[Assert\NotNull]
    public ?int $todoListId = null;

    public ?bool $done = null;
}

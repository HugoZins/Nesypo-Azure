<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\TaskPriority;

class CreateTaskRequest
{
    #[Assert\NotBlank]
    public ?string $title = null;

    #[Assert\NotNull]
    public ?int $todoListId = null;

    #[Assert\Choice(callback: [TaskPriority::class, 'values'])]
    public ?string $priority = null;
}

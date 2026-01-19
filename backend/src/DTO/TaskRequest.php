<?php

namespace App\DTO;

use App\Enum\TaskPriority;
use Symfony\Component\Validator\Constraints as Assert;

class TaskRequest
{
    #[Assert\NotBlank(message: "Le titre est requis.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le titre doit faire au moins {{ limit }} caractères."
    )]
    public ?string $title = null;

    #[Assert\NotNull(message: "Le statut est requis.")]
    public ?bool $done = null;

    #[Assert\NotNull]
    #[Assert\Choice(choices: TaskPriority::class)]
    public string $priority;

    #[Assert\NotNull(message: "La TodoList est requise.")]
    public ?int $todoListId = null;
}

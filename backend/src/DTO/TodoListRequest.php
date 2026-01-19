<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TodoListRequest
{
    #[Assert\NotBlank(message: "Le titre est requis.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le titre doit faire au moins {{ limit }} caractères."
    )]
    public ?string $title = null;
}

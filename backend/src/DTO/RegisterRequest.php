<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequest
{
    #[Assert\NotBlank(message: "L'email est requis.")]
    #[Assert\Email(message: "L'email doit être valide.")]
    public ?string $email = null;

    #[Assert\NotBlank(message: "Le mot de passe est requis.")]
    #[Assert\Length(
        min: 6,
        minMessage: "Le mot de passe doit faire au moins {{ limit }} caractères."
    )]
    public ?string $password = null;

    #[Assert\NotBlank(message: "La confirmation du mot de passe est requise.")]
    public ?string $passwordConfirm = null;
}

<?php

namespace App\OpenApi\AuthController;

use OpenApi\Attributes as OA;

class RegisterDoc
{
    #[OA\Post(
        path: '/api/register',
        description: "Crée un nouvel utilisateur avec une adresse email et un mot de passe",
        summary: "Inscription utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password", "passwordConfirm"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@mail.com"),
                    new OA\Property(property: "password", type: "string", minLength: 6, example: "Password123"),
                    new OA\Property(property: "passwordConfirm", type: "string", example: "Password123"),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur créé",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "email", type: "string", example: "john.doe@mail.com"),
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Erreur de validation", content: new OA\JsonContent(ref: '#/components/schemas/BadRequestResponse')),
        ]
    )]
    public function __invoke(): void {}
}

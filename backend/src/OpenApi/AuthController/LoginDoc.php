<?php

namespace App\OpenApi\AuthController;

use OpenApi\Attributes as OA;

class LoginDoc
{
    #[OA\Post(
        path: '/api/login',
        description: "Authentifie l'utilisateur et stocke le JWT dans un cookie HTTP-only",
        summary: "Connexion utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string"),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: "Connexion réussie", content: new OA\JsonContent(properties: [new OA\Property(property: "status", type: "string", example: "success")])),
            new OA\Response(response: 401, description: "Identifiants invalides", content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
        ]
    )]
    public function __invoke(): void {}
}

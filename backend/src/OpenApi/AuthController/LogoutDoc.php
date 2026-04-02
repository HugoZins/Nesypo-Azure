<?php

namespace App\OpenApi\AuthController;

use OpenApi\Attributes as OA;

class LogoutDoc
{
    #[OA\Post(
        path: '/api/logout',
        description: "Supprime le cookie d'authentification JWT",
        summary: "Déconnexion",
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: "Déconnecté", content: new OA\JsonContent(properties: [new OA\Property(property: "status", type: "string", example: "logged_out")])),
        ]
    )]
    public function __invoke(): void {}
}

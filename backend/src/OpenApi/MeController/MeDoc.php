<?php

namespace App\OpenApi\MeController;

use OpenApi\Attributes as OA;

class MeDoc
{
    #[OA\Get(
        path: '/api/me',
        description: "Retourne les informations de l'utilisateur actuellement authentifié",
        summary: "Utilisateur connecté",
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur authentifié",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "email", type: "string", example: "john.doe@mail.com"),
                        new OA\Property(
                            property: "roles",
                            type: "array",
                            items: new OA\Items(type: "string"),
                            example: ["ROLE_USER"]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Non authentifié",
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
        ]
    )]
    public function __invoke(): void {}
}

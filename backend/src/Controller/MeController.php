<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: "User")]
class MeController extends AbstractController
{
    #[Route('/api/me', methods: ['GET'])]
    #[OA\Get(
        summary: "Utilisateur connecté",
        description: "Retourne les informations de l'utilisateur actuellement authentifié",
        security: [["cookieAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string")),
                    ],
                    example: [
                        "id" => 1,
                        "email" => "john.doe@mail.com",
                        "roles" => ["ROLE_USER"]
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }
}


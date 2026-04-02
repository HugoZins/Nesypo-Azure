<?php

namespace App\Controller;

use App\Entity\User;
use App\OpenApi\Attribute\OARouteSchema;
use App\OpenApi\MeController\MeDoc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class MeController extends AbstractController
{
    #[Route('/api/me', methods: ['GET'])]
    #[OARouteSchema(schemaClass: MeDoc::class)]
    public function me(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }
}

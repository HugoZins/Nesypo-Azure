<?php

namespace App\Controller;

use App\DTO\RegisterRequest;
use App\Service\AuthService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[OA\Tag(name: "Auth")]
class AuthController extends AbstractController
{
    public function __construct(
        private AuthService $authService
    )
    {
    }

    #[Route('/api/register', methods: ['POST'])]
    #[OA\Post(
        summary: "Inscription utilisateur",
        description: "Crée un nouvel utilisateur avec une adresse email et un mot de passe",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password", "passwordConfirm"],
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "john.doe@mail.com"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        minLength: 8,
                        example: "StrongPassword123"
                    ),
                    new OA\Property(
                        property: "passwordConfirm",
                        type: "string",
                        example: "StrongPassword123"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur créé",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "email", type: "string", example: "john.doe@mail.com")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Erreur de validation")
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dto = new RegisterRequest();
        $dto->email = $data['email'] ?? null;
        $dto->password = $data['password'] ?? null;
        $dto->passwordConfirm = $data['passwordConfirm'] ?? null;

        try {
            $user = $this->authService->register($dto);

            return $this->json([
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ]);
        } catch (BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/login', methods: ['POST'])]
    #[OA\Post(
        summary: "Connexion utilisateur",
        description: "Authentifie l'utilisateur et stocke le JWT dans un cookie HTTP-only",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie (cookie JWT défini)",
                content: new OA\JsonContent(
                    example: ["status" => "success"]
                )
            ),
            new OA\Response(response: 401, description: "Identifiants invalides")
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $jwt = $this->authService->login(
                $data['email'] ?? null,
                $data['password'] ?? null
            );

            $response = new JsonResponse(['status' => 'success']);
            $response->headers->setCookie(
                Cookie::create('token')
                    ->withValue($jwt)
                    ->withHttpOnly(true)
                    ->withSecure(true)
                    ->withSameSite('none')
                    ->withPath('/')
            );

            return $response;
        } catch (AuthenticationException) {
            return $this->json(['message' => 'Invalid credentials'], 401);
        }
    }

    #[Route('/api/logout', methods: ['POST'])]
    #[OA\Post(
        summary: "Déconnexion",
        description: "Supprime le cookie d'authentification JWT",
        responses: [
            new OA\Response(
                response: 200,
                description: "Déconnecté",
                content: new OA\JsonContent(
                    example: ["status" => "logged_out"]
                )
            )
        ]
    )]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse(['status' => 'logged_out']);
        $response->headers->clearCookie('token');

        return $response;
    }
}

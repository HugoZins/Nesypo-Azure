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
    ) {}

    #[Route('/api/register', methods: ['POST'])]
    #[OA\Post(
        summary: "Inscription utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password", "passwordConfirm"],
                properties: [
                    new OA\Property(property: "email", type: "string", example: "test@mail.com"),
                    new OA\Property(property: "password", type: "string", example: "password"),
                    new OA\Property(property: "passwordConfirm", type: "string", example: "password"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Utilisateur créé"),
            new OA\Response(response: 400, description: "Erreur de validation"),
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "password", type: "string"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Connecté"),
            new OA\Response(response: 401, description: "Identifiants invalides"),
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
    #[OA\Post(summary: "Déconnexion")]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse(['status' => 'logged_out']);
        $response->headers->clearCookie('token');

        return $response;
    }
}

<?php

namespace App\Controller;

use App\DTO\RegisterRequest;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthController extends AbstractController
{
    public function __construct(
        private AuthService $authService
    )
    {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
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
                'status' => 'success',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                ],
            ]);
        } catch (BadRequestHttpException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['message' => 'Missing credentials'], 400);
        }

        try {
            $jwt = $this->authService->login($email, $password);

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
}

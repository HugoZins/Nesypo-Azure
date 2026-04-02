<?php

namespace App\Controller;

use App\DTO\RegisterRequest;
use App\OpenApi\Attribute\OARouteSchema;
use App\OpenApi\AuthController\LoginDoc;
use App\OpenApi\AuthController\LogoutDoc;
use App\OpenApi\AuthController\RegisterDoc;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthController extends AbstractController
{
    public function __construct(private readonly AuthService $authService) {}

    #[Route('/api/register', methods: ['POST'])]
    #[OARouteSchema(schemaClass: RegisterDoc::class)]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dto = new RegisterRequest();
        $dto->email = $data['email'] ?? null;
        $dto->password = $data['password'] ?? null;
        $dto->passwordConfirm = $data['passwordConfirm'] ?? null;

        try {
            $user = $this->authService->register($dto);
            return $this->json(['id' => $user->getId(), 'email' => $user->getEmail()]);
        } catch (BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/login', methods: ['POST'])]
    #[OARouteSchema(schemaClass: LoginDoc::class)]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $tokens = $this->authService->login(
                $data['email'] ?? '',
                $data['password'] ?? ''
            );

            $response = new JsonResponse(['status' => 'success']);
            $isSecure = $request->isSecure();

            $response->headers->setCookie(
                Cookie::create('token')
                    ->withValue($tokens['accessToken'])
                    ->withHttpOnly(true)
                    ->withSecure($isSecure)
                    ->withSameSite($isSecure ? 'none' : 'lax')
                    ->withPath('/')
                    ->withExpires(new \DateTimeImmutable('+1 hour'))
            );

            $response->headers->setCookie(
                Cookie::create('refresh_token')
                    ->withValue($tokens['refreshToken'])
                    ->withHttpOnly(true)
                    ->withSecure($isSecure)
                    ->withSameSite($isSecure ? 'none' : 'lax')
                    ->withPath('/api/token/refresh')
                    ->withExpires(new \DateTimeImmutable('+7 days'))
            );

            return $response;
        } catch (AuthenticationException) {
            return $this->json(['message' => 'Invalid credentials'], 401);
        }
    }

    #[Route('/api/logout', methods: ['POST'])]
    #[OARouteSchema(schemaClass: LogoutDoc::class)]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse(['status' => 'logged_out']);
        $response->headers->clearCookie('token', '/');
        $response->headers->clearCookie('refresh_token', '/api/token/refresh');
        return $response;
    }
}

<?php

namespace App\Service;

use App\DTO\RegisterRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthService
{
    public function __construct(
        private readonly EntityManagerInterface        $em,
        private readonly UserPasswordHasherInterface   $passwordHasher,
        private readonly ValidatorInterface            $validator,
        private readonly UserRepository                $userRepository,
        private readonly JWTTokenManagerInterface      $jwtManager,
        private readonly RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private readonly RefreshTokenManagerInterface  $refreshTokenManager,
    ) {}

    public function register(RegisterRequest $dto): User
    {
        if ($dto->password !== $dto->passwordConfirm) {
            throw new BadRequestHttpException('Les mots de passe ne correspondent pas.');
        }

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }
            throw new BadRequestHttpException(implode(' | ', $messages));
        }

        $user = new User();
        $user->setEmail($dto->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));
        $user->setRoles(['ROLE_USER']);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $accessToken = $this->jwtManager->create($user);

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl(
            $user,
            604800 // 7 jours
        );

        $this->refreshTokenManager->save($refreshToken);

        return [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken->getRefreshToken(),
        ];
    }
}

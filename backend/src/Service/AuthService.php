<?php

namespace App\Service;

use App\DTO\RegisterRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthService
{
    public function __construct(
        private EntityManagerInterface      $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface          $validator,
        private UserRepository              $userRepository,
        private JWTTokenManagerInterface    $jwtManager,
    )
    {
    }

    public function register(RegisterRequest $request): User
    {
        $errors = $this->validator->validate($request);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(' | ', $messages));
        }

        $user = new User();
        $user->setEmail($request->email);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $request->password)
        );

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function login(string $email, string $password): string
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (
            !$user ||
            !$this->passwordHasher->isPasswordValid($user, $password)
        ) {
            throw new AuthenticationException('Invalid credentials');
        }

        return $this->jwtManager->create($user);
        dump($jwt);
    }
}

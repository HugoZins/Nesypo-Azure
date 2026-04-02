<?php

namespace App\Tests\Unit\Service;

use App\DTO\RegisterRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

class AuthServiceTest extends TestCase
{
    private EntityManagerInterface&Stub $em;
    private UserPasswordHasherInterface&Stub $passwordHasher;
    private ValidatorInterface&Stub $validator;
    private UserRepository&Stub $userRepository;
    private JWTTokenManagerInterface&Stub $jwtManager;
    private RefreshTokenGeneratorInterface&Stub $refreshTokenGenerator;
    private RefreshTokenManagerInterface&Stub $refreshTokenManager;

    protected function setUp(): void
    {
        $this->em = $this->createStub(EntityManagerInterface::class);
        $this->passwordHasher = $this->createStub(UserPasswordHasherInterface::class);
        $this->validator = $this->createStub(ValidatorInterface::class);
        $this->userRepository = $this->createStub(UserRepository::class);
        $this->jwtManager = $this->createStub(JWTTokenManagerInterface::class);
        $this->refreshTokenGenerator = $this->createStub(RefreshTokenGeneratorInterface::class);
        $this->refreshTokenManager = $this->createStub(RefreshTokenManagerInterface::class);
    }

    private function buildService(): AuthService
    {
        return new AuthService(
            $this->em,
            $this->passwordHasher,
            $this->validator,
            $this->userRepository,
            $this->jwtManager,
            $this->refreshTokenGenerator,
            $this->refreshTokenManager,
        );
    }

    public function testRegisterCreeLUtilisateurAvecSucces(): void
    {
        $request = new RegisterRequest();
        $request->email = 'test@mail.com';
        $request->password = 'Password123';
        $request->passwordConfirm = 'Password123';

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->passwordHasher
            ->method('hashPassword')
            ->willReturn('hashed_password');

        // Ici on veut vérifier que persist ET flush sont appelés
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new AuthService(
            $em,
            $this->passwordHasher,
            $this->validator,
            $this->userRepository,
            $this->jwtManager,
            $this->refreshTokenGenerator,
            $this->refreshTokenManager,
        );

        $user = $service->register($request);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('test@mail.com', $user->getEmail());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testRegisterEchouesSiMotsDePasseDifferents(): void
    {
        $request = new RegisterRequest();
        $request->email = 'test@mail.com';
        $request->password = 'Password123';
        $request->passwordConfirm = 'AutrePassword';

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Les mots de passe ne correspondent pas.');

        $this->buildService()->register($request);
    }

    public function testRegisterEchouesSiValidationEchoue(): void
    {
        $request = new RegisterRequest();
        $request->email = 'email_invalide';
        $request->password = 'Password123';
        $request->passwordConfirm = 'Password123';

        $violation = $this->createStub(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn("L'email doit être valide.");

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(BadRequestHttpException::class);

        $this->buildService()->register($request);
    }

    public function testLoginRetourneUnJwtAvecSucces(): void
    {
        $user = new User();
        $user->setEmail('test@mail.com');
        $user->setPassword('hashed_password');

        $this->userRepository
            ->method('findOneBy')
            ->willReturn($user);

        $this->passwordHasher
            ->method('isPasswordValid')
            ->willReturn(true);

        $this->jwtManager
            ->method('create')
            ->willReturn('jwt_token_fake');

        $mockRefreshToken = $this->createStub(\Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface::class);
        $mockRefreshToken->method('getRefreshToken')->willReturn('refresh_token_fake');

        $this->refreshTokenGenerator
            ->method('createForUserWithTtl')
            ->willReturn($mockRefreshToken);

        $tokens = $this->buildService()->login('test@mail.com', 'Password123');

        $this->assertIsArray($tokens);
        $this->assertSame('jwt_token_fake', $tokens['accessToken']);
        $this->assertSame('refresh_token_fake', $tokens['refreshToken']);
    }

    public function testLoginEchouesSiUtilisateurInexistant(): void
    {
        $this->userRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);

        $this->buildService()->login('inconnu@mail.com', 'Password123');
    }

    public function testLoginEchouesSiMotDePasseIncorrect(): void
    {
        $user = new User();
        $user->setEmail('test@mail.com');
        $user->setPassword('hashed_password');

        $this->userRepository
            ->method('findOneBy')
            ->willReturn($user);

        $this->passwordHasher
            ->method('isPasswordValid')
            ->willReturn(false);

        $this->expectException(AuthenticationException::class);

        $this->buildService()->login('test@mail.com', 'mauvais_mot_de_passe');
    }
}

<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
    public function testRegisterAvecSucces(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'nouveau@mail.com',
                'password' => 'Password123',
                'passwordConfirm' => 'Password123',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertSame('nouveau@mail.com', $data['email']);
    }

    public function testRegisterEchoueAvecEmailInvalide(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'pas_un_email',
                'password' => 'Password123',
                'passwordConfirm' => 'Password123',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function testRegisterEchoueAvecMotsDePasseDifferents(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@mail.com',
                'password' => 'Password123',
                'passwordConfirm' => 'AutrePassword',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testLoginAvecSucces(): void
    {
        $client = static::createClient();

        // D'abord on crée un utilisateur
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'login@mail.com',
                'password' => 'Password123',
                'passwordConfirm' => 'Password123',
            ])
        );

        // Puis on se connecte
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'login@mail.com',
                'password' => 'Password123',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('success', $data['status']);

        // Vérifier que le cookie JWT est bien posé
        $cookies = $client->getResponse()->headers->getCookies();
        $tokenCookie = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'token') {
                $tokenCookie = $cookie;
            }
        }
        $this->assertNotNull($tokenCookie, 'Le cookie JWT doit être présent');
        $this->assertTrue($tokenCookie->isHttpOnly(), 'Le cookie doit être HTTP-only');
    }

    public function testLoginEchoueAvecMauvaisMotDePasse(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'mauvais@mail.com',
                'password' => 'Password123',
                'passwordConfirm' => 'Password123',
            ])
        );

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'mauvais@mail.com',
                'password' => 'MauvaisMotDePasse',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLogoutAvecSucces(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/logout',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('logged_out', $data['status']);
    }

    public function testAccesRefuseSansAuthentification(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/me');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}

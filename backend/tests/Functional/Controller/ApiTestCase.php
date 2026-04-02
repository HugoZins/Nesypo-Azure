<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected function createAuthenticatedClient(
        string $email = 'test@mail.com',
        string $password = 'Password123'
    ): KernelBrowser {
        $client = static::createClient();

        // Créer l'utilisateur
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password,
                'passwordConfirm' => $password,
            ])
        );

        // Se connecter pour obtenir le cookie JWT
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        return $client;
    }
}

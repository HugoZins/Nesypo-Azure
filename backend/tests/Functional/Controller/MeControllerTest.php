<?php

namespace App\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

class MeControllerTest extends ApiTestCase
{
    public function testMeRetourneLesInfosDeLUtilisateurConnecte(): void
    {
        $client = $this->createAuthenticatedClient('me@mail.com');

        $client->request(
            'GET',
            '/api/me',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertSame('me@mail.com', $data['email']);
        $this->assertContains('ROLE_USER', $data['roles']);
    }

    public function testMeRetourne401SansAuthentification(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/me');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testMeNExposePasLeMotDePasse(): void
    {
        $client = $this->createAuthenticatedClient('secure@mail.com');

        $client->request(
            'GET',
            '/api/me',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayNotHasKey('password', $data);
    }
}

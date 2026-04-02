<?php

namespace App\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

class TodoListControllerTest extends ApiTestCase
{
    public function testListerLesTodoListsNecessiteAuthentification(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/todo-lists');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testListerLesTodoListsAvecAuthentification(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            '/api/todo-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertArrayHasKey('pages', $data);
        $this->assertIsArray($data['data']);
    }

    public function testCreerUneTodoListAvecSucces(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/todo-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Ma liste de test'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Ma liste de test', $data['title']);
        $this->assertSame(0, $data['progress']);
    }

    public function testCreerUneTodoListEchoueAvecTitreVide(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/todo-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => ''])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreerUneTodoListEchoueAvecTitreTropCourt(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/todo-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'AB'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testRecupererUneTodoListExistante(): void
    {
        $client = $this->createAuthenticatedClient();

        // Créer une liste
        $client->request(
            'POST',
            '/api/todo-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Liste à récupérer'])
        );

        $created = json_decode($client->getResponse()->getContent(), true);

        // La récupérer
        $client->request(
            'GET',
            '/api/todo-lists/' . $created['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($created['id'], $data['id']);
        $this->assertSame('Liste à récupérer', $data['title']);
    }

    public function testRecupererUneTodoListInexistanteRetourne404(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            '/api/todo-lists/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testMettreAJourUneTodoList(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/todo-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Titre original'])
        );

        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request(
            'PUT',
            '/api/todo-lists/' . $created['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Titre modifié'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Titre modifié', $data['title']);
    }

    public function testSupprimerUneTodoList(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/todo-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Liste à supprimer'])
        );

        $created = json_decode($client->getResponse()->getContent(), true);

        $client->request(
            'DELETE',
            '/api/todo-lists/' . $created['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Vérifier qu'elle n'existe plus
        $client->request(
            'GET',
            '/api/todo-lists/' . $created['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUnUtilisateurNePeutPasAccederLaListeDunAutre(): void
    {
        // Utilisateur A crée une liste
        $client = $this->createAuthenticatedClient('userA@mail.com');

        $client->request(
            'POST',
            '/api/todo-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Liste privée de A'])
        );

        $created = json_decode($client->getResponse()->getContent(), true);

        // Enregistrer et connecter l'utilisateur B avec le même client
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'userB@mail.com',
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
                'email' => 'userB@mail.com',
                'password' => 'Password123',
            ])
        );

        // Utilisateur B tente d'accéder à la liste de A
        $client->request(
            'GET',
            '/api/todo-lists/' . $created['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testPaginationFonctionne(): void
    {
        $client = $this->createAuthenticatedClient();

        // Créer 3 todolists
        for ($i = 1; $i <= 3; $i++) {
            $client->request(
                'POST',
                '/api/todo-lists',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['title' => "Liste numéro $i"])
            );
        }

        // Récupérer page 1 avec limit 2
        $client->request(
            'GET',
            '/api/todo-lists?page=1&limit=2',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(1, $data['page']);
        $this->assertSame(2, $data['limit']);
        $this->assertSame(3, $data['total']);
        $this->assertSame(2, $data['pages']);
        $this->assertCount(2, $data['data']);

        // Récupérer page 2
        $client->request(
            'GET',
            '/api/todo-lists?page=2&limit=2',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(2, $data['page']);
        $this->assertCount(1, $data['data']);
    }
}

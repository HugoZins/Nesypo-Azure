<?php

namespace App\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends ApiTestCase
{
    private function createTodoList(mixed $client, string $title = 'Liste de test'): array
    {
        $client->request(
            'POST',
            '/api/todo-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => $title])
        );

        return json_decode($client->getResponse()->getContent(), true);
    }

    private function createTask(mixed $client, int $todoListId, string $title = 'Tâche de test', string $priority = 'Moyenne'): array
    {
        $client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => $title,
                'priority' => $priority,
                'todoListId' => $todoListId,
            ])
        );

        return json_decode($client->getResponse()->getContent(), true);
    }

    public function testListerLesTachesNecessiteAuthentification(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/todo-lists/1/tasks');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreerUneTacheAvecSucces(): void
    {
        $client = $this->createAuthenticatedClient();
        $todoList = $this->createTodoList($client);

        $client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Acheter du lait',
                'priority' => 'Haute',
                'todoListId' => $todoList['id'],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Acheter du lait', $data['title']);
        $this->assertFalse($data['done']);
        $this->assertSame('Haute', $data['priority']);
    }

    public function testCreerUneTacheEchoueAvecTitreVide(): void
    {
        $client = $this->createAuthenticatedClient();
        $todoList = $this->createTodoList($client);

        $client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => '',
                'todoListId' => $todoList['id'],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreerUneTacheSurUneTodoListInexistante(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Tâche orpheline',
                'todoListId' => 99999,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testListerLesTachesDUneTodoList(): void
    {
        $client = $this->createAuthenticatedClient();
        $todoList = $this->createTodoList($client);

        $this->createTask($client, $todoList['id'], 'Tâche 1');
        $this->createTask($client, $todoList['id'], 'Tâche 2');
        $this->createTask($client, $todoList['id'], 'Tâche 3');

        $client->request(
            'GET',
            '/api/todo-lists/' . $todoList['id'] . '/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(3, $data);
    }

    public function testLesTaskesSontRetourneesParOrdreDeCreation(): void
    {
        $client = $this->createAuthenticatedClient();
        $todoList = $this->createTodoList($client);

        $this->createTask($client, $todoList['id'], 'Première tâche');
        $this->createTask($client, $todoList['id'], 'Deuxième tâche');
        $this->createTask($client, $todoList['id'], 'Troisième tâche');

        $client->request(
            'GET',
            '/api/todo-lists/' . $todoList['id'] . '/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame('Première tâche', $data[0]['title']);
        $this->assertSame('Deuxième tâche', $data[1]['title']);
        $this->assertSame('Troisième tâche', $data[2]['title']);
    }

    public function testModifierLeStatutDuneTache(): void
    {
        $client = $this->createAuthenticatedClient();
        $todoList = $this->createTodoList($client);
        $task = $this->createTask($client, $todoList['id']);

        $client->request(
            'PATCH',
            '/api/tasks/' . $task['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['done' => true])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['done']);
    }

    public function testModifierLeTitreDuneTache(): void
    {
        $client = $this->createAuthenticatedClient();
        $todoList = $this->createTodoList($client);
        $task = $this->createTask($client, $todoList['id']);

        $client->request(
            'PATCH',
            '/api/tasks/' . $task['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Titre modifié'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Titre modifié', $data['title']);
    }

    public function testModifierLaPrioriteDuneTache(): void
    {
        $client = $this->createAuthenticatedClient();
        $todoList = $this->createTodoList($client);
        $task = $this->createTask($client, $todoList['id'], 'Tâche', 'Basse');

        $client->request(
            'PATCH',
            '/api/tasks/' . $task['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['priority' => 'Haute'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Haute', $data['priority']);
    }

    public function testModifierUneTacheInexistanteRetourne404(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PATCH',
            '/api/tasks/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['done' => true])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testSupprimerUneTache(): void
    {
        $client = $this->createAuthenticatedClient();
        $todoList = $this->createTodoList($client);
        $task = $this->createTask($client, $todoList['id']);

        $client->request(
            'DELETE',
            '/api/tasks/' . $task['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('success', $data['status']);
    }

    public function testSupprimerUneTacheInexistanteRetourne404(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'DELETE',
            '/api/tasks/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUnUtilisateurNePeutPasModifierLaTacheDunAutre(): void
    {
        $client = $this->createAuthenticatedClient('ownerA@mail.com');
        $todoList = $this->createTodoList($client);
        $task = $this->createTask($client, $todoList['id']);

        // Se reconnecter en tant que utilisateur B
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'ownerB@mail.com',
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
                'email' => 'ownerB@mail.com',
                'password' => 'Password123',
            ])
        );

        $client->request(
            'PATCH',
            '/api/tasks/' . $task['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['done' => true])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testLaProgressionDeLaTodoListEstMiseAJourApresModificationDuneTache(): void
    {
        $client = $this->createAuthenticatedClient();
        $todoList = $this->createTodoList($client);

        $task1 = $this->createTask($client, $todoList['id'], 'Tâche 1');
        $this->createTask($client, $todoList['id'], 'Tâche 2');

        // Marquer la première tâche comme faite
        $client->request(
            'PATCH',
            '/api/tasks/' . $task1['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['done' => true])
        );

        // Vérifier la progression de la todolist
        $client->request(
            'GET',
            '/api/todo-lists/' . $todoList['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(50, $data['progress']);
        $this->assertSame(1, $data['completedTasks']);
        $this->assertSame(2, $data['totalTasks']);
    }
}

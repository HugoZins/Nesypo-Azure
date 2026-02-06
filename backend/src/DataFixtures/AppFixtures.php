<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\TodoList;
use App\Entity\Task;
use App\Enum\TaskPriority;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        /*
         * USERS
         */
        $admin = $this->createUser(
            'admin@todo.local',
            ['ROLE_ADMIN'],
            'Admin123!',
            $manager
        );

        $alice = $this->createUser(
            'alice@todo.local',
            ['ROLE_USER'],
            'User123!',
            $manager
        );

        $bob = $this->createUser(
            'bob@todo.local',
            ['ROLE_USER'],
            'User123!',
            $manager
        );

        $users = [$admin, $alice, $bob];

        /*
         * TODOLISTS + TASKS
         */
        foreach ($users as $user) {
            $lists = $this->getTodoListsForUser($user->getEmail());

            foreach ($lists as $listTitle => $tasks) {
                $todoList = new TodoList();
                $todoList->setTitle($listTitle);
                $todoList->setOwner($user);

                foreach ($tasks as $taskData) {
                    $task = new Task();
                    $task->setTitle($taskData['title']);
                    $task->setDone($taskData['done']);
                    $task->setPriority($taskData['priority']);
                    $task->setTodoList($todoList);

                    $manager->persist($task);
                }

                $manager->persist($todoList);
            }
        }

        $manager->flush();
    }

    private function createUser(
        string        $email,
        array         $roles,
        string        $password,
        ObjectManager $manager
    ): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );

        $manager->persist($user);

        return $user;
    }

    private function getTodoListsForUser(string $email): array
    {
        return [
            'Courses & maison' => [
                ['title' => 'Acheter du lait', 'done' => false, 'priority' => TaskPriority::LOW],
                ['title' => 'Commander sacs poubelle', 'done' => true, 'priority' => TaskPriority::LOW],
                ['title' => 'Nettoyer le frigo', 'done' => false, 'priority' => TaskPriority::MEDIUM],
                ['title' => 'Payer facture électricité', 'done' => false, 'priority' => TaskPriority::HIGH],
                ['title' => 'Réparer la porte', 'done' => false, 'priority' => TaskPriority::MEDIUM],
            ],
            'Travail' => [
                ['title' => 'Répondre aux emails', 'done' => true, 'priority' => TaskPriority::LOW],
                ['title' => 'Préparer réunion équipe', 'done' => false, 'priority' => TaskPriority::HIGH],
                ['title' => 'Finaliser le rapport', 'done' => false, 'priority' => TaskPriority::HIGH],
                ['title' => 'Relancer client', 'done' => false, 'priority' => TaskPriority::MEDIUM],
                ['title' => 'Mettre à jour Jira', 'done' => true, 'priority' => TaskPriority::LOW],
            ],
            'Santé' => [
                ['title' => 'Prendre rendez-vous médecin', 'done' => false, 'priority' => TaskPriority::HIGH],
                ['title' => 'Acheter vitamines', 'done' => true, 'priority' => TaskPriority::LOW],
                ['title' => 'Faire du sport', 'done' => false, 'priority' => TaskPriority::MEDIUM],
                ['title' => 'Marcher 30 minutes', 'done' => true, 'priority' => TaskPriority::LOW],
                ['title' => 'Dormir 8h', 'done' => false, 'priority' => TaskPriority::MEDIUM],
            ],
            'Administratif' => [
                ['title' => 'Déclarer les impôts', 'done' => false, 'priority' => TaskPriority::HIGH],
                ['title' => 'Scanner documents', 'done' => true, 'priority' => TaskPriority::MEDIUM],
                ['title' => 'Classer factures', 'done' => false, 'priority' => TaskPriority::MEDIUM],
                ['title' => 'Changer assurance', 'done' => false, 'priority' => TaskPriority::HIGH],
                ['title' => 'Mettre à jour adresse', 'done' => true, 'priority' => TaskPriority::LOW],
            ],
            'Loisirs' => [
                ['title' => 'Regarder un film', 'done' => true, 'priority' => TaskPriority::LOW],
                ['title' => 'Lire un livre', 'done' => false, 'priority' => TaskPriority::LOW],
                ['title' => 'Sortie entre amis', 'done' => false, 'priority' => TaskPriority::MEDIUM],
                ['title' => 'Planifier vacances', 'done' => false, 'priority' => TaskPriority::MEDIUM],
                ['title' => 'Jeu vidéo', 'done' => true, 'priority' => TaskPriority::LOW],
            ],
        ];
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use App\Enum\TaskPriority;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // Utilisateurs fixes pour faciliter la connexion
        $admin = $this->createUser('admin@todo.local', ['ROLE_ADMIN'], 'Admin123!', $manager);
        $alice = $this->createUser('alice@todo.local', ['ROLE_USER'], 'User123!', $manager);
        $bob   = $this->createUser('bob@todo.local',   ['ROLE_USER'], 'User123!', $manager);

        // Utilisateurs supplémentaires générés avec Faker
        $extraUsers = [];
        for ($i = 0; $i < 5; $i++) {
            $firstName = $this->faker->firstName();
            $lastName  = $this->faker->lastName();
            $email     = strtolower($firstName . '.' . $lastName . '@todo.local');
            $extraUsers[] = $this->createUser($email, ['ROLE_USER'], 'User123!', $manager);
        }

        $allUsers = [$admin, $alice, $bob, ...$extraUsers];

        foreach ($allUsers as $user) {
            $nbLists = $this->faker->numberBetween(3, 8);

            for ($l = 0; $l < $nbLists; $l++) {
                $todoList = new TodoList();
                $todoList->setTitle($this->generateTodoListTitle());
                $todoList->setOwner($user);

                $nbTasks = $this->faker->numberBetween(2, 8);
                for ($t = 0; $t < $nbTasks; $t++) {
                    $task = new Task();
                    $task->setTitle($this->generateTaskTitle());
                    $task->setDone($this->faker->boolean(30)); // 30% de chance d'être fait
                    $task->setPriority($this->faker->randomElement(TaskPriority::cases()));
                    $task->setTodoList($todoList);
                    $manager->persist($task);
                }

                $manager->persist($todoList);
            }
        }

        $manager->flush();
    }

    private function createUser(
        string $email,
        array $roles,
        string $password,
        ObjectManager $manager
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $manager->persist($user);
        return $user;
    }

    private function generateTodoListTitle(): string
    {
        $titles = [
            fn() => 'Courses ' . $this->faker->month(),
            fn() => 'Projet ' . $this->faker->word(),
            fn() => 'Objectifs ' . $this->faker->year(),
            fn() => ucfirst($this->faker->word()) . ' à faire',
            fn() => 'Liste ' . $this->faker->word(),
            fn() => 'Organisation ' . $this->faker->month(),
            fn() => ucfirst($this->faker->catchPhrase()),
            fn() => 'Semaine du ' . $this->faker->date('d/m'),
        ];

        return $this->faker->randomElement($titles)();
    }

    private function generateTaskTitle(): string
    {
        $titles = [
            fn() => 'Appeler ' . $this->faker->firstName(),
            fn() => 'Envoyer email à ' . $this->faker->company(),
            fn() => 'Acheter ' . $this->faker->word(),
            fn() => 'Préparer ' . $this->faker->word(),
            fn() => 'Finir ' . $this->faker->word(),
            fn() => 'Lire ' . $this->faker->sentence(3),
            fn() => 'Nettoyer ' . $this->faker->word(),
            fn() => 'Commander ' . $this->faker->word(),
            fn() => 'Répondre à ' . $this->faker->firstName(),
            fn() => 'Planifier ' . $this->faker->word(),
            fn() => 'Vérifier ' . $this->faker->word(),
            fn() => 'Mettre à jour ' . $this->faker->word(),
        ];

        return ucfirst($this->faker->randomElement($titles)());
    }
}

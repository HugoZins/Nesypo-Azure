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
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // 1) Admin
        $admin = new User();
        $admin->setEmail('admin@todo.local');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        $manager->persist($admin);

        // 2) User normal
        $user = new User();
        $user->setEmail('user@todo.local');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'User123!'));
        $manager->persist($user);

        // 3) TodoLists + Tasks
        for ($i = 1; $i <= 3; $i++) {
            $list = new TodoList();
            $list->setTitle("Liste $i");
            $list->setOwner($user);

            // 3 tasks par liste
            for ($j = 1; $j <= 3; $j++) {
                $task = new Task();
                $task->setTitle("Tâche $j de Liste $i");
                $task->setDone($j % 2 === 0);
                $task->setPriority(TaskPriority::MEDIUM);
                $task->setTodoList($list);

                $manager->persist($task);
            }

            $manager->persist($list);
        }

        $manager->flush();
    }
}

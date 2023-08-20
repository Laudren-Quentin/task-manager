<?php

// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Project;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Date;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        // Create a user with manually set data
        $admin = new User();
        $admin->setEmail("admin@admin.com");
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // Create five users with ROLE_USER
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail("user{$i}@user.com");
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user'));
            $user->setRoles(['ROLE_USER']);
            $users[] = $user;
            $manager->persist($user);
        }


        // Create some categories
        $categories = ['Urgent', 'Modéré', 'Neutre'];
        foreach ($categories as $categoryLabel) {
            $category = new Category();
            $category->setLabel($categoryLabel);
            $manager->persist($category);
        }

        // Create five projects with assigned users
        for ($i = 1; $i <= 5; $i++) {
            $project = new Project();
            $project->setTitle("Project {$i}");
            $project->setCreator($admin);
            $project->setCreatedAt(new DateTimeImmutable());
            $project->setUpdatedAt(new DateTimeImmutable());
            foreach ($users as $user) {
                if ($user !== $admin && mt_rand(0, 1) === 1) {
                    $project->addTeam($user);
                }
            }
            $manager->persist($project);

            // Create 5 to 10 tasks per project
            $numTasks = mt_rand(5, 10);
            for ($j = 1; $j <= $numTasks; $j++) {
                $task = new Task();
                $task->setTitle("Task {$j} for Project {$i}");
                $task->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.");
                $task->setCreator($admin);
                $task->setProject($project);
                $task->setCreatedAt(new \DateTimeImmutable());
                $task->setUpdatedAt(new \DateTimeImmutable());
                // Assign a user with ROLE_USER to every other task
                if ($j % 2 === 0) {
                    $randomUser = $users[array_rand($users)];
                    if (in_array('ROLE_USER', $randomUser->getRoles())) {
                        $task->setAssignedUser($randomUser);
                    }
                }
                // Mark 1 task out of 5 as completed, others as not completed
                $task->setCompleted($j % 5 === 0);
                $manager->persist($task);
            }
        }

        $manager->flush();
    }

    // Create some categories

}

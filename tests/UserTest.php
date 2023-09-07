<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    private $entityManager;
    private $userRepository;

    protected function setUp(): void
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);

        // (3) Récupérer le vrai UserRepository
        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    public function testUserExists()
    {
        // Générer une adresse e-mail aléatoire
        $email = 'test' . uniqid() . '@example.com';
        $password = 'password';

        // Créez un utilisateur fictif
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);
        // Autres attributs de l'utilisateur

        // Persistez l'utilisateur dans la base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Appelez la méthode qui vérifie si l'utilisateur existe
        $retrievedUser = $this->userRepository->findOneBy(['email' => $email]);

        // Vérifiez si l'utilisateur a été récupéré avec succès
        $this->assertInstanceOf(User::class, $retrievedUser);
        $this->assertEquals($email, $retrievedUser->getEmail());
    }
    public function testCannotCreateDuplicateUser()
    {
        // Créez un utilisateur avec une adresse e-mail
        $email = 'test' . uniqid() . '@example.com';
        $password = 'password';

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);

        // Persistez l'utilisateur dans la base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Tentez de créer un autre utilisateur avec la même adresse e-mail
        $duplicateUser = new User();
        $duplicateUser->setEmail($email);
        $duplicateUser->setPassword('another_password');

        try {
            // Cela devrait lever une exception
            $this->entityManager->persist($duplicateUser);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            // Vérifiez que l'exception est une violation de contrainte unique
            $this->assertInstanceOf(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class, $e);

            return;
        }

        // Si aucune exception n'est levée, le test échoue
        $this->fail('Expected UniqueConstraintViolationException was not thrown.');
    }
}

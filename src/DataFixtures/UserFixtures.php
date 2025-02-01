<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

/**
 * Fixtures des utilisateurs
 */
class UserFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $hasher){}
    public function load(ObjectManager $manager): void
    {
        // Générateur de données aléatoires
        $faker = Factory::create();

        // Création d'un utilisateur admin
        $admin = new User();
        $email = $faker->email();
        $admin
            ->setEmail($email)
            ->setFirstName($faker->firstName())
            ->setLastName($faker->lastName())
            ->setPassword($this->hasher->hashPassword($admin,"A1$email"))
            ->setRoles(['ROLE_USER','ROLE_ADMIN']);
        $manager->persist($admin);

        // Création de 50 utilisateurs aléatoires
        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $email = $faker->email();
            $user
                ->setEmail($email)
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPassword($this->hasher->hashPassword($user,"A1$email"))
                ->setRoles(['ROLE_USER']);
            $manager->persist($user);
        }
        $manager->flush();
    }
}

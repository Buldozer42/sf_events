<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Type;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

/**
 * Fixtures des événements
 */
class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Générateur de données aléatoires
        $faker = Factory::create();

        // Récupération de tous les types et utilisateurs
        $types = $manager->getRepository(Type::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

        // Création de 25 événements aléatoires
        for ($i = 0; $i < 25; $i++) {
            $event = new Event();
            $maxGuests = $faker->numberBetween(10, 100);
            $owner = $faker->randomElement($users);
            $private = $faker->boolean(60);
            $event
                ->setName($faker->sentence(3))
                ->setDescription($faker->text())
                ->setDate($faker->dateTimeBetween('-1 year', '+1 year'))
                ->setMaxGuests($maxGuests)
                ->setPrice($faker->randomFloat(2, 0, 100))
                ->setLocation($faker->address())
                ->setPrivate($private)
                ->setType($faker->randomElement($types))
                ->setOwner($owner);
            if ($private) {
                $event->setVisible($faker->boolean(80));
            } 
            else {
                $event->setVisible(true);
            }
            $j = 0;
            while ($j < $faker->numberBetween(0, $maxGuests)) {
                $guest = $faker->randomElement($users);
                if ($guest !== $owner) {
                    $event->addGuest($guest);
                    $j++;
                }
            }
            $manager->persist($event);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        // Définir les classes de fixtures qui doivent être chargées avant celle-ci
        return [
            UserFixtures::class,
            TypeFixtures::class,
        ];
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Fixtures des types
 */
class TypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Générateur de données aléatoires
        $faker = Factory::create();

        // Création de 10 types aléatoires
        for ($i = 0; $i < 10; $i++) {
            $type = new Type();
            $type->setName($faker->word());
            $manager->persist($type);
        }
        $manager->flush();
    }
}

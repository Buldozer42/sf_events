<?php

namespace App\Tests\TestFixture;

use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeTestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i=0; $i<=5; $i++) {
            $type = new Type();
            $type->setName("Type $i");
            $manager->persist($type);
        }
        $manager->flush();
    }
}

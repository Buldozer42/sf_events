<?php

namespace App\Tests\TestFixture;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserTestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i=0; $i<=9; $i++){
            $user = new User();
            $user->setEmail("user$i@email.com")
                    ->setPassword("user")
                    ->setFirstName("FN$i")
                    ->setLastName("LN$i")
            ;
            $manager->persist($user);
        }
        $admin = new User();
        $admin->setEmail("admin@email.com")
                ->setPassword("admin")
                ->setFirstName("Admin")
                ->setLastName("Admin")
                ->setRoles(["ROLE_ADMIN", "ROLE_USER"])
        ;
        $manager->persist($admin);
        $manager->flush();
    }
}

<?php

namespace App\Tests\TestFixture;

use DateTime;
use App\Entity\Event;
use App\Entity\User;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EventTestFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $type = $manager->getRepository(Type::class)->findAll()[0];

        $event1 = new Event();
        $event1->setName('Event 1')
            ->setDate(new DateTime('tomorrow'))
            ->setLocation('Location 1')
            ->setDescription('Description 1')
            ->setOwner($users[0])
            ->setMaxGuests(10)
            ->setPrivate(false)
            ->setVisible(true)
            ->setPrice(10)
            ->setType($type)
            ->addGuest($users[1])
            ->addGuest($users[2])
            ->setInvitedEmails([$users[3]->getEmail(), $users[4]->getEmail()])
        ;
        $manager->persist($event1);

        $event10 = new Event();
        $event10->setName('Event 10')
            ->setDate(new DateTime('tomorrow + 1 day'))
            ->setLocation('Location 10')
            ->setDescription('Description 10')
            ->setOwner($users[0])
            ->setMaxGuests(10)
            ->setPrivate(true)
            ->setVisible(true)
            ->setPrice(40)
            ->setType($type)
            ->addGuest($users[1])
            ->addGuest($users[2])
            ->setInvitedEmails([$users[3]->getEmail(), $users[4]->getEmail()])
        ;
        $manager->persist($event10);

        $event2 = new Event();
        $event2->setName('Event 2')
            ->setDate(new DateTime('1999-01-01'))
            ->setLocation('Location 2')
            ->setDescription('Description 2')
            ->setOwner($users[0])
            ->setMaxGuests(10)
            ->setPrivate(false)
            ->setVisible(true)
            ->setPrice(10)
            ->setType($type)
            ->addGuest($users[1])
            ->addGuest($users[2])
            ->setInvitedEmails([$users[3]->getEmail(), $users[4]->getEmail()])
        ;
        $manager->persist($event2);

        $event3 = new Event();
        $event3->setName('Event 3')
            ->setDate(new DateTime('tomorrow'))
            ->setLocation('Location 3')
            ->setDescription('Description 3')
            ->setOwner($users[0])
            ->setMaxGuests(10)
            ->setPrivate(false)
            ->setVisible(false)
            ->setPrice(10)
            ->setType($type)
            ->addGuest($users[1])
            ->addGuest($users[2])
            ->setInvitedEmails([$users[3]->getEmail(), $users[4]->getEmail()])
        ;
        $manager->persist($event3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserTestFixtures::class,
            TypeTestFixtures::class,
        ];
    }
}

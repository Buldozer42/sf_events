<?php

namespace App\Tests\Repository;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use App\Tests\TestFixture\EventTestFixtures;

use App\Tests\BaseTest;

class EventRepositoryTest extends BaseTest
{

    private EventRepository $eventRepository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->eventRepository = $this->entityManager->getRepository(Event::class);
    }

    
    public function loadFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new EventTestFixtures());
        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    public function testfindAllActive(): void
    {
        $events = $this->eventRepository->findAllActive();
        $this->assertCount(2, $events);
    }

    public function testfindAllActiveAndVisible(): void
    {
        $events = $this->eventRepository->findAllActiveAndVisible();
        $this->assertCount(1, $events);
    }

    public function testfindInvitedEvents(): void
    {
        $user3= $this->entityManager->getRepository(User::class)->findAll()[3];
        $events = $this->eventRepository->findInvitedEvents($user3->getEmail());
        $this->assertCount(3, $events);
    }

    // public function testSearch(): void
    // {
    //     $event = $this->eventRepository->findAll()[0];
    //     $parameter = [
    //         'search' => 'Event 1',
    //         'private' => false,
    //         'price' => 100,
    //         'date' => new \DateTime('today'),
    //     ];
    //     $events = $this->eventRepository->search($parameter);
    //     $this->assertCount(1, $events);
    //     $this->assertEquals($event, $events[0]);
    // }

}

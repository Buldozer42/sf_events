<?php

namespace App\Tests\Entity;

use App\Entity\Demand;
use App\Entity\User;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class DemandTest extends TestCase
{
    public function testDemandProperties(): void
    {
        $user = $this->createMock(User::class);
        $event = $this->createMock(Event::class);
        $demand = new Demand();

        $demand->setCurrentState('pending');
        $demand->setContent('Hello World');
        $demand->setUser($user);
        $demand->setEvent($event);

        $this->assertNull($demand->getId());
        $this->assertEquals('pending', $demand->getCurrentState());
        $this->assertEquals('Hello World', $demand->getContent());
        $this->assertEquals($user, $demand->getUser());
        $this->assertEquals($event, $demand->getEvent());
    }

    public function testSetUser(): void
    {
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);
        $demand = new Demand();

        $demand->setUser($user1);
        $this->assertEquals($user1, $demand->getUser());
        
        $demand->setUser($user2);
        $this->assertEquals($user2, $demand->getUser());
    }

    public function testSetEvent(): void
    {
        $event1 = $this->createMock(Event::class);
        $event2 = $this->createMock(Event::class);
        $demand = new Demand();

        $demand->setEvent($event1);
        $this->assertEquals($event1, $demand->getEvent());
        
        $demand->setEvent($event2);
        $this->assertEquals($event2, $demand->getEvent());
    }
}

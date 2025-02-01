<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Demand;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testEventProperties(): void
    {
        $owner = $this->createMock(User::class);
        $event = new Event();

        $event->setName('Event Name');
        $event->setDate(new \DateTime('2025-05-20'));
        $event->setMaxGuests(100);
        $event->setDescription('Event Description');
        $event->setPrice(49.99);
        $event->setLocation('Event Location');
        $event->setOwner($owner);

        $this->assertNull($event->getId());
        $this->assertEquals('Event Name', $event->getName());
        $this->assertEquals(new \DateTime('2025-05-20'), $event->getDate());
        $this->assertEquals(100, $event->getMaxGuests());
        $this->assertEquals('Event Description', $event->getDescription());
        $this->assertEquals(49.99, $event->getPrice());
        $this->assertEquals('Event Location', $event->getLocation());
        $this->assertEquals($owner, $event->getOwner());
    }

    public function testManageGuests(): void
    {
        $event = new Event();
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);

        $event->addGuest($user1);
        $event->addGuest($user2);

        $this->assertCount(2, $event->getGuests());

        $event->removeGuest($user1);
        $this->assertCount(1, $event->getGuests());
    }

    public function testManageComments(): void
    {
        $event = new Event();
        $comment1 = $this->createMock(Comment::class);
        $comment2 = $this->createMock(Comment::class);

        $event->addComment($comment1);
        $event->addComment($comment2);

        $this->assertCount(2, $event->getComments());

        $event->removeComment($comment1);
        $this->assertCount(1, $event->getComments());
    }

    public function testManageDemands(): void
    {
        $event = new Event();
        $demand1 = $this->createMock(Demand::class);
        $demand2 = $this->createMock(Demand::class);

        $event->addDemand($demand1);
        $event->addDemand($demand2);

        $this->assertCount(2, $event->getDemands());

        $event->removeDemand($demand1);
        $this->assertCount(1, $event->getDemands());
    }

    public function testManageInvitedEmails(): void
    {
        $event = new Event();

        $event->addInvitedEmail('test1@example.com');
        $event->addInvitedEmail('test2@example.com');

        $this->assertCount(2, $event->getInvitedEmails());

        $event->removeInvitedEmail('test1@example.com');
        $this->assertCount(1, $event->getInvitedEmails());
    }
}

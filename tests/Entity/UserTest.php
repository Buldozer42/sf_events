<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Demand;
use App\Entity\Comment;
use App\Entity\Notification;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUser(): void
    {  
        $user = new User();
        $user->setEmail("user@example.com");
        $user->setPassword("user");
        $user->setFirstName("FN");
        $user->setLastName("LN");

        $this->assertEquals("user@example.com", $user->getEmail());
        $this->assertEquals("user", $user->getPassword());
        $this->assertEquals("FN", $user->getFirstName());
        $this->assertEquals("LN", $user->getLastName());
        $this->assertContains('ROLE_USER', $user->getRoles());

        $this->assertEquals("FN LN", (string) $user);
    }

    public function testEventManagement(): void
    {
        $user = new User();
        $event = $this->createMock(Event::class);

        $event->expects($this->once())
            ->method('addGuest')
            ->with($this->equalTo($user));

        $user->addAttendingEvent($event);
        $this->assertContains($event, $user->getAttendingEvents());

        $event->expects($this->once())
            ->method('removeGuest')
            ->with($this->equalTo($user));

        $user->removeAttendingEvent($event);
        $this->assertNotContains($event, $user->getAttendingEvents());
    }

    public function testOwnedEventManagement(): void
    {
        $user = new User();
        $event = $this->createMock(Event::class);

        $user->addOwnedEvent($event);
        $this->assertContains($event, $user->getOwnedEvents());

        $user->removeOwnedEvent($event);
        $this->assertNotContains($event, $user->getOwnedEvents());
    }

    public function testDemandManagement(): void
    {
        $user = new User();
        $demand = $this->createMock(Demand::class);

        $user->addDemand($demand);
        $this->assertContains($demand, $user->getDemands());

        $user->removeDemand($demand);
        $this->assertNotContains($demand, $user->getDemands());
    }

    public function testCommentManagement(): void
    {
        $user = new User();
        $comment = $this->createMock(Comment::class);

        $user->addComment($comment);
        $this->assertContains($comment, $user->getComments());

        $user->removeComment($comment);
        $this->assertNotContains($comment, $user->getComments());
    }

    public function testNotificationManagement(): void
    {
        $user = new User();
        $notification = $this->createMock(Notification::class);

        $user->addNotification($notification);
        $this->assertContains($notification, $user->getNotifications());

        $user->removeNotification($notification);
        $this->assertNotContains($notification, $user->getNotifications());
    }
}

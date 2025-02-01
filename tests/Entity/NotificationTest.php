<?php

namespace App\Tests\Entity;

use App\Entity\Notification;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testNotificationProperties(): void
    {
        $user = $this->createMock(User::class);
        $notification = new Notification();

        $notification->setUser($user);
        $notification->setContent('You have a new message.');

        $this->assertNull($notification->getId());
        $this->assertEquals($user, $notification->getUser());
        $this->assertEquals('You have a new message.', $notification->getContent());
    }

    public function testSetUser(): void
    {
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);
        $notification = new Notification();

        $notification->setUser($user1);
        $this->assertEquals($user1, $notification->getUser());

        $notification->setUser($user2);
        $this->assertEquals($user2, $notification->getUser());
    }

    public function testSetContent(): void
    {
        $notification = new Notification();
        $notification->setContent('System update available.');
        
        $this->assertEquals('System update available.', $notification->getContent());

        $notification->setContent('Your request has been approved.');
        $this->assertEquals('Your request has been approved.', $notification->getContent());
    }
}

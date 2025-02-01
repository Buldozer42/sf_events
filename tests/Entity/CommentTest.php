<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function testCommentProperties(): void
    {
        $user = $this->createMock(User::class);
        $event = $this->createMock(Event::class);
        $comment = new Comment($user, $event);
        
        $comment->setContent('Hello World');
        
        $this->assertNull($comment->getId());
        $this->assertEquals('Hello World', $comment->getContent());
        $this->assertInstanceOf(\DateTimeImmutable::class, $comment->getSubmittedAt());
        $this->assertEquals($user, $comment->getUser());
        $this->assertEquals($event, $comment->getEvent());
    }

    public function testSetUser(): void
    {
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);
        $event = $this->createMock(Event::class);
        $comment = new Comment($user1, $event);

        $comment->setUser($user2);
        $this->assertEquals($user2, $comment->getUser());
    }

    public function testSetEvent(): void
    {
        $user = $this->createMock(User::class);
        $event1 = $this->createMock(Event::class);
        $event2 = $this->createMock(Event::class);
        $comment = new Comment($user, $event1);

        $comment->setEvent($event2);
        $this->assertEquals($event2, $comment->getEvent());
    }
}

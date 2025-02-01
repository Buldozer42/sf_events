<?php

namespace App\Tests\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Event;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;

class NotificationServiceTest extends TestCase
{
    private NotificationService $notificationService;
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->notificationService = new NotificationService($this->entityManager);
    }

    public function testCreateNotification()
    {
        $user = $this->createMock(User::class);
        $content = 'Test content';

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Notification::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $notification = $this->notificationService->createNotification($user, $content);

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertSame($content, $notification->getContent());
        $this->assertSame($user, $notification->getUser());
    }

    public function testCreateExpulsionNotification()
    {
        $user = $this->createMock(User::class);
        $event = $this->createMock(Event::class);
        $event->method('getName')->willReturn('Sample Event');

        $this->notificationService = $this->getMockBuilder(NotificationService::class)
            ->onlyMethods(['createNotification'])
            ->setConstructorArgs([$this->entityManager])
            ->getMock();

        $this->notificationService->expects($this->once())
            ->method('createNotification')
            ->with($user, 'You have been expelled from the event Sample Event.');

        $this->notificationService->createExpulsionNotification($user, $event);
    }

    public function testCreateDeleteEventNotification()
    {
        $event = $this->createMock(Event::class);
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);

        $guests = new ArrayCollection([$user1, $user2]);
        $event->method('getGuests')->willReturn($guests);
        $event->method('getName')->willReturn('Deleted Event');

        $this->notificationService = $this->getMockBuilder(NotificationService::class)
            ->onlyMethods(['createNotification'])
            ->setConstructorArgs([$this->entityManager])
            ->getMock();

        $this->notificationService->expects($this->exactly(2))
            ->method('createNotification')
            ->with(
                $this->logicalOr($user1, $user2),
                'The event Deleted Event has been deleted.'
            );

        $this->notificationService->createDeleteEventNotification($event);
    }

    public function testCreateDemandAcceptedNotification()
    {
        $user = $this->createMock(User::class);
        $event = $this->createMock(Event::class);
        $event->method('getName')->willReturn('Sample Event');

        $this->notificationService = $this->getMockBuilder(NotificationService::class)
            ->onlyMethods(['createNotification'])
            ->setConstructorArgs([$this->entityManager])
            ->getMock();

        $this->notificationService->expects($this->once())
            ->method('createNotification')
            ->with($user, 'Your demand to participate in the event Sample Event has been accepted.');

        $this->notificationService->createDemandAcceptedNotification($user, $event);
    }

    public function testCreateDemandRejectedNotification()
    {
        $user = $this->createMock(User::class);
        $event = $this->createMock(Event::class);
        $event->method('getName')->willReturn('Sample Event');

        $this->notificationService = $this->getMockBuilder(NotificationService::class)
            ->onlyMethods(['createNotification'])
            ->setConstructorArgs([$this->entityManager])
            ->getMock();

        $this->notificationService->expects($this->once())
            ->method('createNotification')
            ->with($user, 'Your demand to participate in the event Sample Event has been rejected.');

        $this->notificationService->createDemandRejectedNotification($user, $event);
    }

    // public function testCreateCommentNotification()
    // {
    //     $user = $this->createMock(User::class);
    //     $event = $this->createMock(Event::class);
    //     $user1 = $this->createMock(User::class);
    //     $user2 = $this->createMock(User::class);

    //     $guests = new ArrayCollection([$user1, $user2]);
    //     $event->method('getGuests')->willReturn($guests);
    //     $event->method('getName')->willReturn('Sample Event');

    //     $this->notificationService = $this->getMockBuilder(NotificationService::class)
    //         ->onlyMethods(['createNotification'])
    //         ->setConstructorArgs([$this->entityManager])
    //         ->getMock();

    //     $this->notificationService->expects($this->exactly(2))
    //         ->method('createNotification')
    //         ->with(
    //             $this->logicalOr($user1, $user2),
    //             'A new comment has been posted on the event Sample Event.'
    //         );

    //     $this->notificationService->createCommentNotification($user, $event);
    // }

    public function testCheckNotificationExist()
    {
        $user = $this->createMock(User::class);
        $content = 'Test notification content';
        $notification = $this->createMock(Notification::class);
        $notification->method('getContent')->willReturn($content);

        $user->method('getNotifications')->willReturn(new ArrayCollection([$notification]));

        $result = $this->testPrivateMethod($this->notificationService, 'checkNotificationExist', [$user, $content]);

        $this->assertTrue($result);
    }

    private function testPrivateMethod($class, string $methodName, array $args) : mixed
    {
        $reflection = new ReflectionClass($class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($class, $args);
    }
}

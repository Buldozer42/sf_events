<?php

namespace App\Tests\Controller;

use App\Entity\Notification;
use App\Tests\BaseTest;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Loader;
use App\Tests\TestFixture\UserTestFixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

class NotificationControllerTest extends BaseTest
{
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->entityManager->getRepository(User::class)->findAll()[rand(0,9)];
    }

    public function loadFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new UserTestFixtures());
        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    public function testDeleteNotification(): void
    {
        $this->assertEmpty($this->user->getNotifications());

        $notification = new Notification();
        $notification->setUser($this->user);
        $notification->setContent('Test notification');
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $id = $notification->getId();
        $this->client->request('GET', "/notification/delete/$id");
        $this->assertResponseRedirects('/login');

        $this->loginTestUser($this->user);
        $this->client->request('GET', "/notification/delete/$id");
        $this->assertResponseRedirects('/user');
        $this->assertSelectorNotExists('.alert-danger');
        $this->assertEmpty($this->user->getNotifications());
    }

}
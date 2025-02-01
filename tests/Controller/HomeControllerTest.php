<?php

namespace App\Tests\Controller;

use App\Tests\BaseTest;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Loader;
use App\Tests\TestFixture\UserTestFixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

class HomeControllerTest extends BaseTest
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function loadFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new UserTestFixtures());
        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Welcome !');        
    }

    public function testIndexLoggedUser(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['firstName' => 'FN0']);
        $this->assertNotNull($user);
        $this->loginTestUser($user);
        
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Welcome FN0 !');
    }

}
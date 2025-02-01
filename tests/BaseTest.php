<?php

namespace App\Tests;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use ReflectionClass;

abstract class BaseTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $doctrine = static::getContainer()->get('doctrine');
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $doctrine->getManager();
        $this->truncateDatabase();
        $this->loadFixtures();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->entityManager);
    }

    protected abstract function loadFixtures(): void;

    private function truncateDatabase(): void
    {
        $ormPurger = new ORMPurger($this->entityManager);
        $ormPurger->purge();
    }

    protected function testPrivateMethod($class, string $methodName, array $args) : mixed
    {
        $reflection = new ReflectionClass($class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($class, $args);
    }

    protected function loginTestUser(User $user): void
    {
        $this->client->loginUser($user, 'main');
        $security = $this->client->getContainer()->get('security.token_storage');
        $authenticatedUser = $security->getToken()?->getUser();
        $this->assertNotNull($authenticatedUser);
        $this->assertSame($user->getUserIdentifier(),$authenticatedUser->getUserIdentifier());
    }
}

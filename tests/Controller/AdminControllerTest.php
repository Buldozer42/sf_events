<?php

namespace App\Tests\Controller;

use App\Controller\AdminController;
use App\Tests\BaseTest;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\Type;
use App\Repository\UserRepository;
use App\Tests\TestFixture\EventTestFixtures;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Symfony\Component\DomCrawler\Crawler;
class AdminControllerTest extends BaseTest
{
    private User $user;
    private string $tooLongString;
    private UserRepository $userRepo;
    private AdminController $adminController;
    public function setUp(): void
    {
        parent::setUp();
        $this->userRepo = $this->entityManager->getRepository(User::class);

        $this->user = $this->userRepo->findOneBy(["email" => "admin@email.com"]);

        $this->entityManager->persist($this->user);
        $this->entityManager->flush();

        $this->loginTestUser($this->user);
    }
    public function loadFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new EventTestFixtures());
        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    private function testList(string $type, int $nbRow): Crawler
    {
        $crawler = $this->client->request('GET', "/admin/{$type}");
        $this->assertResponseIsSuccessful();
        file_put_contents('output.html', $crawler->html());
        
        $this->assertSelectorTextContains("h1", ucfirst($type) . " list");
        $this->assertCount($nbRow, $crawler->filter("body > div.container.mt-2 > div > table > tbody")->children('tr'));
        
        return $crawler;
    }

    private function testDelete(string $entityClass, string $routePrefix, object $repository): void
    {
        $count = $repository->count([]);
        $entity = $repository->findAll()[0];

        $this->client->request('GET', "/admin/{$routePrefix}/delete/{$entity->getId()}");
        $this->assertResponseRedirects("/admin/{$routePrefix}s");
        
        $this->assertCount($count - 1, $repository->findAll());
    }

    public function testListUser(): void
    {
        $this->testList('users', $this->userRepo->count([]));
    }

    public function testListEvent(): void
    {
        $this->testList('events', $this->entityManager->getRepository(Event::class)->count([]));
    }

    public function testListType(): void
    {
        $this->testList('types', $this->entityManager->getRepository(Type::class)->count([]));
    }

    public function testDeleteUser(): void
    {
        $this->testDelete(User::class, 'user', $this->userRepo);
    }

    public function testDeleteEvent(): void
    {
        $this->testDelete(Event::class, 'event', $this->entityManager->getRepository(Event::class));
    }

    public function testDeleteType(): void
    {
        $this->testDelete(Type::class, 'type', $this->entityManager->getRepository(Type::class));
    }

    public function testAddType(): void
    {
        $crawler = $this->client->request('GET', '/admin/type/add');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter("form")->form([
            'type[name]' => 'New Type',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/types');

        $type = $this->entityManager->getRepository(Type::class)->findOneBy(['name' => 'New Type']);
        $this->assertNotNull($type);
    }
}


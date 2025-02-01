<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\TestFixture\UserTestFixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Tests\BaseTest;

class SecurityControllerTest extends BaseTest
{
    private User $user;
    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->entityManager->getRepository(User::class)->findAll()[rand(0,9)];
        
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->user->setPassword($hasher->hashPassword($this->user, "user"));
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }
    public function loadFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new UserTestFixtures());
        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    public function testDisplayConnexion(): void
    {
        $crawler = $this->client->request('GET', '/login');
        
        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithBadCredentials(): void
    {
        $crawler = $this->client->request('GET', "/login");
        
        $form = $crawler->selectButton("Sign in")->form([
            '_username' => $this->user->getEmail(),
            '_password' => "badPassword"
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects("http://localhost/login");
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert-danger");
    }

    public function testSuccessfullLoginAndLogout(){
        $crawler = $this->client->request('GET', "/login");
        $form = $crawler->selectButton("Sign in")->form([
            '_username' => $this->user->getEmail(),
            '_password' => "user"
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects("http://localhost/");
        $this->client->followRedirect();

        $crawler = $this->client->request('GET', "/logout");
        $this->assertResponseRedirects("http://localhost/");
    }

    private function formSignUp(Crawler $crawler, string $firstName, string $lastName, string $email, string $password, string $confirmPassword): void
    {
        $form = $crawler->filter("form[name='user']")->form([
            'user[firstName]' => $firstName,
            'user[lastName]' => $lastName,
            'user[email]' => $email,
            'user[password]' => $password,
            'user[confirm_password]' => $confirmPassword
        ]);
        $this->client->submit($form);
    }

    public function testRegister(): void
    {
        $crawler = $this->client->request('GET', "/register");
        $this->assertResponseIsSuccessful();

        $this->formSignUp($crawler, "John", "Doe", "jdoe@email.com", "1Azertyuiopq", "aaa");
        $this->assertSelectorExists(".alert-danger");

        $this->formSignUp($crawler, "John", "Doe", "jdoe@email.com", "aaa", "aaa");
        $this->assertSelectorExists(".alert-danger");

        $this->formSignUp($crawler, "John", "Doe", "user1@email.com", "1Azertyuiopq", "1Azertyuiopq");
        $this->assertSelectorExists(".alert-danger");

        $userRepository = $this->entityManager->getRepository(User::class);
        $this->assertCount(11, $userRepository->findAll());
        $this->formSignUp($crawler, "John", "Doe", "jdoe@email.com", "1Azertyuiopq", "1Azertyuiopq");
        $this->assertResponseRedirects("http://localhost/login");
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert-success");
        $this->assertCount(12, $userRepository->findAll());
    }
}


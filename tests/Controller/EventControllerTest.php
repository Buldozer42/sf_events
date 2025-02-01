<?php

namespace App\Tests\Controller;

use App\Tests\BaseTest;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\Type;
use App\Entity\Demand;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Doctrine\Common\DataFixtures\Loader;
use App\Tests\TestFixture\EventTestFixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Symfony\Component\DomCrawler\Crawler;

class EventControllerTest extends BaseTest
{

    private UserRepository $userRepository;
    private EventRepository $eventRepository;
    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->eventRepository = $this->entityManager->getRepository(Event::class);
    }

    public function loadFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new EventTestFixtures());
        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    private function searchForm(Crawler $crawler, string|null $search, string|null $date, bool|null $isPrivate, string|null $type, float|null $price): void
    {
        if ($date === null) {
            $date = date('d/m/Y H:i', time());
        }

        $form = $crawler->filter("form[name='event_search']")->form();
        if ($search !== null) {
            $form['event_search[search]'] = $search;
        }
        if ($isPrivate !== null) {
            $form['event_search[isPrivate]'] = $isPrivate;
        }
        if ($type !== null) {
            $form['event_search[type]'] = $type;
        }
        if ($price !== null) {
            $form['event_search[price]'] = $price;
        }

        $form['event_search[date]'] = $date;
        $this->client->submit($form);
    }

    public function testList(): void
    {
        $crawler = $this->client->request('GET', '/events');
        $this->assertResponseIsSuccessful();
        $eventCount = count($this->eventRepository->findAllActiveAndVisible());
        $this->assertCount($eventCount*2, $this->client->getCrawler()->filter('.card-title'));

        $this->searchForm($crawler, null, null, null, null, null);
        $this->assertCount($eventCount*2, $this->client->getCrawler()->filter('.card-title'));


        $this->searchForm($crawler, 'Event 1', null, null, null, null);
        $this->assertCount(4, $this->client->getCrawler()->filter('.card-title'));

        $this->searchForm($crawler, null, date('d/m/Y H:i', strtotime('+2 days')), null, null, null);
        $this->assertCount(4, $this->client->getCrawler()->filter('.card-title'));

        $this->searchForm($crawler, null, null, true, null, null);
        $this->assertCount(4, $this->client->getCrawler()->filter('.card-title'));

        $this->searchForm($crawler, null, null, false, null, null);
        $this->assertCount(4, $this->client->getCrawler()->filter('.card-title'));

        $this->searchForm($crawler, null, null, null, null, 20);
        $this->assertCount(4, $this->client->getCrawler()->filter('.card-title'));
    }

    public function testShow(): void
    {
        $user =$this->userRepository->findAll()[0];
        $this->loginTestUser($user);

        $event = $this->eventRepository->findAll()[0];
        $this->client->request('GET', "/event/{$event->getId()}");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.card-title', $event->getName());

        $this->client->request('GET', "/event/0");
        $this->assertResponseStatusCodeSame(404);

        $user = $this->userRepository->findAll()[9];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event/{$event->getId()}");
        $this->assertResponseRedirects('/events');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');        
    }

    public function testShowComment(): void
    {
        $user = $this->userRepository->findAll()[0];
        $this->loginTestUser($user);

        $event = $this->eventRepository->findAll()[0];
        $this->client->request('GET', "/event/{$event->getId()}");
        $form = $this->client->getCrawler()->filter("form[name='comment']")->form([
            'comment[content]' => 'Comment test',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects("/event/{$event->getId()}");
        $this->client->followRedirect();
        $this->assertAnySelectorTextContains('span', 'Comment test');
    }

    public function testShowInvite(): void
    {
        $user = $this->userRepository->findAll()[0];
        $this->loginTestUser($user);

        $event = $this->eventRepository->findAll()[0];
        $this->client->request('GET', "/event/{$event->getId()}");
        $form = $this->client->getCrawler()->filter("form[name='email']")->form([
            'email[email]' => 'test@email.com',
        ]);
        $this->client->submit($form);
        
        $event = $this->eventRepository->findAll()[0];
        $this->assertContains('test@email.com', $event->getInvitedEmails());
    }

    private function formEvent(
        Crawler $crawler, 
        string|null $name,
        string|null $description,
        string|null $date,
        string|null $location,
        int|null $maxGuests,
        bool|null $private,
        bool|null $visible,
        float|null $price,
        int|null $type
    ): void
    {
        $form = $crawler->filter("form[name='event']")->form([
            'event[name]' => $name,
            'event[description]' => $description,
            'event[date]' => $date,
            'event[location]' => $location,
            'event[maxGuests]' => $maxGuests,
            'event[private]' => $private,
            'event[visible]' => $visible,
            'event[price]' => $price,
            'event[type]' => $type,
        ]);
        $this->client->submit($form);
    }

    public function testRegister(): void
    {
        $this->client->request('GET', '/event-register');
        $this->assertResponseRedirects('/login');

        $user = $this->userRepository->findAll()[0];
        $this->loginTestUser($user);
        $this->client->request('GET', '/event-register');
        $this->assertResponseIsSuccessful();

        $typeId = $this->entityManager->getRepository(Type::class)->findAll()[0]->getId();

        $this->formEvent(
            $this->client->getCrawler(),
            'Event test',
            'Description test',
            date('Y-m-d\TH:i', strtotime('+1 days')),
            'Location test',
            10,
            false,
            true,
            20,
            $typeId
        );
        $this->assertResponseRedirects('/events');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $event = $this->eventRepository->findOneBy(['name' => 'Event test']);
        $this->assertNotNull($event);
    }

    public function testEdit(): void
    {
        $event = $this->eventRepository->findAll()[0];
        $this->client->request('GET', "/event-edit/{$event->getId()}");
        $this->assertResponseRedirects('/login');

        $user = $this->userRepository->findAll()[5];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-edit/{$event->getId()}");
        $this->assertResponseRedirects('/events');
        
        $user = $this->userRepository->findAll()[0];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-edit/{$event->getId()}");
        $this->assertResponseIsSuccessful();

        $typeId = $this->entityManager->getRepository(Type::class)->findAll()[0]->getId();

        $this->formEvent(
            $this->client->getCrawler(),
            'Event New',
            'Description test',
            date('Y-m-d\TH:i', strtotime('+1 days')),
            'Location test',
            1,
            false,
            true,
            20,
            $typeId
        );
        $this->assertResponseRedirects("/event-edit/{$event->getId()}");
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');

        $this->formEvent(
            $this->client->getCrawler(),
            'Event New',
            'Description test',
            date('Y-m-d\TH:i', strtotime('+1 days')),
            'Location test',
            10,
            false,
            true,
            20,
            $typeId
        );
        $this->assertResponseRedirects("/events");
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $event = $this->eventRepository->findOneBy(['name' => 'Event New']);
        $this->assertNotNull($event);
    }

    public function testDelete(): void
    {
        $event = $this->eventRepository->findAll()[0];
        $this->client->request('GET', "/event-delete/{$event->getId()}");
        $this->assertResponseRedirects('/login');

        $user = $this->userRepository->findAll()[5];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-delete/{$event->getId()}");
        $this->assertResponseRedirects('/events');

        $user = $this->userRepository->findAll()[0];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-delete/{$event->getId()}");
        $this->assertResponseRedirects('/events');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $event = $this->eventRepository->findOneBy(['id' => $event->getId()]);
        $this->assertNull($event);
    }

    public function testParticipate(): void
    {
        $allEvents = $this->eventRepository->findAll();
        $event = $allEvents[0];
        $this->client->request('GET', "/event-participate/{$event->getId()}");
        $this->assertResponseRedirects('/login');

        $user = $this->userRepository->findAll()[0];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-participate/{$event->getId()}");
        $this->assertResponseRedirects("/events");

        $user = $this->userRepository->findAll()[1];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-participate/{$event->getId()}");
        $this->assertResponseRedirects("/events");

        $user = $this->userRepository->findAll()[3];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-participate/{$event->getId()}");
        $this->assertResponseRedirects("/event/{$event->getId()}");
        $this->client->followRedirect();

        $event = $allEvents[2];
        $this->client->request('GET', "/event-participate/{$event->getId()}");
        $this->assertResponseRedirects("/events");

        $event = $allEvents[1];
        $user = $this->userRepository->findAll()[5];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-participate/{$event->getId()}");
        $this->assertResponseRedirects("/event-participate/request/{$event->getId()}");
    }

    public function testEventMakeRequest(): void
    {
        $allEvents = $this->eventRepository->findAll();
        $event = $allEvents[0];

        $user = $this->userRepository->findAll()[0];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-participate/request/{$event->getId()}");
        $this->assertResponseRedirects("/events");

        $user = $this->userRepository->findAll()[1];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-participate/request/{$event->getId()}");
        $this->assertResponseRedirects("/events");

        $event = $allEvents[1];
        $user = $this->userRepository->findAll()[6];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-participate/request/{$event->getId()}");
        $this->assertResponseIsSuccessful();
        $form = $this->client->getCrawler()->filter("form[name='demand']")->form([
            'demand[content]' => 'hello',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects("/events");
        $event = $this->eventRepository->findOneBy(['id' => $event->getId()]);
        $this->assertCount(1, $event->getDemands());
        $this->assertEquals('hello', $event->getDemands()[0]->getContent());
    }

    public function testInviteAccept(): void
    {
        $event = $this->eventRepository->findAll()[0];
        $this->client->request('GET', "/event-invite/accept/{$event->getId()}");
        $this->assertResponseRedirects('/login');

        $user = $this->userRepository->findAll()[1];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-invite/accept/{$event->getId()}");
        $this->assertResponseRedirects('/events');

        $user = $this->userRepository->findAll()[6];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-invite/accept/{$event->getId()}");
        $this->assertResponseRedirects("/events");

        $user = $this->userRepository->findAll()[3];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-invite/accept/{$event->getId()}");
        $this->assertResponseRedirects("/event/{$event->getId()}");

        $event = $this->eventRepository->findOneBy(['id' => $event->getId()]);
        $this->assertCount(3, $event->getGuests());
        $this->assertNotContains($user->getEmail(), $event->getInvitedEmails());
    }

    public function testInviteCancel(): void
    {
        $event = $this->eventRepository->findAll()[0];
        $this->client->request('GET', "/event-invite/cancel/{$event->getId()}");
        $this->assertResponseRedirects('/login');

        $user = $this->userRepository->findAll()[7];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-invite/cancel/{$event->getId()}");
        $this->assertResponseRedirects('/events');
        $event = $this->eventRepository->findOneBy(['id' => $event->getId()]);
        $this->assertNotContains($user->getEmail(), $event->getInvitedEmails());

        $goodUser = $this->userRepository->findAll()[3];
        $email = $goodUser->getEmail();
        $user = $this->userRepository->findAll()[6];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-invite/cancel/{$event->getId()}/{$email}");
        $this->assertResponseRedirects("/events");
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    private function makeDemand(User $user, Event $event): Demand
    {
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-participate/request/{$event->getId()}");
        $form = $this->client->getCrawler()->filter("form[name='demand']")->form([
            'demand[content]' => 'hello',
        ]);
        $this->client->submit($form);
        $demand = $this->entityManager->getRepository(Demand::class)->findOneBy(['content' => 'hello']);
        if ($demand === null) {
            $this->fail('Demand not found');
        }
        return $demand;
    }

    public function testAcceptDemand(): void
    {
        $event = $this->eventRepository->findAll()[1];
        $demand = $this->makeDemand($this->userRepository->findAll()[6], $event);

        $this->loginTestUser($this->userRepository->findAll()[7]);
        $this->client->request('GET', "/event-demand/accept/{$event->getId()}/{$demand->getId()}");
        $this->assertResponseRedirects('/events');

        $this->loginTestUser($this->userRepository->findAll()[0]);
        $this->client->request('GET', "/event-demand/accept/{$event->getId()}/{$demand->getId()}");
        $this->assertResponseRedirects("/event/{$event->getId()}");

        $event = $this->eventRepository->findOneBy(['id' => $event->getId()]);
        $this->assertContains($this->userRepository->findAll()[6], $event->getGuests());

        $this->client->request('GET', "/event-demand/accept/{$event->getId()}/{$demand->getId()}");
        $this->assertResponseRedirects("/events");
    }

    public function testRejectDemand(): void
    {
        $event = $this->eventRepository->findAll()[1];
        $demand = $this->makeDemand($this->userRepository->findAll()[6], $event);

        $this->loginTestUser($this->userRepository->findAll()[7]);
        $this->client->request('GET', "/event-demand/reject/{$event->getId()}/{$demand->getId()}");
        $this->assertResponseRedirects('/events');

        $this->loginTestUser($this->userRepository->findAll()[0]);
        $this->client->request('GET', "/event-demand/reject/{$event->getId()}/{$demand->getId()}");
        $this->assertResponseRedirects("/event/{$event->getId()}");

        $event = $this->eventRepository->findOneBy(['id' => $event->getId()]);
        $this->assertNotContains($this->userRepository->findAll()[6], $event->getGuests());

        $this->client->request('GET', "/event-demand/reject/{$event->getId()}/{$demand->getId()}");
        $this->assertResponseRedirects("/events");
    }

    public function testRemoveGuest(): void
    {
        $userToRemove = $this->userRepository->findAll()[1];

        $event = $this->eventRepository->findAll()[0];
        $this->client->request('GET', "/event/guest-remove/{$event->getId()}/{$userToRemove->getId()}");
        $this->assertResponseRedirects('/login');

        $user = $this->userRepository->findAll()[3];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event/guest-remove/{$event->getId()}/{$userToRemove->getId()}");
        $this->assertResponseRedirects('/events');

        $user = $this->userRepository->findAll()[0];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event/guest-remove/{$event->getId()}/{$userToRemove->getId()}");
        $this->assertResponseRedirects("/event/{$event->getId()}");
        $this->client->followRedirect();

        $event = $this->eventRepository->findOneBy(['id' => $event->getId()]);
        $this->assertNotContains($userToRemove, $event->getGuests());
    }

    public function testEventLeave(): void
    {
        $event = $this->eventRepository->findAll()[0];
        $this->client->request('GET', "/event-leave/{$event->getId()}");
        $this->assertResponseRedirects('/login');

        $user = $this->userRepository->findAll()[2];
        $this->loginTestUser($user);
        $this->client->request('GET', "/event-leave/{$event->getId()}");
        $this->assertResponseRedirects('/events');

        $event = $this->eventRepository->findAll()[0];
        $this->assertNotContains($user, $event->getGuests());
    }
}
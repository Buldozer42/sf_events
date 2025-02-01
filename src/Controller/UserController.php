<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur dédié aux utilisateurs
 */
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    /**
     * Affiche la page de profil de l'utilisateur
     * 
     * @param EventRepository $eventRepository Le repository des événements
     * @return Response
     */
    #[Route('/user', name: 'app_user')]
    public function index(EventRepository $eventRepository): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Récupère les événements de l'utilisateur
        $events = $user->getEvents();

        // Trie les événements par date
        usort($events, fn($a, $b) => $a->getDate() <=> $b->getDate());

        // Récupère les événements auxquels l'utilisateur a été invité
        $invitedEvents = $eventRepository->findInvitedEvents($user->getEmail());

        // Affiche la page de profil de l'utilisateur
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'sortedEvents' => $events,
            'invitedEvents' => $invitedEvents,
        ]);
    }
}

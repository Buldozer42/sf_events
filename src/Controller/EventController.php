<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Demand;
use App\Entity\User;
use App\Entity\Comment;
use App\Repository\EventRepository;
use App\Form\EventType;
use App\Form\DemandType;
use App\Form\EventSearchType;
use App\Form\CommentType;
use App\Form\EmailType;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Contrôleur des événements
 */
class EventController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private WorkflowInterface $demandResolving,
        private NotificationService $notificationService
    ){}

    /**
     * Affiche la liste des événements
     * 
     * @param Request $request
     * @param EventRepository $eventRepository Le repository des événements
     * @return Response
     */
    #[Route('/events', name: 'app_events')]
    public function list(Request $request, EventRepository $eventRepository): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Crée et traite le formulaire de recherche
        $form = $this->createForm(EventSearchType::class);
        $form->handleRequest($request);
    
        // Si le formulaire est soumis et valide...
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData();

            // Recherche les événements correspondant aux critères
            $events = $eventRepository->search($search);

            // Affiche la liste des événements correspondant aux critères
            return $this->render('event/list.html.twig', [
                'events' => $events,
                'user' => $this->getUser(),
                'form' => $form->createView(),
            ]);
        }
        // Sinon...
        else {
            // Récupère la liste des événements actifs et visibles par tous
            $events = $eventRepository->findAllActiveAndVisible();

            // Si l'utilisateur est connecté...
            if ($user){
                // Fusionne les événements actifs de l'utilisateur avec les événements actifs et visibles par tous
                $events = array_unique(
                    array_merge($events, $user->getActiveEvents())
                    ,SORT_REGULAR
                );

                // Trie les événements par date
                usort($events, fn($a, $b) => $a->getDate() <=> $b->getDate());
            }
        }

        // Affiche la liste des événements
        return $this->render('event/list.html.twig', [
            'events' => $events,
            'user' => $this->getUser(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche un événement donné
     * 
     * @param Request $request
     * @param Event $event L'événement à afficher
     * @param UserRepository $userRepository Le repository des utilisateurs
     * @param HubInterface $hub Le hub Mercure (pour les mises à jour en temps réel)
     * @return Response
     */
    #[Route('/event/{id}', name: 'app_event')]
    public function show(Request $request, Event $event, UserRepository $userRepository, HubInterface $hub): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Vérifie si l'utilisateur a le droit de voir l'événement
        try {   
            $this->denyAccessUnlessGranted(
                'view', 
                [
                    $event,
                    $user
                ],
            );
        }
        // Redirige l'utilisateur vers la liste des événements s'il n'a pas le droit de voir l'événement
        catch (AccessDeniedException) {
            $this->addFlash('danger', 'You cannot view this event');
            return $this->redirectToRoute("app_events");
        }

        // Crée et traite le formulaire de commentaire
        $comment = new Comment($user, $event);
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        // Si le formulaire est soumis...
        if ($commentForm->isSubmitted()) {
            // Si le formulaire est valide...
            if ($commentForm->isValid()) {
                // Fixe la date de soumission du commentaire
                $comment->setSubmittedAt(new \DateTimeImmutable());

                // Crée une notification pour chaque utilisateur participant à l'événement
                $this->notificationService->createCommentNotification($user, $event);

                // Enregistre le commentaire en base de données
                $this->manager->persist($comment);
                $this->manager->flush();

                // Envoie une mise à jour Mercure pour informer les utilisateurs de l'ajout du commentaire
                // Malheureusement, n'est pas fonctionnel
                // $update = new Update(
                //     'event/' . $event->getId()
                // );
                // $hub->publish($update);

                // Ajoute un message flash de succès et redirige vers l'événement
                $this->addFlash('success', 'Comment added');
                return $this->redirectToRoute('app_event', ['id' => $event->getId()]);
            } else {
                // Sinon, ajoute un message flash d'erreur
                $error = $commentForm->getErrors(true);
                foreach ($error as $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
        }

        // Crée et traite le formulaire d'invitation par email
        $emailForm = $this->createForm(EmailType::class);
        $emailForm->handleRequest($request);

        // Si le formulaire est soumis...
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            // Récupère l'email de l'invité et l'ajoute à la liste des invités
            $email = $emailForm->get('email')->getData();
            $event->addInvitedEmail($email);

            // Enregistre la modification en base de données
            $this->manager->persist($event);
            $this->manager->flush();
            
            // Ajoute un message flash de succès
            $link = $this->generateUrl('app_event_invite_accept', ['event' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->addFlash('success', 'Guest invited. You can share this link to him: ' . $link);

            // Crée une notification pour l'utilisateur invité s'il a un compte
            if ($userRepository->findOneByEmail($email)) {
                $this->notificationService->createInvitationNotification($userRepository->findOneByEmail($email), $event);
            }

            // Redirige vers l'événement
            return $this->redirectToRoute('app_event', ['id' => $event->getId()]);
        }

        // Affiche l'événement
        return $this->render('event/show.html.twig', [
            'user' => $user,
            'event' => $event,
            'commentForm' => $commentForm->createView(),
            'emailForm' => $emailForm->createView(),
        ]);
    }

    /**
     * Crée ou édite un événement
     * 
     * @param Request $request
     * @param Event $event L'événement à éditer (null si on crée un événement)
     * @return Response
     */
    #[Route('/event-register', name: 'app_event_register')]
    #[Route(path: '/event-edit/{id}', name: 'app_event_edit')]
    public function register(Request $request, ?Event $event): Response
    {
        // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
        if (!$this->getUser()) {
            $this->addFlash('danger', 'You must be logged in to create an event');
            return $this->redirectToRoute('app_login');
        }

        // Si l'événement n'existe pas...
        if (! $event){
            // Crée un nouvel événement
            $event = new Event();
        } 
        // Sinon...
        else {
            // Vérifie si l'utilisateur a le droit de modifier l'événement
            if ($event->getOwner() !== $this->getUser()) {
                $this->addFlash('danger', 'You cannot edit this event');
                return $this->redirectToRoute("app_events");
            }
        }

        // Crée et traite le formulaire de l'événement
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        // Si le formulaire est soumis...
        if ($form->isSubmitted()) {

            // Si le formulaire est valide...
            if ($form->isValid()) {

                // Vérifie si le nombre de participants est inférieur au nombre maximal de participants
                if ($event->getGuests()->count() > $event->getMaxGuests()) {
                    $this->addFlash('danger', 'You cannot reduce the max number of guests below the number of guests already participating');
                    return $this->redirectToRoute('app_event_edit', ['id' => $event->getId()]);
                    
                }

                // Si l'événement est publique et n'est pas visible, le rend visible
                if (! $event->isPrivate() && ! $event->isVisible()){
                    $event->setVisible(true);
                }

                // Fixe le propriétaire de l'événement
                $event->setOwner($this->getUser());

                // Enregistre l'événement en base de données
                $this->manager->persist($event);
                $this->manager->flush();

                // Ajoute un message flash de succès et redirige vers la liste des événements
                $this->addFlash('success', 'Event created');
                return $this->redirectToRoute('app_events');
            } 
            // Sinon...
            else {
                // Ajoute un message flash d'erreur
                $error = $form->getErrors(true);
                foreach ($error as $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
        }

        // Affiche le formulaire de l'événement
        return $this->render('event/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprime un événement
     * 
     * @param Event $event L'événement à supprimer
     * @return Response
     */
    #[Route('/event-delete/{id}', name: 'app_event_delete')]
    public function deleteEvent(Event $event): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in to delete an event');
            return $this->redirectToRoute('app_login');
        }

        // Si l'utilisateur n'est pas le propriétaire de l'événement, redirige vers la liste des événements
        if ($event->getOwner() !== $user) {
            $this->addFlash('danger', 'You cannot delete this event');
            return $this->redirectToRoute("app_events");
        }

        // Crée une notification pour chaque utilisateur participant à l'événement
        $this->notificationService->createDeleteEventNotification($event);

        // Supprime l'événement de la base de données
        $this->manager->remove($event);
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers la liste des événements
        $this->addFlash('success', 'Event deleted');
        return $this->redirectToRoute('app_events');
    }

    /**
     * Participer à un événement
     * 
     * @param Event $event L'événement auquel participer
     * @return Response
     */
    #[Route('/event-participate/{id}', name: 'app_event_participate')]
    public function participate(Event $event): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in to participate');
            return $this->redirectToRoute('app_login');
        }

        // Vérifie si l'utilisateur a le droit de participer à l'événement
        try {
            $this->denyAccessUnlessGranted(
                'participate', 
                [
                    $event,
                    $user
                ]
            );
        }
        catch (AccessDeniedException) {
            $this->addFlash('danger', 'You cannot participate to this event');
            return $this->redirectToRoute("app_events");
        }

        // Si l'événement est privé, redirige vers la demande de participation
        if ($event->isPrivate()){
            return $this->redirectToRoute('app_event_participate_request', ['id' => $event->getId()]);
        }

        // Ajoute l'utilisateur à la liste des participants
        $event->addGuest($user);
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers l'événement
        $this->addFlash('success', 'You are now participating in the event');
        return $this->redirectToRoute('app_event', ['id' => $event->getId()]);
    }

    /**
     * Permet de faire une demande de participation à un événement
     * 
     * @param Request $request
     * @param Event $event L'événement auquel participer
     * @return Response
     */
    #[Route('/event-participate/request/{id}', name: 'app_event_participate_request')]
    public function eventMakeRequest(Request $request, Event $event): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Verifie si l'utilisateur peut participer à l'événement
        try {
            $this->denyAccessUnlessGranted(
                'participate', 
                [
                    $event,
                    $user
                ]
            );
        }
        catch (AccessDeniedException) {
            $this->addFlash('danger', 'You cannot participate to this event');
            return $this->redirectToRoute("app_events");
        }

        // Récupère la demande de participation en attente de l'utilisateur si elle existe
        $demand = $event->getPendingDemandForUser($user);

        // Si la demande existe, affiche un message d'information
        if ($demand) {
            $this->addFlash('info', 'You already have a pending request. By continuing, you will modify it.');
        } 

        // Sinon, crée une nouvelle demande de participation
        else {
            $demand = new Demand();
            $demand->setCurrentState('pending');
        }

        // Crée et traite le formulaire de demande de participation
        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide...
        if ($form->isSubmitted() && $form->isValid()) {
            // Fixe l'événement et l'utilisateur de la demande
            $demand->setEvent($event);
            $demand->setUser($user);

            // Enregistre la demande en base de données
            $this->manager->persist($demand);
            $this->manager->flush();

            // Ajoute un message flash de succès et redirige vers la liste des événements
            $this->addFlash('success', 'Demand sent');
            return $this->redirectToRoute('app_events');

        }

        // Affiche le formulaire de demande de participation
        return $this->render('event/demand.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    /**
     * Accepte une invitation à un événement
     * 
     * @param Event $event L'événement en question
     * @return Response
     */
    #[Route('/event-invite/accept/{event}', name: 'app_event_invite_accept')]
    public function inviteAccept(Event $event): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in to participate');
            return $this->redirectToRoute('app_login');
        }

        // Vérifie que l'email de l'utilisateur est dans la liste des invités
        if (! in_array($user->getEmail(), $event->getInvitedEmails())) {
            $this->addFlash('danger', 'You are not invited to this event');
            return $this->redirectToRoute("app_events");
        }

        // Supprime l'email de l'utilisateur de la liste des invités et l'ajoute à la liste des participants
        $event->removeInvitedEmail($user->getEmail());
        $event->addGuest($user);

        // Enregistre les modifications en base de données
        $this->manager->persist($event);
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers l'événement
        return $this->redirectToRoute('app_event', ['id' => $event->getId()]);
    }

    /**
     * Annule une invitation à un événement
     * 
     * @param Event $event L'événement en question
     * @param string $email L'email de l'utilisateur invité (optionnel)
     * @return Response
     */
    #[Route('/event-invite/cancel/{event}/{email?}', name: 'app_event_invite_cancel')]
    public function inviteCancel(Event $event, ?string $email): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in to cancel an invitation');
            return $this->redirectToRoute('app_login');
        }

        // Si l'email n'est pas fourni, utilise l'email de l'utilisateur connecté
        if (! $email){
            $email = $user->getEmail();
        }
        
        // Vérifie si l'utilisateur a le droit d'annuler l'invitation
        try {
            $this->denyAccessUnlessGranted(
                'cancelInvitation', 
                [
                    $event,
                    $user,
                    $email
                ]
            );
        }
        catch (AccessDeniedException) {
            $this->addFlash('danger', 'You cannot cancel this invitation');
            return $this->redirectToRoute("app_events");
        }

        // Supprime l'email de la liste des invités
        $event->removeInvitedEmail($email);

        // Enregistre les modifications en base de données
        $this->manager->persist($event);
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers la liste des événements
        $this->addFlash('success', 'Invitation canceled');
        return $this->redirectToRoute('app_events');
    }

    /**
     * Accepte une demande de participation à un événement
     * 
     * @param Event $event L'événement en question
     * @param Demand $demand La demande de participation
     * @return Response
     */
    #[Route('/event-demand/accept/{event}/{demand}', name: 'app_event_demand_accept')]
    public function acceptDemand(Event $event, Demand $demand): Response
    {
        // Vérifie si l'utilisateur est le propriétaire de l'événement
        if ($event->getOwner() !== $this->getUser()) {  
            $this->addFlash('danger', 'You cannot accept this demand');
            return $this->redirectToRoute("app_events");
        }

        // Vérifie si la demande est liée à l'événement et si elle peut être acceptée
        if ($demand->getEvent() !== $event || ! $this->demandResolving->can($demand, 'to_accept')) {
            $this->addFlash('danger', 'This demand cannot be accepted');
            return $this->redirectToRoute("app_events");
        }

        // Accepte la demande et ajoute l'utilisateur à la liste des participants
        $this->demandResolving->apply($demand, 'to_accept');
        $guest = $demand->getUser();
        $event->addGuest($guest);
        
        // Crée une notification pour l'utilisateur invité
        $this->notificationService->createDemandAcceptedNotification($guest, $event);

        // Enregistre les modifications en base de données
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers l'événement
        $this->addFlash('success', 'Demand accepted');
        return $this->redirectToRoute('app_event', ['id' => $event->getId()]);
    }

    /**
     * Rejette une demande de participation à un événement
     * 
     * @param Event $event L'événement en question
     * @param Demand $demand La demande de participation
     * @return Response
     */
    #[Route('/event-demand/reject/{event}/{demand}', name: 'app_event_demand_reject')]
    public function rejectDemand(Event $event, Demand $demand): Response
    {
        // Vérifie si l'utilisateur est le propriétaire de l'événement
        if ($event->getOwner() !== $this->getUser()) {  
            $this->addFlash('danger', 'You cannot reject this demand');
            return $this->redirectToRoute("app_events");
        }

        // Vérifie si la demande est liée à l'événement et si elle peut être rejetée
        if ($demand->getEvent() !== $event || ! $this->demandResolving->can($demand, 'to_reject')) {
            $this->addFlash('danger', 'This demand cannot be rejected');
            return $this->redirectToRoute("app_events");
        }

        // Rejette la demande
        $this->demandResolving->apply($demand, 'to_reject');

        // Crée une notification pour l'utilisateur invité
        $this->notificationService->createDemandRejectedNotification($demand->getUser(), $event);

        // Enregistre les modifications en base de données
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers l'événement
        $this->addFlash('success', 'Demand rejected');
        return $this->redirectToRoute('app_event', ['id' => $event->getId()]);
    }

    /**
     * Supprime un invité d'un événement
     * 
     * @param Event $event L'événement en question
     * @param User $guest L'invité à supprimer
     * @return Response
     */
    #[Route('/event/guest-remove/{event}/{guest}', name: 'app_event_guest_remove')]
    public function removeGuest(Event $event, User $guest): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in to remove a guest');
            return $this->redirectToRoute('app_login');
        }

        // Vérifie si l'utilisateur est le propriétaire de l'événement
        if ($event->getOwner() !== $user) {
            $this->addFlash('danger', 'You cannot remove this guest');
            return $this->redirectToRoute("app_events");
        }

        // Supprime l'invité de la liste des participants
        $event->removeGuest($guest);

        // Crée une notification pour l'invité
        $this->notificationService->createExpulsionNotification($guest, $event);
        
        // Enregistre les modifications en base de données
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers l'événement
        $this->addFlash('success', 'Guest removed');
        return $this->redirectToRoute('app_event', ['id' => $event->getId()]);
    }

    /**
     * Quitte un événement
     * 
     * @param Event $event L'événement à quitter
     * @return Response
     */
    #[Route('/event-leave/{id}', name: 'app_event_leave')]
    public function leave(Event $event): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in to leave an event');
            return $this->redirectToRoute('app_login');
        }

        // Retire l'utilisateur de la liste des participants
        $event->removeGuest($user);

        // Enregistre les modifications en base de données
        $this->manager->persist($event);
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers la liste des événements
        $this->addFlash('success', 'You left the event');
        return $this->redirectToRoute('app_events');
    }
}

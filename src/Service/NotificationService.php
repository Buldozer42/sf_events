<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour la gestion des notifications
 */
class NotificationService
{
    public function __construct(private EntityManagerInterface $entityManager){}

    /**
     * Crée une notification pour un utilisateur
     * 
     * @param User $user
     * @param string $content Le contenu de la notification
     * @return Notification
     */
    public function createNotification(User $user, string $content): Notification
    {
        // Création de la notification
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setContent($content);

        // Enregistrement en base de données
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // Retourne la notification
        return $notification;
    }

    /**
     * Crée une notification pour un utilisateur qui a été expulsé d'un événement
     * 
     * @param User $user
     * @param Event $event
     * @return void
     */
    public function createExpulsionNotification(User $user, Event $event): void
    {
        $this->createNotification($user, 'You have been expelled from the event ' . $event->getName().'.');
    }

    /**
     * Crée une notification pour chaque invité d'un événement qui a été supprimé
     * 
     * @param Event $event
     * @return void
     */
    public function createDeleteEventNotification(Event $event): void
    {
        $content = 'The event ' . $event->getName() . ' has been deleted.';
        $users = $event->getGuests();
        foreach ($users as $user) {
            $this->createNotification($user, $content);
        }
    }

    /**
     * Crée une notification pour un utilisateur qui a été accepté à un événement
     * 
     * @param User $user
     * @param Event $event
     * @return void
     */
    public function createDemandAcceptedNotification(User $user, Event $event): void
    {
        $content = 'Your demand to participate in the event ' . $event->getName() . ' has been accepted.';
        $this->createNotification($user, $content);
    }


    /**
     * Crée une notification pour un utilisateur qui a été refusé à un événement
     * 
     * @param User $user
     * @param Event $event
     * @return void
     */
    public function createDemandRejectedNotification(User $user, Event $event): void
    {
        $content = 'Your demand to participate in the event ' . $event->getName() . ' has been rejected.';
        $this->createNotification($user, $content);
    }

    /**
     * Crée une notification pour chaque invité d'un événement qui a reçu un nouveau commentaire
     * 
     * @param User $user
     * @param Event $event
     * @return void
     */
    public function createCommentNotification(User $user, Event $event): void
    {
        $content = 'A new comment has been posted on the event ' . $event->getName() . '.';
        $users = $event->getGuests();
        $users = $users->filter(fn(User $guest) => $guest->getId() !== $user->getId());
        foreach ($users as $guest) {
            if (!$this->checkNotificationExist($guest, $content)) {
                $this->createNotification($guest, $content);
            }
        }
    }

    /**
     * Vérifie si une notification existe déjà pour un utilisateur
     * 
     * @param User $user
     * @param string $content Le contenu de la notification
     * @return bool
     */
    private function checkNotificationExist(User $user, string $content): bool
    {
        $notifications = $user->getNotifications();
        foreach ($notifications as $notification) {
            if ($notification->getContent() === $content) {
                return true;
            }
        }
        return false;
    }

    /**
     * Crée une notification pour un utilisateur qui a été invité à un événement
     * 
     * @param User $user
     * @param Event $event
     * @return void
     */
    public function createInvitationNotification(User $user, Event $event): void
    {
        $content = 'You have been invited to the event ' . $event->getName() . '.';
        $this->createNotification($user, $content);
    }
}
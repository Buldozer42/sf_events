<?php

namespace App\Controller;

use App\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Contrôleur des notifications
 */
#[Route('/notification')]
class NotificationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ){}

    /**
     * Supprime une notification
     * 
     * @param Notification $notification La notification à supprimer
     * @return Response
     */
    #[Route('/delete/{id}', name: 'app_notification_delete')]
    public function deleteNotification(Notification $notification): Response
    {
        // Vérifie que l'utilisateur connecté est bien le 'propriétaire' de la notification
        if ($notification->getUser() !== $this->getUser()) {
            $this->addFlash('danger', 'You cannot delete this notification.');
        }
        else {
            // Supprime la notification
            $this->entityManager->remove($notification);
            $this->entityManager->flush();
        }

        // Redirige vers le profil de l'utilisateur
        return $this->redirectToRoute('app_user');
    }
}

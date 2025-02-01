<?php

namespace App\Controller;

use App\Entity\Type;
use App\Entity\Event;
use App\Entity\User;
use App\Entity\Comment;
use App\Form\TypeType;
use App\Repository\EventRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Contrôleur de la section d'administration
 */
#[Route('/admin')]
class AdminController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $manager,
    ){}

    /**
     * Affiche la liste des types d'événements
     * 
     * @param TypeRepository $typeRepository Le repository des types d'événements
     * @return Response
     */

    #[Route('/types', name: 'app_admin_types')]
    public function listTypes(TypeRepository $typeRepository): Response
    {
        return $this->render('admin/types.html.twig', [
            'types' => $typeRepository->findAll()
        ]);
    }

    /**
     * Ajoute ou modifie un type d'événement
     * 
     * @param Request $request La requête HTTP
     * @param Type|null $type Le type d'événement à modifier (null si on ajoute un type)
     * @return Response
     */
    #[Route('/type/add', name: 'app_admin_type_add')]
    #[Route('/type/modify/{id}', name: 'app_admin_type_modify')]
    public function addType(Request $request, ?Type $type = null): Response
    {
        // Si le type n'existe pas, crée un nouveau type
        if (! $type) {
            $type = new Type();
        }

        // Prépare et traite le formulaire de type
        $form = $this->createForm(TypeType::class, $type);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide...
        if ($form->isSubmitted() && $form->isValid()) {
            // enregistre le type en base de données
            $this->manager->persist($type);
            $this->manager->flush();

            // Ajoute un message flash de succès et redirige vers la liste des types
            $this->addFlash('success', 'Type added successfully !');
            return $this->redirectToRoute('app_admin_types');
        }

        return $this->render('admin/addType.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprime un type d'événement
     * 
     * @param Type $type Le type d'événement à supprimer
     * @return Response
     */
    #[Route('/type/delete/{id}', name: 'app_admin_type_delete')]
    public function deleteType(Type $type): Response
    {
        // Supprime le type de la base de données
        $this->manager->remove($type);
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers la liste des types
        $this->addFlash('success', 'Type deleted successfully !');
        return $this->redirectToRoute('app_admin_types');
    }

    /**
     * Affiche la liste des événements
     * 
     * @param EventRepository $eventRepository Le repository des événements
     * @return Response
     */
    #[Route('/events', name: 'app_admin_events')]
    public function listEvents(EventRepository $eventRepository): Response
    {
        return $this->render('admin/events.html.twig', [
            'events' => $eventRepository->findAll(),
            'user' => $this->getUser()
        ]);
    }

    /**
     * Supprime un événement
     * 
     * @param Event $event L'événement à supprimer
     * @return Response
     */
    #[Route('/event/delete/{id}', name: 'app_admin_event_delete')]
    public function deleteEvent(Event $event): Response
    {
        // Supprime l'événement de la base de données
        $this->manager->remove($event);
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers la liste des événements
        $this->addFlash('success', 'Event deleted successfully !');
        return $this->redirectToRoute('app_admin_events');
    }

    /**
     * Affiche la liste des utilisateurs
     * 
     * @param UserRepository $userRepository Le repository des utilisateurs
     * @return Response
     */
    #[Route('/users', name: 'app_admin_users')]
    public function listUsers(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    /**
     * Supprime un utilisateur
     * 
     * @param User $user L'utilisateur à supprimer
     * @return Response
     */
    #[Route('/user/delete/{id}', name: 'app_admin_user_delete')]
    public function deleteUser(User $user): Response
    {
        // Supprime l'utilisateur de la base de données
        $this->manager->remove($user);
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers la liste des utilisateurs
        $this->addFlash('success', 'User deleted successfully !');
        return $this->redirectToRoute('app_admin_users');
    }

    /**
     * Supprime un commentaire
     * 
     * @param Comment $comment Le commentaire à supprimer
     * @return Response
     */
    #[Route('/comment/delete/{id}', name: 'app_admin_comment_delete')]
    public function deleteComment(Comment $comment): Response
    {
        // Supprime le commentaire de la base de données
        $this->manager->remove($comment);
        $this->manager->flush();

        // Ajoute un message flash de succès et redirige vers la liste des événements
        $this->addFlash('success', 'Comment deleted successfully !');
        return $this->redirectToRoute('app_admin_events');
    }
}

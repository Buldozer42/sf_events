<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\MercureTokenGenerator;

/**
 * Contrôleur des éléments de sécurité
 */
class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager)
    {}

    /**
     * Affiche le formulaire de connexion
     * 
     * @param AuthenticationUtils $authenticationUtils Les utilitaires d'authentification
     * @return Response
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Affiche le formulaire de connexion
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Déconnecte l'utilisateur
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Affiche le formulaire d'inscription
     * 
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher L'interface de hachage des mots de passe
     * @param UserRepository $userRepo Le repository des utilisateurs
     * @return Response
     */
    #[Route(path: '/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $hasher, UserRepository $userRepo): Response
    {
        // Crée un nouvel utilisateur
        $user = new User();

        // Crée et traite le formulaire d'inscription
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis...
        if ($form->isSubmitted()) {
            // Si le formulaire est valide...
            if ($form->isValid()) {
                // Récupère le mot de passe et le mot de passe de confirmation
                $password = $form->get('password')->getData();
                $confirmPassword = $form->get('confirm_password')->getData();

                // Vérifie que les mots de passe correspondent
                if ($password !== $confirmPassword) {
                    $this->addFlash('danger', 'Passwords do not match');
                } 
                // Vérifie que l'email n'est pas déjà utilisé
                else if ($userRepo->findOneBy(['email' => $user->getEmail()])) {
                    $this->addFlash('danger', 'Email already in use');
                } 
                // Si tout est bon...
                else {
                    // Fixe le mot de passe haché de l'utilisateur
                    $user->setPassword(
                        $hasher->hashPassword($user, $password)
                    );
                    // Fixe le rôle de l'utilisateur
                    $user->setRoles(['ROLE_USER']);

                    // Enregistre l'utilisateur en base de données
                    $this->manager->persist($user);
                    $this->manager->flush();

                    // Ajoute un message flash de succès et redirige vers la page de connexion
                    $this->addFlash('success', 'You are now registered! Please login');
                    return $this->redirectToRoute('app_login');
                }
            } 
            // Sinon...
            else {
                // Ajoute un message flash d'erreur pour chaque erreur
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }
        }

        // Affiche le formulaire d'inscription
        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Abonne un utilisateur à un événement Mercure (Fonctionnalité non implémentée)
     * 
     * @param string $eventId L'identifiant de l'événement
     * @param MercureTokenGenerator $tokenGenerator Le générateur de jetons Mercure
     * @return JsonResponse
     */
    #[Route(path: '/mercure/subscribe/{eventId}', name: 'mercure_subscribe')]
    public function subscribe(string $eventId, MercureTokenGenerator $tokenGenerator): JsonResponse
    {
        $topic = "event/$eventId";
        $jwt = $tokenGenerator->generate($topic);

        return new JsonResponse(['jwt' => $jwt]);
    }
}

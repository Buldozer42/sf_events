<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter pour les événements (Event)
 */
class EventVoter extends Voter
{
    private const PARTICIPATE = 'participate';
    private const VIEW = 'view';
    private const CANCELINVITATION = 'cancelInvitation';

    protected function supports(string $attribute, $subject): bool
    {
        // Verifier si l'attribut est supporté
        if (!in_array(
            $attribute, 
            [
                self::PARTICIPATE, 
                self::VIEW,
                self::CANCELINVITATION
            ]))
        {
            return false;
        }

        // Verifier que le premier sujet est un événement
        if (!$subject[0] instanceof Event) {
            return false;
        }
        
        // Verifier que le deuxième sujet est un utilisateur
        if (!$subject[1] instanceof User) {
            return false;
        }

        // Verifier que le troisième sujet est une chaine de caractères si l'attribut est CANCELINVITATION
        if ($attribute === self::CANCELINVITATION && !is_string($subject[2])) {
            return false;
        }

        return true;
    }

    /**
     * Vote sur l'attribut et le sujet donnés
     * 
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * 
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        // Récupére l'utilisateur connecté
        $user = $token->getUser();

        // Retourne false si l'utilisateur n'est pas connecté
        if (!$user instanceof User) {
            return false;
        }

        // Appele la méthode correspondante a l'attribut souhaité
        switch ($attribute) {
            case self::PARTICIPATE:
                return $this->canParticipate($subject[0], $subject[1]);
            case self::VIEW:
                return $this->canView($subject[0], $subject[1]);
            case self::CANCELINVITATION:
                return $this->canCancelInvitation($subject[0], $subject[1], $subject[2]);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Vérifie si l'utilisateur peut participer à un événement
     * 
     * @param Event $event
     * @param User $user
     * 
     * @return bool
     */
    private function canParticipate(Event $event, User $user): bool
    {
        // Vérifie si l'événement est complet
        if ($event->getMaxGuests() <= count($event->getGuests())) {
            return false;
        }

        // Vérifie si l'utilisateur participe déjà à l'événement
        if ($event->getGuests()->contains($user)) {
            return false;
        }

        // Vérifie si l'utilisateur est le propriétaire de l'événement
        if ($event->getOwner() === $user) {
            return false;
        }

        // Vérifie si l'événement est visible
        if (! $event->isVisible()) {
            return false;
        }

        // Vérifie si l'événement est passé
        if ($event->getDate() < new \DateTime()) {
            return false;
        }
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut voir un événement
     * 
     * @param Event $event
     * @param User $user
     * 
     * @return bool
     */
    private function canView(Event $event, User $user): bool
    {
        // Vérifie si l'utilisateur est le propriétaire de l'événement
        if ($event->getOwner() === $user) {
            return true;
        }

        // Vérifie si l'utilisateur participe à l'événement
        if ($event->getGuests()->contains($user)) {
            return true;
        }
        return false;
    }

    /**
     * Vérifie si l'utilisateur peut annuler une invitation à un événement
     * 
     * @param Event $event
     * @param User $user
     * @param string $email
     * 
     * @return bool
     */
    private function canCancelInvitation(Event $event, User $user, string $email) : bool
    {
        // Vérifie si l'utilisateur est le propriétaire de l'événement
        if ($event->getOwner() === $user) {
            return true;
        }

        // Vérifie si l'email est celui de l'utilisateur
        if ($user->getEmail() === $email) {
            return true;
        }

        // Vérifie si l'email est dans la liste des invités 
        if (in_array($user->getEmail(), $event->getInvitedEmails())) {
            return true;
        }

        return false;
    }
}

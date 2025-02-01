<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

/**
 * Fixtures de commentaires
 */
class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Générateur de données aléatoires
        $faker = Factory::create();

        // Récupération de tous les événements
        $events = $manager->getRepository(Event::class)->findAll();

        // Pour chaque événement...
        foreach ($events as $event) {
            // Defini de 0 à 5 commentaires
            $i = $faker->numberBetween(0, 5);
            while ($i > 0) {
                // Si l'événement n'a pas de participants, on arrête là
                if ($event->getGuests()->count() === 0) {
                    break;
                }

                // Sélectionne un utilisateur au hasard parmi les participants
                $user = $event->getGuests()[$faker->numberBetween(0, $event->getGuests()->count() - 1)];

                // Crée un commentaire attaché à cet utilisateur et cet événement
                $comment = new Comment($user, $event);

                // Définit une date comprise entre la date de l'événement et maintenant
                // ou comprise entre la date de l'événement et une semaine avant, si l'événement est passé
                $eventDate = $event->getDate();
                if ($eventDate > new \DateTime()){
                    $date = $faker->dateTimeBetween('-1 week', 'now');
                } else {
                    $startDate = new \DateTime();
                    $startDate->setTimestamp($eventDate->getTimestamp());
                    $date = $faker->dateTimeBetween(
                        (new \DateTime())->setTimestamp($eventDate->getTimestamp())->modify('-1 week'),
                         $eventDate
                    );
                } 
                $date = \DateTimeImmutable::createFromMutable($date);

                // Définit le contenu du commentaire et sa date de soumission
                $comment
                    ->setContent($faker->sentence())
                    ->setSubmittedAt($date);

                // Sauvegarde le commentaire
                $manager->persist($comment);
                $i--;
            }
        }
        // Exécute l'enregistrement en base de données
        $manager->flush();
    }

    public function getDependencies(): array
    {
        
        return [
            UserFixtures::class,
            EventFixtures::class,
        ];
    }
}

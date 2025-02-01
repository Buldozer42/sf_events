<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }
    
    /**
     * Retourne tous les événements actifs
     * 
     * @return array
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.date > CURRENT_TIMESTAMP()')
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne tous les événements actifs et visibles
     * 
     * @return array
     */
    public function findAllActiveAndVisible(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.date > CURRENT_TIMESTAMP()')
            ->andWhere('e.visible = true')
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Retourne tous les événements pour lesquels un email est invité
     * 
     * @param string $email Email à rechercher
     * @return array
     */
    public function findInvitedEvents(string $email): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.invitedEmails LIKE :email')
            ->setParameter('email', '%'.$email.'%')
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des événements en fonction de critères
     * 
     * @param array $parameter Paramètres de recherche
     * @return array
     */
    public function search(array $parameter): array
    {
        // Récupération des paramètres
        $name = $parameter['search'] ?? null;
        $private = $parameter['isPrivate'] ?? null;
        $price = $parameter['price'] ?? null;
        $date = $parameter['date'] ?? 'CURRENT_TIMESTAMP()';
        $type = $parameter['type'] ?? null;

        // Création d'une requête triée par date
        $querry = $this->createQueryBuilder('e')
            ->orderBy('e.date', 'ASC');

        // Ajout des conditions
        if ($name) {
            $querry->andWhere('e.name LIKE :name')
                ->setParameter('name', "%$name%");
        }
        if ($private) {
            $querry->andWhere('e.private = :private')
                ->setParameter('private', $private);
        }

        if ($price) {
            $querry->andWhere('e.price <= :price')
                ->setParameter('price', $price);
        }

        if ($type) {
            $querry->andWhere('e.type = :type')
                ->setParameter('type', $type);
        }

        // Completion de la requête avec la date
        return $querry->andWhere('e.date > :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

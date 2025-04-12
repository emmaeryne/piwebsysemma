<?php

namespace App\Repository;

use App\Entity\Abonnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Abonnement>
 */
class AbonnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Abonnement::class);
    }

    public function findAllSorted(?string $sort = null): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($sort) {
            switch ($sort) {
                case 'prix_asc':
                    $qb->orderBy('a.prixTotal', 'ASC');
                    break;
                case 'prix_desc':
                    $qb->orderBy('a.prixTotal', 'DESC');
                    break;
                case 'date_debut_asc':
                    $qb->orderBy('a.dateDebut', 'ASC');
                    break;
                case 'date_debut_desc':
                    $qb->orderBy('a.dateDebut', 'DESC');
                    break;
            }
        }

        return $qb->getQuery()->getResult();
    }
}
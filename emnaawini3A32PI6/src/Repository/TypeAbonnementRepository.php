<?php

namespace App\Repository;

use App\Entity\TypeAbonnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeAbonnement>
 */
class TypeAbonnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeAbonnement::class);
    }

    /**
     * Récupère tous les types d'abonnements avec tri et recherche.
     *
     * @param string $sort Champ de tri (ex: prix_asc, prix_desc, nom_asc, nom_desc)
     * @param string $search Terme de recherche
     * @param int $limit Nombre d'éléments par page
     * @param int $offset Décalage pour la pagination
     * @return TypeAbonnement[]
     */
    public function findAllSorted(string $sort, string $search, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('ta');

        // Recherche
        if ($search) {
            $qb->andWhere('ta.nom LIKE :search OR ta.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Tri
        if ($sort) {
            switch ($sort) {
                case 'prix_asc':
                    $qb->orderBy('ta.prix', 'ASC');
                    break;
                case 'prix_desc':
                    $qb->orderBy('ta.prix', 'DESC');
                    break;
                case 'nom_asc':
                    $qb->orderBy('ta.nom', 'ASC');
                    break;
                case 'nom_desc':
                    $qb->orderBy('ta.nom', 'DESC');
                    break;
                default:
                    $qb->orderBy('ta.id', 'ASC'); // Tri par défaut
                    break;
            }
        } else {
            $qb->orderBy('ta.id', 'ASC'); // Tri par défaut si aucun tri spécifié
        }

        // Pagination
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte le nombre total de types d'abonnements correspondant à la recherche.
     *
     * @param string $search Terme de recherche
     * @return int
     */
    public function countBySearch(string $search): int
    {
        $qb = $this->createQueryBuilder('ta')
                   ->select('COUNT(ta.id)');

        if ($search) {
            $qb->andWhere('ta.nom LIKE :search OR ta.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Récupère les types d'abonnements les plus réservés.
     *
     * @return TypeAbonnement[]
     */
    public function findMostReserved(): array
    {
        return $this->createQueryBuilder('ta')
                    ->select('ta')
                    ->leftJoin('ta.reservations', 'r')
                    ->groupBy('ta.id')
                    ->orderBy('COUNT(r.id)', 'DESC')
                    ->setMaxResults(5)
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Récupère des recommandations de types d'abonnements.
     *
     * @return TypeAbonnement[]
     */
    public function findRecommendations(): array
    {
        return $this->createQueryBuilder('ta')
                    ->where('ta.isPremium = :premium')
                    ->setParameter('premium', true)
                    ->orderBy('ta.prix', 'ASC')
                    ->setMaxResults(5)
                    ->getQuery()
                    ->getResult();
    }
}
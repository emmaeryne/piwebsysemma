<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /**
     * Recherche des services avec filtres personnalisés
     *
     * @param string $search Terme de recherche pour nom ou description
     * @param string $categorie Filtre par catégorie
     * @param ?float $prixMax Prix maximum (nullable)
     * @param string $sortBy Champ de tri
     * @param string $sortOrder Ordre de tri (ASC/DESC)
     * @param bool $returnQuery Si true, retourne un Query au lieu des résultats
     * @return array|Query Liste des services filtrés ou Query paginable
     */
    public function findByFilters(
        string $search = '',
        string $categorie = '',
        ?float $prixMax = null,
        string $sortBy = 'nom',
        string $sortOrder = 'ASC',
        bool $returnQuery = false
    ): mixed {
        $qb = $this->createQueryBuilder('s');

        if ($search) {
            $qb->andWhere('s.nom LIKE :search OR s.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($categorie) {
            $qb->andWhere('s.categorie = :categorie')
               ->setParameter('categorie', $categorie);
        }

        if ($prixMax !== null) {
            $qb->andWhere('s.prix <= :prixMax')
               ->setParameter('prixMax', $prixMax);
        }

        $allowedSortFields = ['nom', 'prix', 'categorie', 'nombreReservations'];
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'nom';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $qb->orderBy("s.{$sortBy}", $sortOrder);

        if ($returnQuery) {
            return $qb->getQuery();
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les services les plus populaires
     *
     * @param int $limit Nombre maximum de résultats
     * @return Service[]
     */
    public function findPopularServices(int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.nombreReservations', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les services par taux d'utilisation
     *
     * @param float $minRate Taux minimum
     * @param float $maxRate Taux maximum
     * @return Service[]
     */
    public function findServicesByUtilizationRate(float $minRate = 0, float $maxRate = 100): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.capaciteMax > 0')
            ->andWhere('s.nombreReservations / s.capaciteMax * 100 BETWEEN :minRate AND :maxRate')
            ->setParameter('minRate', $minRate)
            ->setParameter('maxRate', $maxRate)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques par catégorie
     *
     * @return array Statistiques agrégées par catégorie
     */
    public function getCategoryStatistics(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.categorie, COUNT(s.id) as service_count, AVG(s.prix) as avg_price')
            ->groupBy('s.categorie')
            ->getQuery()
            ->getArrayResult();
    }
}
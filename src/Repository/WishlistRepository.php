<?php

namespace App\Repository;

use App\Entity\Wishlist;
use App\Entity\Etudiant;
use App\Entity\OffreDeStage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wishlist>
 */
class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    public function findByEtudiant(Etudiant $etudiant): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.etudiant = :etudiant')
            ->setParameter('etudiant', $etudiant)
            ->orderBy('w.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByEtudiantAndOffre(Etudiant $etudiant, OffreDeStage $offre): ?Wishlist
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.etudiant = :etudiant')
            ->andWhere('w.offreDeStage = :offre')
            ->setParameter('etudiant', $etudiant)
            ->setParameter('offre', $offre)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 
 
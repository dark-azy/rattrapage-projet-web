<?php

namespace App\Repository;

use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    public function findNoteByEtudiantAndEntreprise(string $nomEtudiant, string $nomEntreprise): ?Note
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.nomEtudiant = :nomEtudiant')
            ->andWhere('n.nomEntreprise = :nomEntreprise')
            ->setParameter('nomEtudiant', $nomEtudiant)
            ->setParameter('nomEntreprise', $nomEntreprise)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 
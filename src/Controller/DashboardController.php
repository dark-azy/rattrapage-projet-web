<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Entity\PiloteDePromotion;
use App\Entity\Entreprise;
use App\Entity\OffreDeStage;
use App\Entity\Promotion;
use App\Entity\Candidature;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            // Statistiques pour l'admin
            $stats = [
                'etudiants' => $entityManager->getRepository(Etudiant::class)->count([]),
                'pilotes' => $entityManager->getRepository(PiloteDePromotion::class)->count([]),
                'entreprises' => $entityManager->getRepository(Entreprise::class)->count([]),
                'offres' => $entityManager->getRepository(OffreDeStage::class)->count([]),
                'candidatures' => $entityManager->getRepository(Candidature::class)->count([]),
                'promotions' => $entityManager->getRepository(Promotion::class)->count([])
            ];

            // Liste des étudiants avec leurs promotions
            $etudiants = $entityManager->getRepository(Etudiant::class)
                ->createQueryBuilder('e')
                ->leftJoin('e.promotion', 'p')
                ->addSelect('p')
                ->getQuery()
                ->getResult();
            
            // Distribution des étudiants par promotion
            $promotions = $entityManager->getRepository(Promotion::class)->findAll();
            $distributionPromos = [];
            foreach ($promotions as $promotion) {
                $distributionPromos[$promotion->getNom()] = count($promotion->getEtudiants());
            }

            // Offres de stage par mois
            $offres = $entityManager->getRepository(OffreDeStage::class)->findAll();
            $offresParMois = array_fill(1, 12, 0); // Initialise un tableau avec 12 mois à 0
            
            foreach ($offres as $offre) {
                if ($offre->getDateDebutStage()) {
                    $mois = (int)$offre->getDateDebutStage()->format('n');
                    $offresParMois[$mois]++;
                }
            }
            
            // Convertir en format attendu par le template
            $offresParMoisFormatted = [];
            foreach ($offresParMois as $mois => $nombre) {
                $offresParMoisFormatted[] = [
                    'mois' => $mois,
                    'nombre' => $nombre
                ];
            }

            // Récupérer les dernières candidatures
            $dernieresCandidatures = $entityManager->getRepository(Candidature::class)
                ->createQueryBuilder('c')
                ->leftJoin('c.etudiant', 'e')
                ->leftJoin('c.offreDeStage', 'o')
                ->addSelect('e', 'o')
                ->orderBy('c.date_candidature', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();

            return $this->render('dashboard/admin.html.twig', [
                'stats' => $stats,
                'etudiants' => $etudiants,
                'distributionPromos' => $distributionPromos,
                'offresParMois' => $offresParMoisFormatted,
                'dernieresCandidatures' => $dernieresCandidatures
            ]);

        } elseif ($this->isGranted('ROLE_PILOTE')) {
            /** @var \App\Entity\PiloteDePromotion $pilote */
            $pilote = $this->getUser();
            
            // Statistiques pour le pilote
            $stats = [
                'etudiants' => $entityManager->getRepository(Etudiant::class)
                    ->createQueryBuilder('e')
                    ->join('e.promotion', 'p')
                    ->where('p.pilote = :pilote')
                    ->setParameter('pilote', $pilote)
                    ->select('COUNT(e.id)')
                    ->getQuery()
                    ->getSingleScalarResult(),
                'offres' => $entityManager->getRepository(OffreDeStage::class)
                    ->createQueryBuilder('o')
                    ->where('o.pilote = :pilote')
                    ->setParameter('pilote', $pilote)
                    ->select('COUNT(o.id)')
                    ->getQuery()
                    ->getSingleScalarResult(),
                'entreprises' => $entityManager->getRepository(Entreprise::class)
                    ->createQueryBuilder('e')
                    ->join('e.offresDeStage', 'o')
                    ->where('o.pilote = :pilote')
                    ->setParameter('pilote', $pilote)
                    ->select('COUNT(DISTINCT e.id)')
                    ->getQuery()
                    ->getSingleScalarResult(),
                'etudiantsEnStage' => $entityManager->getRepository(Etudiant::class)
                    ->createQueryBuilder('e')
                    ->join('e.promotion', 'p')
                    ->where('p.pilote = :pilote')
                    ->andWhere('e.statut = :statut')
                    ->setParameter('pilote', $pilote)
                    ->setParameter('statut', 'En stage')
                    ->select('COUNT(e.id)')
                    ->getQuery()
                    ->getSingleScalarResult(),
                'candidaturesEnAttente' => $entityManager->getRepository(Candidature::class)
                    ->createQueryBuilder('c')
                    ->join('c.etudiant', 'e')
                    ->join('e.promotion', 'p')
                    ->where('p.pilote = :pilote')
                    ->andWhere('c.statut = :statut')
                    ->setParameter('pilote', $pilote)
                    ->setParameter('statut', 'En attente')
                    ->select('COUNT(c.id)')
                    ->getQuery()
                    ->getSingleScalarResult()
            ];

            // Récupérer les étudiants du pilote avec leurs candidatures
            $etudiants = $entityManager->getRepository(Etudiant::class)
                ->createQueryBuilder('e')
                ->join('e.promotion', 'p')
                ->leftJoin('e.candidatures', 'c')
                ->addSelect('c')
                ->where('p.pilote = :pilote')
                ->setParameter('pilote', $pilote)
                ->getQuery()
                ->getResult();

            // Récupérer les offres de stage du pilote avec leurs candidatures
            $offres = $entityManager->getRepository(OffreDeStage::class)
                ->createQueryBuilder('o')
                ->leftJoin('o.candidatures', 'c')
                ->addSelect('c')
                ->where('o.pilote = :pilote')
                ->setParameter('pilote', $pilote)
                ->getQuery()
                ->getResult();

            // Récupérer les entreprises liées aux offres du pilote
            $entreprises = $entityManager->getRepository(Entreprise::class)
                ->createQueryBuilder('e')
                ->join('e.offresDeStage', 'o')
                ->where('o.pilote = :pilote')
                ->setParameter('pilote', $pilote)
                ->getQuery()
                ->getResult();
            
            // Distribution des étudiants par promotion pour le pilote
            $distributionPromos = [];
            $promotions = $pilote->getPromotions();
            foreach ($promotions as $promotion) {
                $distributionPromos[$promotion->getNom()] = count($promotion->getEtudiants());
            }
            
            // Offres par mois pour le pilote
            $offresParMois = [];
            $moisActuel = new \DateTime();
            for ($i = 0; $i < 6; $i++) {
                $mois = clone $moisActuel;
                $mois->modify('-' . $i . ' months');
                $moisFormat = $mois->format('m/Y');
                
                $nombreOffres = $entityManager->getRepository(OffreDeStage::class)
                    ->createQueryBuilder('o')
                    ->select('COUNT(o.id)')
                    ->where('o.pilote = :pilote')
                    ->andWhere('o.date_debut_stage >= :debutMois')
                    ->andWhere('o.date_debut_stage < :finMois')
                    ->setParameter('pilote', $pilote)
                    ->setParameter('debutMois', $mois->format('Y-m-01'))
                    ->setParameter('finMois', $mois->format('Y-m-t'))
                    ->getQuery()
                    ->getSingleScalarResult();
                
                $offresParMois[] = [
                    'mois' => $moisFormat,
                    'nombre' => $nombreOffres
                ];
            }
            $offresParMois = array_reverse($offresParMois);

            // Récupérer les dernières candidatures pour les promotions du pilote
            $dernieresCandidatures = $entityManager->getRepository(Candidature::class)
                ->createQueryBuilder('c')
                ->join('c.etudiant', 'e')
                ->join('e.promotion', 'p')
                ->leftJoin('c.offreDeStage', 'o')
                ->addSelect('e', 'o')
                ->where('p.pilote = :pilote')
                ->setParameter('pilote', $pilote)
                ->orderBy('c.date_candidature', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();

            return $this->render('dashboard/pilote.html.twig', [
                'stats' => $stats,
                'etudiants' => $etudiants,
                'offres' => $offres,
                'entreprises' => $entreprises,
                'distributionPromos' => $distributionPromos,
                'offresParMois' => $offresParMois,
                'dernieresCandidatures' => $dernieresCandidatures
            ]);

        } elseif ($this->isGranted('ROLE_ETUDIANT')) {
            /** @var \App\Entity\Etudiant $etudiant */
            $etudiant = $this->getUser();
            
            // Statistiques pour l'étudiant
            $stats = [
                'candidatures' => $entityManager->getRepository(Candidature::class)
                    ->count(['etudiant' => $etudiant]),
                'entretiens' => $entityManager->getRepository(Candidature::class)
                    ->count(['etudiant' => $etudiant, 'statut' => 'Entretien']),
                'offresDisponibles' => $entityManager->getRepository(OffreDeStage::class)
                    ->count(['statut_offre' => 'Disponible']),
                'candidaturesAcceptees' => $entityManager->getRepository(Candidature::class)
                    ->count(['etudiant' => $etudiant, 'statut' => 'Acceptée']),
                'candidaturesEnAttente' => $entityManager->getRepository(Candidature::class)
                    ->count(['etudiant' => $etudiant, 'statut' => 'En attente']),
                'candidaturesRefusees' => $entityManager->getRepository(Candidature::class)
                    ->count(['etudiant' => $etudiant, 'statut' => 'Refusée'])
            ];

            // Récupérer les offres disponibles avec leurs entreprises
            $offres = $entityManager->getRepository(OffreDeStage::class)
                ->createQueryBuilder('o')
                ->leftJoin('o.entreprise', 'e')
                ->addSelect('e')
                ->where('o.statut_offre = :statut')
                ->setParameter('statut', 'Disponible')
                ->orderBy('o.datePublication', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();

            // Récupérer les candidatures de l'étudiant avec les offres et entreprises
            $candidatures = $entityManager->getRepository(Candidature::class)
                ->createQueryBuilder('c')
                ->leftJoin('c.offreDeStage', 'o')
                ->leftJoin('o.entreprise', 'e')
                ->addSelect('o', 'e')
                ->where('c.etudiant = :etudiant')
                ->setParameter('etudiant', $etudiant)
                ->orderBy('c.date_candidature', 'DESC')
                ->getQuery()
                ->getResult();

            // Récupérer les offres recommandées (basées sur la promotion de l'étudiant)
            $offresRecommandees = $entityManager->getRepository(OffreDeStage::class)
                ->createQueryBuilder('o')
                ->leftJoin('o.entreprise', 'e')
                ->addSelect('e')
                ->where('o.statut_offre = :statut')
                ->andWhere('o.promotion = :promotion')
                ->setParameter('statut', 'Disponible')
                ->setParameter('promotion', $etudiant->getPromotion())
                ->orderBy('o.datePublication', 'DESC')
                ->setMaxResults(3)
                ->getQuery()
                ->getResult();

            return $this->render('dashboard/etudiant.html.twig', [
                'stats' => $stats,
                'offres' => $offres,
                'candidatures' => $candidatures,
                'offresRecommandees' => $offresRecommandees
            ]);
        }

        return $this->redirectToRoute('app_login');
    }
} 
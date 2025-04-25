<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Form\EtudiantType;
use App\Repository\EtudiantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/etudiants')]
class EtudiantStageController extends AbstractController
{
    #[Route('/', name: 'app_etudiants_stage')]
    #[IsGranted('ROLE_PILOTE')]
    public function index(EtudiantRepository $etudiantRepository): Response
    {
        /** @var \App\Entity\PiloteDePromotion $pilote */
        $pilote = $this->getUser();
        
        // Récupérer les étudiants des promotions du pilote
        $etudiants = $etudiantRepository->createQueryBuilder('e')
            ->join('e.promotion', 'p')
            ->where('p.pilote = :pilote')
            ->setParameter('pilote', $pilote)
            ->orderBy('e.nom_etudiant', 'ASC')
            ->getQuery()
            ->getResult();

        // Statistiques
        $stats = [
            'total' => count($etudiants),
            'en_recherche' => count(array_filter($etudiants, fn($e) => $e->getStatut() === 'En recherche')),
            'en_stage' => count(array_filter($etudiants, fn($e) => $e->getStatut() === 'En stage'))
        ];

        return $this->render('etudiant_stage/index.html.twig', [
            'etudiants' => $etudiants,
            'stats' => $stats
        ]);
    }

    #[Route('/new', name: 'app_etudiant_stage_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PILOTE')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $etudiant = new Etudiant();
        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($etudiant);
            $entityManager->flush();

            $this->addFlash('success', 'L\'étudiant a été créé avec succès.');
            return $this->redirectToRoute('app_etudiants_stage');
        }

        return $this->render('etudiant_stage/new.html.twig', [
            'etudiant' => $etudiant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_etudiant_stage_show', methods: ['GET'])]
    #[IsGranted('ROLE_PILOTE')]
    public function show(Etudiant $etudiant): Response
    {
        // Vérifier que l'étudiant appartient à une promotion du pilote
        if ($etudiant->getPromotion()->getPilote() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cet étudiant.');
        }

        return $this->render('etudiant_stage/show.html.twig', [
            'etudiant' => $etudiant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_etudiant_stage_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PILOTE')]
    public function edit(Request $request, Etudiant $etudiant, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'étudiant appartient à une promotion du pilote
        if ($etudiant->getPromotion()->getPilote() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cet étudiant.');
        }

        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'étudiant a été modifié avec succès.');
            return $this->redirectToRoute('app_etudiants_stage');
        }

        return $this->render('etudiant_stage/edit.html.twig', [
            'etudiant' => $etudiant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_etudiant_stage_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PILOTE')]
    public function delete(Request $request, Etudiant $etudiant, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'étudiant appartient à une promotion du pilote
        if ($etudiant->getPromotion()->getPilote() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cet étudiant.');
        }

        if ($this->isCsrfTokenValid('delete'.$etudiant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($etudiant);
            $entityManager->flush();

            $this->addFlash('success', 'L\'étudiant a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_etudiants_stage');
    }
} 
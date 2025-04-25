<?php

namespace App\Controller;

use App\Entity\OffreDeStage;
use App\Entity\PiloteDePromotion;
use App\Form\OffreDeStageType;
use App\Repository\OffreDeStageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/offres')]
class OffreDeStageController extends AbstractController
{
    #[Route('/', name: 'app_offres_stage')]
    #[IsGranted('ROLE_PILOTE')]
    public function index(OffreDeStageRepository $offreRepository): Response
    {
        /** @var PiloteDePromotion $pilote */
        $pilote = $this->getUser();
        
        // Si c'est un admin, on montre toutes les offres
        if ($this->isGranted('ROLE_ADMIN')) {
            $offres = $offreRepository->findAll();
        } else {
            // Sinon on montre uniquement les offres du pilote
            $offres = $offreRepository->findBy(['pilote' => $pilote]);
        }
        
        // Statistiques
        $stats = [
            'total' => count($offres),
            'actives' => count(array_filter($offres, fn($o) => $o->getStatutOffre() === 'Disponible')),
            'inactives' => count(array_filter($offres, fn($o) => $o->getStatutOffre() !== 'Disponible'))
        ];

        return $this->render('offre_de_stage/index.html.twig', [
            'offres' => $offres,
            'stats' => $stats
        ]);
    }

    #[Route('/new', name: 'app_offre_stage_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PILOTE')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offre = new OffreDeStage();
        
        /** @var PiloteDePromotion $pilote */
        $pilote = $this->getUser();
        $offre->setPilote($pilote);
        
        $form = $this->createForm(OffreDeStageType::class, $offre, [
            'is_admin' => $this->isGranted('ROLE_ADMIN')
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si ce n'est pas un admin, on force le pilote à être l'utilisateur connecté
            if (!$this->isGranted('ROLE_ADMIN')) {
                $offre->setPilote($pilote);
            }
            
            $entityManager->persist($offre);
            $entityManager->flush();

            $this->addFlash('success', 'L\'offre a été créée avec succès.');
            return $this->redirectToRoute('app_offres_stage');
        }

        return $this->render('offre_de_stage/new.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offre_stage_show', methods: ['GET'])]
    #[IsGranted('ROLE_PILOTE')]
    public function show(OffreDeStage $offre): Response
    {
        // Vérifier que le pilote a accès à cette offre
        if (!$this->isGranted('ROLE_ADMIN') && $offre->getPilote() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette offre.');
        }

        return $this->render('offre_de_stage/show.html.twig', [
            'offre' => $offre
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offre_stage_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PILOTE')]
    public function edit(Request $request, OffreDeStage $offre, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que le pilote a accès à cette offre
        if (!$this->isGranted('ROLE_ADMIN') && $offre->getPilote() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette offre.');
        }

        $form = $this->createForm(OffreDeStageType::class, $offre, [
            'is_admin' => $this->isGranted('ROLE_ADMIN')
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si ce n'est pas un admin, on force le pilote à être l'utilisateur connecté
            if (!$this->isGranted('ROLE_ADMIN')) {
                $offre->setPilote($this->getUser());
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'L\'offre a été modifiée avec succès.');
            return $this->redirectToRoute('app_offres_stage');
        }

        return $this->render('offre_de_stage/edit.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offre_stage_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PILOTE')]
    public function delete(Request $request, OffreDeStage $offre, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que le pilote a accès à cette offre
        if (!$this->isGranted('ROLE_ADMIN') && $offre->getPilote() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette offre.');
        }

        if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($offre);
            $entityManager->flush();

            $this->addFlash('success', 'L\'offre a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_offres_stage');
    }
} 
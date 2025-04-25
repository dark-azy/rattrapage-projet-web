<?php

namespace App\Controller;

use App\Entity\OffreDeStage;
use App\Form\OffreDeStageType;
use App\Repository\OffreDeStageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/admin/offres')]
class OffreController extends AbstractController
{
    #[Route('/', name: 'app_offres', methods: ['GET'])]
    public function index(OffreDeStageRepository $offreRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_PILOTE')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
        }
        
        $offres = $offreRepository->findAll();
        
        // Statistiques
        $stats = [
            'total' => count($offres),
            'actives' => count(array_filter($offres, fn($o) => $o->getStatutOffre() === 'Disponible')),
            'inactives' => count(array_filter($offres, fn($o) => $o->getStatutOffre() !== 'Disponible'))
        ];

        return $this->render('offre/index.html.twig', [
            'offres' => $offres,
            'stats' => $stats
        ]);
    }

    #[Route('/new', name: 'app_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offre = new OffreDeStage();
        $form = $this->createForm(OffreDeStageType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offre);
            $entityManager->flush();

            $this->addFlash('success', 'L\'offre a été créée avec succès.');
            return $this->redirectToRoute('app_offres');
        }

        return $this->render('offre/new.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offre_show', methods: ['GET'])]
    public function show(OffreDeStage $offre): Response
    {
        return $this->render('offre/show.html.twig', [
            'offre' => $offre
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OffreDeStage $offre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OffreDeStageType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'offre a été modifiée avec succès.');
            return $this->redirectToRoute('app_offres');
        }

        return $this->render('offre/edit.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offre_delete', methods: ['POST'])]
    public function delete(Request $request, OffreDeStage $offre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->request->get('_token'))) {
            // Check if the offer has any candidatures
            if ($offre->getCandidatures()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer cette offre car elle a des candidatures associées. Veuillez d\'abord supprimer les candidatures.');
                return $this->redirectToRoute('app_offre_show', ['id' => $offre->getId()]);
            }
            
            // Check if the offer has any wishlist entries
            if ($offre->getWishlists()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer cette offre car elle est dans des wishlists. Veuillez d\'abord supprimer les entrées de wishlist.');
                return $this->redirectToRoute('app_offre_show', ['id' => $offre->getId()]);
            }
            
            $entityManager->remove($offre);
            $entityManager->flush();

            $this->addFlash('success', 'L\'offre a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_offres');
    }
} 
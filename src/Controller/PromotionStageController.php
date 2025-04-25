<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/promotions')]
class PromotionStageController extends AbstractController
{
    #[Route('/', name: 'app_promotions_stage')]
    #[IsGranted('ROLE_PILOTE')]
    public function index(PromotionRepository $promotionRepository): Response
    {
        /** @var \App\Entity\PiloteDePromotion $pilote */
        $pilote = $this->getUser();
        
        // Récupérer les promotions du pilote
        $promotions = $promotionRepository->findBy(['pilote' => $pilote]);

        // Statistiques
        $stats = [
            'total' => count($promotions),
            'total_etudiants' => array_sum(array_map(fn($p) => count($p->getEtudiants()), $promotions))
        ];

        return $this->render('promotion_stage/index.html.twig', [
            'promotions' => $promotions,
            'stats' => $stats
        ]);
    }

    #[Route('/new', name: 'app_promotion_stage_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PILOTE')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Assigner automatiquement le pilote actuel
            $promotion->setPilote($this->getUser());
            
            $entityManager->persist($promotion);
            $entityManager->flush();

            $this->addFlash('success', 'La promotion a été créée avec succès.');
            return $this->redirectToRoute('app_promotions_stage');
        }

        return $this->render('promotion_stage/new.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_promotion_stage_show', methods: ['GET'])]
    #[IsGranted('ROLE_PILOTE')]
    public function show(Promotion $promotion): Response
    {
        // Vérifier que la promotion appartient au pilote
        if ($promotion->getPilote() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette promotion.');
        }

        return $this->render('promotion_stage/show.html.twig', [
            'promotion' => $promotion,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_promotion_stage_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PILOTE')]
    public function edit(Request $request, Promotion $promotion, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que la promotion appartient au pilote
        if ($promotion->getPilote() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette promotion.');
        }

        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La promotion a été modifiée avec succès.');
            return $this->redirectToRoute('app_promotions_stage');
        }

        return $this->render('promotion_stage/edit.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_promotion_stage_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PILOTE')]
    public function delete(Request $request, Promotion $promotion, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que la promotion appartient au pilote
        if ($promotion->getPilote() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette promotion.');
        }

        if ($this->isCsrfTokenValid('delete'.$promotion->getId(), $request->request->get('_token'))) {
            $entityManager->remove($promotion);
            $entityManager->flush();

            $this->addFlash('success', 'La promotion a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_promotions_stage');
    }
} 
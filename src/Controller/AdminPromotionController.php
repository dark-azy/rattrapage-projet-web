<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Form\AdminPromotionType;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/promotions')]
#[IsGranted('ROLE_ADMIN')]
class AdminPromotionController extends AbstractController
{
    #[Route('/', name: 'app_admin_promotions')]
    public function index(PromotionRepository $promotionRepository): Response
    {
        $promotions = $promotionRepository->findAll();

        // Statistiques
        $stats = [
            'total' => count($promotions),
            'total_etudiants' => array_sum(array_map(fn($p) => count($p->getEtudiants()), $promotions))
        ];

        return $this->render('admin/promotion/index.html.twig', [
            'promotions' => $promotions,
            'stats' => $stats
        ]);
    }

    #[Route('/new', name: 'app_admin_promotion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $promotion = new Promotion();
        $form = $this->createForm(AdminPromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($promotion);
            $entityManager->flush();

            $this->addFlash('success', 'La promotion a été créée avec succès.');
            return $this->redirectToRoute('app_admin_promotions');
        }

        return $this->render('admin/promotion/new.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_promotion_show', methods: ['GET'])]
    public function show(Promotion $promotion): Response
    {
        return $this->render('admin/promotion/show.html.twig', [
            'promotion' => $promotion,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_promotion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Promotion $promotion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminPromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La promotion a été modifiée avec succès.');
            return $this->redirectToRoute('app_admin_promotions');
        }

        return $this->render('admin/promotion/edit.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_promotion_delete', methods: ['POST'])]
    public function delete(Request $request, Promotion $promotion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promotion->getId(), $request->request->get('_token'))) {
            $entityManager->remove($promotion);
            $entityManager->flush();

            $this->addFlash('success', 'La promotion a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_admin_promotions');
    }
} 
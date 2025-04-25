<?php

namespace App\Controller;

use App\Entity\PiloteDePromotion;
use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/pilote')]
#[IsGranted('ROLE_ADMIN')]
class PilotePromotionController extends AbstractController
{
    #[Route('/{id}/promotions', name: 'app_pilote_promotions', methods: ['GET', 'POST'])]
    public function editPromotions(Request $request, PiloteDePromotion $pilote, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les promotions actuelles du pilote
        $currentPromotions = $pilote->getPromotions()->toArray();

        $form = $this->createFormBuilder($pilote)
            ->add('promotions', EntityType::class, [
                'class' => Promotion::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Promotions',
                'attr' => [
                    'class' => 'form-select select2',
                    'data-placeholder' => 'Sélectionnez les promotions'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les nouvelles promotions sélectionnées
            $newPromotions = $pilote->getPromotions()->toArray();

            // Retirer le pilote des promotions qui ne sont plus sélectionnées
            foreach ($currentPromotions as $promotion) {
                if (!in_array($promotion, $newPromotions)) {
                    $promotion->setPilote(null);
                }
            }

            // Ajouter le pilote aux nouvelles promotions
            foreach ($newPromotions as $promotion) {
                $promotion->setPilote($pilote);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Les promotions du pilote ont été mises à jour avec succès.');
            return $this->redirectToRoute('app_users');
        }

        return $this->render('user/edit_pilote_promotions.html.twig', [
            'pilote' => $pilote,
            'form' => $form,
        ]);
    }
} 
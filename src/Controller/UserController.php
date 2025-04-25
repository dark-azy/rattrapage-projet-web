<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Etudiant;
use App\Entity\PiloteDePromotion;
use App\Entity\Administrateur;
use App\Entity\Promotion;
use App\Form\EtudiantType;
use App\Form\PiloteType;
use App\Form\UserType;
use App\Form\AdminType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\AdministrateurType;

#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'app_users')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/etudiant/new', name: 'app_etudiant_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function newEtudiant(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $etudiant = new Etudiant();
        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $etudiant,
                $form->get('password')->getData()
            );
            $etudiant->setPassword($hashedPassword);

            $entityManager->persist($etudiant);
            $entityManager->flush();

            $this->addFlash('success', 'Étudiant créé avec succès.');
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('user/new_etudiant.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pilote/new', name: 'app_pilote_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function newPilote(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $pilote = new PiloteDePromotion();
        $form = $this->createForm(PiloteType::class, $pilote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $pilote,
                $form->get('password')->getData()
            );
            $pilote->setPassword($hashedPassword);

            // Récupérer la promotion par défaut
            $defaultPromotion = $entityManager->getRepository(Promotion::class)
                ->findOneBy(['nom' => 'Promotion par défaut']);

            if ($defaultPromotion) {
                $pilote->addPromotion($defaultPromotion);
                $defaultPromotion->setPilote($pilote);
            }

            $entityManager->persist($pilote);
            $entityManager->flush();

            $this->addFlash('success', 'Pilote créé avec succès.');
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('user/new_pilote.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route('/admin/new', name: 'app_admin_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function newAdmin(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $admin = new Administrateur();
        $form = $this->createForm(AdministrateurType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $admin,
                $form->get('password')->getData()
            );
            $admin->setPassword($hashedPassword);

            $entityManager->persist($admin);
            $entityManager->flush();

            $this->addFlash('success', 'Administrateur créé avec succès.');
            return $this->redirectToRoute('app_users');
        }

        return $this->render('user/new_admin.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/etudiant/delete/{id}', name: 'app_etudiant_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteEtudiant(Request $request, Etudiant $etudiant, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_etudiant_' . $etudiant->getId(), $request->request->get('_token'))) {
            $em->remove($etudiant);
            $em->flush();
            $this->addFlash('success', 'Étudiant supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_users');
    }


    #[Route('/pilote/delete/{id}', name: 'app_pilote_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deletePilote(Request $request, PiloteDePromotion $pilote, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_pilote_' . $pilote->getId(), $request->request->get('_token'))) {
            $entityManager->remove($pilote);
            $entityManager->flush();
            $this->addFlash('success', 'Le pilote a été supprimé.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_users');
    }

    #[Route('/admin/delete/{id}', name: 'app_admin_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAdministrateur(Request $request, Administrateur $admin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_admin_' . $admin->getId(), $request->request->get('_token'))) {
            $entityManager->remove($admin);
            $entityManager->flush();

            $this->addFlash('success', 'Administrateur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_users');
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a été modifié avec succès.');
            return $this->redirectToRoute('app_users');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/etudiant/{id}/promotion', name: 'app_etudiant_change_promotion', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changePromotion(Request $request, Etudiant $etudiant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($etudiant)
            ->add('promotion', EntityType::class, [
                'class' => Promotion::class,
                'choice_label' => 'nom',
                'label' => 'Promotion',
                'attr' => ['class' => 'form-control']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La promotion de l\'étudiant a été modifiée avec succès.');
            return $this->redirectToRoute('app_users');
        }

        return $this->render('user/change_promotion.html.twig', [
            'etudiant' => $etudiant,
            'form' => $form
        ]);
    }
}
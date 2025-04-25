<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Adresse;
use App\Form\EntrepriseType;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\DTO\EntrepriseDTO;

#[IsGranted('ROLE_PILOTE')]
#[Route('/entreprises')]
class EntrepriseStageController extends AbstractController
{
    #[Route('/', name: 'app_entreprises_stage')]
    public function index(EntrepriseRepository $entrepriseRepository): Response
    {
        $entreprises = $entrepriseRepository->findAll();
        
        // Statistiques
        $stats = [
            'total' => count($entreprises),
            'actives' => count(array_filter($entreprises, fn($e) => $e->isActive())),
            'inactives' => count(array_filter($entreprises, fn($e) => !$e->isActive()))
        ];

        return $this->render('entreprise_stage/index.html.twig', [
            'entreprises' => $entreprises,
            'stats' => $stats
        ]);
    }

    #[Route('/new', name: 'app_entreprise_stage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dto = new EntrepriseDTO();
        $form = $this->createForm(EntrepriseType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créer une nouvelle adresse
            $adresse = new Adresse();
            $adresse->setRue($dto->getRue());
            $adresse->setCodePostal($dto->getCodePostal());
            $adresse->setVille($dto->getVille());
            $adresse->setPays($dto->getPays());

            // Persister l'adresse
            $entityManager->persist($adresse);
            $entityManager->flush();

            // Créer une nouvelle entreprise
            $entreprise = new Entreprise();
            $entreprise->setNom($dto->getNom());
            $entreprise->setSecteur($dto->getSecteur());
            $entreprise->setAdresse($adresse);
            $entreprise->setActive($dto->isActive());
            $entreprise->setTelephone($dto->getTelephone());
            $entreprise->setEmail($dto->getEmail());
            $entreprise->setDescription($dto->getDescription());

            // Persister l'entreprise
            $entityManager->persist($entreprise);
            $entityManager->flush();

            $this->addFlash('success', 'L\'entreprise a été créée avec succès.');
            return $this->redirectToRoute('app_entreprises_stage');
        }

        return $this->render('entreprise_stage/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_entreprise_stage_show')]
    public function show(Entreprise $entreprise): Response
    {
        return $this->render('entreprise_stage/show.html.twig', [
            'entreprise' => $entreprise
        ]);
    }

    #[Route('/{id}/edit', name: 'app_entreprise_stage_edit')]
    public function edit(Request $request, Entreprise $entreprise, EntityManagerInterface $entityManager): Response
    {
        $dto = new EntrepriseDTO();
        $dto->setNom($entreprise->getNom());
        $dto->setSecteur($entreprise->getSecteur());
        $dto->setActive($entreprise->isActive());
        $dto->setTelephone($entreprise->getTelephone());
        $dto->setEmail($entreprise->getEmail());
        $dto->setDescription($entreprise->getDescription());
        
        // Récupérer les données de l'adresse
        $adresse = $entreprise->getAdresse();
        if ($adresse) {
            $dto->setRue($adresse->getRue());
            $dto->setCodePostal($adresse->getCodePostal());
            $dto->setVille($adresse->getVille());
            $dto->setPays($adresse->getPays());
        }

        $form = $this->createForm(EntrepriseType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour l'entreprise
            $entreprise->setNom($dto->getNom());
            $entreprise->setSecteur($dto->getSecteur());
            $entreprise->setActive($dto->isActive());
            $entreprise->setTelephone($dto->getTelephone());
            $entreprise->setEmail($dto->getEmail());
            $entreprise->setDescription($dto->getDescription());

            // Mettre à jour l'adresse
            if (!$adresse) {
                $adresse = new Adresse();
                $entreprise->setAdresse($adresse);
            }
            
            $adresse->setRue($dto->getRue());
            $adresse->setCodePostal($dto->getCodePostal());
            $adresse->setVille($dto->getVille());
            $adresse->setPays($dto->getPays());

            $entityManager->persist($adresse);
            $entityManager->flush();

            $this->addFlash('success', 'L\'entreprise a été modifiée avec succès.');
            return $this->redirectToRoute('app_entreprises_stage');
        }

        return $this->render('entreprise_stage/edit.html.twig', [
            'form' => $form->createView(),
            'entreprise' => $entreprise
        ]);
    }

    #[Route('/{id}/delete', name: 'app_entreprise_stage_delete', methods: ['POST'])]
    public function delete(Request $request, Entreprise $entreprise, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$entreprise->getId(), $request->request->get('_token'))) {
            $entityManager->remove($entreprise);
            $entityManager->flush();
            $this->addFlash('success', 'L\'entreprise a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_entreprises_stage');
    }
} 
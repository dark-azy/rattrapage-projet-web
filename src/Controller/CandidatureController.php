<?php
// src/Controller/CandidatureController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Candidature;
use App\Entity\OffreDeStage;
use App\Entity\User;
use App\Entity\Etudiant;
use App\Entity\PiloteDePromotion;

class CandidatureController extends AbstractController
{
    #[Route('/candidatures', name: 'app_candidatures')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Si c'est un étudiant
        if ($user instanceof Etudiant) {
            $candidatures = $entityManager->getRepository(Candidature::class)
                ->findBy(['etudiant' => $user]);

            return $this->render('candidature/index.html.twig', [
                'candidatures' => $candidatures,
                'activeFilter' => 'pending'
            ]);
        }
        
        // Si c'est un pilote
        if ($user instanceof PiloteDePromotion) {
            // Récupérer les étudiants des promotions du pilote avec leurs candidatures
            $etudiants = $entityManager->getRepository(Etudiant::class)
                ->createQueryBuilder('e')
                ->join('e.promotion', 'p')
                ->leftJoin('e.candidatures', 'c')
                ->where('p.pilote = :pilote')
                ->setParameter('pilote', $user)
                ->getQuery()
                ->getResult();

            return $this->render('candidature/pilote_index.html.twig', [
                'etudiants' => $etudiants
            ]);
        }

        // Si ce n'est ni un étudiant ni un pilote
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/candidature/{id}/update-status', name: 'app_candidature_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user || !($user instanceof PiloteDePromotion)) {
            $this->addFlash('error', 'Vous devez être connecté en tant que pilote pour effectuer cette action.');
            return $this->redirectToRoute('app_candidatures');
        }

        // Vérifier si le pilote est responsable de la promotion de l'étudiant
        if ($candidature->getEtudiant()->getPromotion()->getPilote() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette candidature.');
            return $this->redirectToRoute('app_candidatures');
        }

        $newStatus = $request->request->get('status');
        if (in_array($newStatus, ['Acceptée', 'Refusée', 'En attente'])) {
            $candidature->setStatut($newStatus);
            if ($newStatus !== 'En attente') {
                $candidature->setDateReponse(new \DateTime());
            }
            $entityManager->flush();
            
            $this->addFlash('success', 'Le statut de la candidature a été mis à jour.');
        } else {
            $this->addFlash('error', 'Statut invalide.');
        }

        return $this->redirectToRoute('app_candidatures');
    }

    #[Route('/candidature/new/{id}', name: 'app_candidature_new', methods: ['POST'])]
    public function new(Request $request, OffreDeStage $offre, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user || !($user instanceof Etudiant)) {
            return new JsonResponse(['error' => 'Vous devez être connecté en tant qu\'étudiant pour postuler'], Response::HTTP_FORBIDDEN);
        }

        // Vérifier si une candidature existe déjà
        $existingCandidature = $entityManager->getRepository(Candidature::class)->findOneBy([
            'etudiant' => $user,
            'offreDeStage' => $offre
        ]);

        if ($existingCandidature) {
            return new JsonResponse(['error' => 'Vous avez déjà postulé à cette offre'], Response::HTTP_BAD_REQUEST);
        }

        // Créer une nouvelle candidature
        $candidature = new Candidature();
        $candidature->setEtudiant($user);
        $candidature->setOffreDeStage($offre);
        // Le statut "En attente" est défini par défaut dans le constructeur de Candidature

        $entityManager->persist($candidature);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Candidature enregistrée avec succès'
        ]);
    }

    #[Route('/candidature/{id}', name: 'app_candidature_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidature->getId(), $request->request->get('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();

            $this->addFlash('success', 'La candidature a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_offre_show', ['id' => $candidature->getOffreDeStage()->getId()]);
    }
}

?>
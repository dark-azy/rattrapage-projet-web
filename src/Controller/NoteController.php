<?php

namespace App\Controller;

use App\Entity\Note;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Psr\Log\LoggerInterface;

class NoteController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    #[Route('/note/create', name: 'app_note_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        NoteRepository $noteRepository,
        Security $security
    ): Response {
        try {
            // Vérifier si l'utilisateur est connecté
            $user = $security->getUser();
            $this->logger->info('User trying to rate: ' . ($user ? $user->getUserIdentifier() : 'not logged in'));

            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Vous devez être connecté pour noter une entreprise'
                ], Response::HTTP_FORBIDDEN);
            }

            // Vérifier si l'utilisateur est un étudiant
            if (!($user instanceof \App\Entity\Etudiant)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Seuls les étudiants peuvent noter les entreprises'
                ], Response::HTTP_FORBIDDEN);
            }

            $etudiant = $user;
            $this->logger->info('Student found: ' . ($etudiant ? 'yes' : 'no'));

            $nomEntreprise = $request->request->get('nom_entreprise');
            $valeur = $request->request->get('valeur');

            $this->logger->info('Rating attempt - Entreprise: ' . $nomEntreprise . ', Valeur: ' . $valeur);

            // Vérifier si les données requises sont présentes
            if (!$nomEntreprise || !$valeur) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données manquantes'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier si l'étudiant a déjà noté cette entreprise
            $existingNote = $noteRepository->findNoteByEtudiantAndEntreprise(
                $etudiant->getNomEtudiant(),
                $nomEntreprise
            );

            if ($existingNote) {
                return $this->json([
                    'success' => false,
                    'message' => 'Vous avez déjà noté cette entreprise'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Créer une nouvelle note
            $note = new Note();
            $note->setNomEtudiant($etudiant->getNomEtudiant());
            $note->setNomEntreprise($nomEntreprise);
            $note->setValeur($valeur);

            $entityManager->persist($note);
            $entityManager->flush();

            $this->logger->info('Note successfully created for student ' . $etudiant->getNomEtudiant() . ' and company ' . $nomEntreprise);

            return $this->json([
                'success' => true,
                'message' => 'Votre notation a été prise en compte'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error creating note: ' . $e->getMessage());
            $this->logger->error('Stack trace: ' . $e->getTraceAsString());

            return $this->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement de votre note: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 
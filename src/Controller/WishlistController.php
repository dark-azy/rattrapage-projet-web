<?php
// src/Controller/WishlistController.php
namespace App\Controller;

use App\Entity\Wishlist;
use App\Entity\Etudiant;
use App\Entity\OffreDeStage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wishlist')]
#[IsGranted('ROLE_ETUDIANT')]
class WishlistController extends AbstractController
{
    #[Route('/', name: 'app_wishlist')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /** @var Etudiant $etudiant */
        $etudiant = $this->getUser();
        
        // Récupérer toutes les offres de la wishlist de l'étudiant
        $wishlists = $entityManager->getRepository(Wishlist::class)->findByEtudiant($etudiant);
        $offres = array_map(fn($w) => $w->getOffreDeStage(), $wishlists);

        return $this->render('wishlist/index.html.twig', [
            'offres' => $offres
        ]);
    }

    #[Route('/add/{id}', name: 'app_wishlist_add', methods: ['POST'])]
    public function add(OffreDeStage $offre, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var Etudiant $etudiant */
        $etudiant = $this->getUser();

        // Vérifier si l'offre n'est pas déjà dans la wishlist
        $existingWishlist = $entityManager->getRepository(Wishlist::class)
            ->findOneByEtudiantAndOffre($etudiant, $offre);

        if ($existingWishlist) {
            return new JsonResponse(['success' => false, 'error' => 'Cette offre est déjà dans votre wishlist']);
        }

        // Créer une nouvelle entrée dans la wishlist
        $wishlist = new Wishlist();
        $wishlist->setEtudiant($etudiant);
        $wishlist->setOffreDeStage($offre);

        $entityManager->persist($wishlist);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/remove/{id}', name: 'app_wishlist_remove', methods: ['POST'])]
    public function remove(OffreDeStage $offre, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var Etudiant $etudiant */
        $etudiant = $this->getUser();

        $wishlist = $entityManager->getRepository(Wishlist::class)
            ->findOneByEtudiantAndOffre($etudiant, $offre);

        if (!$wishlist) {
            return new JsonResponse(['success' => false, 'error' => 'Cette offre n\'est pas dans votre wishlist']);
        }

        $entityManager->remove($wishlist);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/check/{id}', name: 'app_wishlist_check', methods: ['GET'])]
    public function check(OffreDeStage $offre, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var Etudiant $etudiant */
        $etudiant = $this->getUser();

        $wishlist = $entityManager->getRepository(Wishlist::class)
            ->findOneByEtudiantAndOffre($etudiant, $offre);

        return new JsonResponse(['inWishlist' => $wishlist !== null]);
    }
}

?>
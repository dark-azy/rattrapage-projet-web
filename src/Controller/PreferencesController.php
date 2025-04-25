<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class PreferencesController extends AbstractController
{
    #[Route('/preferences', name: 'app_preferences')]
    public function index(): Response
    {
        $user = $this->getUser();
        $role = 'ROLE_USER';

        if ($this->isGranted('ROLE_ADMIN')) {
            $role = 'ROLE_ADMIN';
        } elseif ($this->isGranted('ROLE_PILOTE')) {
            $role = 'ROLE_PILOTE';
        } elseif ($this->isGranted('ROLE_ETUDIANT')) {
            $role = 'ROLE_ETUDIANT';
        }

        return $this->render('preferences/index.html.twig', [
            'role' => $role,
        ]);
    }
} 
<?php
// src/Controller/HomeController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class HomeController extends AbstractController
{
#[Route('/', name: 'app_home')]
public function index(Security $security): Response
{
// Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
if (!$this->getUser()) {
return $this->redirectToRoute('app_login');
}

// Texte "À propos de nous"
$aboutText = "Interned est une plateforme dédiée à la recherche de stages pour les étudiants. Notre mission est de connecter les étudiants avec les entreprises qui proposent des stages correspondant à leurs compétences et aspirations. Nous facilitons le processus de recherche et de candidature pour permettre aux étudiants de trouver rapidement des opportunités pertinentes.";

// Exemples d'offres de stage
$offers = [
[
'title' => 'Stage en développement web',
'description' => 'Développement de fonctionnalités web pour une application de commerce électronique',
'icon' => 'code'
],
[
'title' => 'Stage en marketing digital',
'description' => 'Participation à la stratégie de marketing digital et gestion des réseaux sociaux',
'icon' => 'chart-line'
],
[
'title' => 'Stage en design UX/UI',
'description' => 'Conception d\'interfaces utilisateur pour applications mobiles',
'icon' => 'paint-brush'
]
];

return $this->render('home/index.html.twig', [
'about_text' => $aboutText,
'offers' => $offers
]);
}
}
?>
<?php
// src/Controller/InfoLegalesController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InfoLegalesController extends AbstractController
{
    #[Route('/InfosLegales', name: 'app_InfosLegales')]
    public function index(): Response
    {
        return $this->render('info/legal.html.twig');
    }
    #[Route('/CGU', name: 'app_CGU')]
    public function CGU(): Response
    {
        return $this->render('info/cgu.html.twig');
    }
    #[Route('/PDC', name: 'app_InfosConfidential')]
    public function pdc(): Response
    {
        return $this->render('info/confidentialite.html.twig');
    }

    #[Route('/Help', name: 'app_Help')]
    public function help(): Response
    {
        return $this->render('info/aide.html.twig');
    }
}
?>
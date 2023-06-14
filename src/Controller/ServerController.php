<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class ServerController extends AbstractController
{
    #[Route('/', name: 'app_server')]
    public function index(): Response
    {
        return $this->render('server/index.html.twig');
    }
}

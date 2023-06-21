<?php

namespace App\Controller\App;

use App\Form\ServerFilterFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class ServerController extends AbstractController
{
    #[Route('/', name: 'app_servers', methods: ['GET'])]
    public function listServers(Request $request): Response
    {
        $form = $this->createForm(ServerFilterFormType::class);

        return $this->render('app/server/list.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

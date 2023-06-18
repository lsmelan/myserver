<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/server')]
class ServerController extends AbstractController
{
    #[Route('/upload-list', name: 'app_upload_list')]
    public function uploadList(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('file', FileType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            // Validate file extension
            $allowedExtensions = ['xlsx', 'csv'];
            $extension = $file->getClientOriginalExtension();

            if (!in_array($extension, $allowedExtensions)) {
                $this->addFlash('error', 'Only XLSX and CSV files are allowed.');

                return $this->redirectToRoute('app_upload_list');
            }

            // Spawn a background process to handle the file processing
            $process = new Process(['php', '../bin/console', 'app:process-file', $file->getRealPath()]);
            $process->disableOutput();
            $process->start();
            $process->wait();

            // Redirect to a success page or perform further actions
            $this->addFlash('success', 'File processed successfully.');

            return $this->redirectToRoute('app_index');
        }

        return $this->render('admin/server/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

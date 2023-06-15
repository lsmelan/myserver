<?php

namespace App\Controller\admin;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/server')]
class ServerController extends AbstractController
{
    #[Route('/add', name: 'app_upload_server')]
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
                return $this->redirectToRoute('app_upload_server');
            }

            // Process the file in chunks
            try {
                foreach ($this->getRows($file->getRealPath(), 200) as $row) {
                    var_dump($row);
                }
            } catch (FileException $e) {
                $this->addFlash('error', 'An error occurred while processing the file.');
                return $this->redirectToRoute('app_upload_server');
            }

            // Redirect to a success page or perform further actions
            return $this->redirectToRoute('upload_success');
        }

        return $this->render('admin/server/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function getRows(string $filePath, int $chunkSize = 1000): \Generator
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        for ($row = 1; $row <= $highestRow; $row += $chunkSize) {
            $endRow = $row + $chunkSize - 1;
            if ($endRow > $highestRow) {
                $endRow = $highestRow;
            }

            $range = 'A' . $row . ':' . $highestColumn . $endRow;
            yield $worksheet->rangeToArray($range, returnCellRef: true);
        }
    }
}

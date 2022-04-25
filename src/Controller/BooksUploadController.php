<?php

namespace App\Controller;

use App\Form\BooksUploadType;
use App\Service\UploadCsvContent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BooksUploadController extends AbstractController
{
    #[Route('/', name: 'app_books_upload')]
    public function uploadFile(
        Request $request,
        UploadCsvContent $uploadCsvContent): Response
    {
        $form = $this->createForm(BooksUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // save file
            $uploadedFile = $form['file_upload']->getData();

            if ($uploadedFile) {
                $destination = $this->getParameter('kernel.project_dir').'/public/uploads';
                $fileName = uniqid() . '-' . $uploadedFile->getClientOriginalName();

                $uploadedFile->move(
                    $destination,
                    $fileName
                );
                $this->addFlash('success', "File uploaded successfuly!");
               

                $file = $destination . '/' . $fileName;

                $filesystem = new Filesystem();
                // Import file content to db.
                if($filesystem->exists($destination) && $filesystem->exists([$file])) {
                    if (($handle = fopen($file, "r")) !== FALSE) {
                        $messageArray = [];
                        $lastImportedBookId = $uploadCsvContent->contentToDb($handle, $messageArray);

                        if (!empty($messageArray['skipped_id'])) {
                            $bookIds = implode(", ", $messageArray['skipped_id']);
                            $this->addFlash('error', 'Skiped import of existing book with id: ' . $bookIds);
                        }

                        if (!empty($messageArray['skipped_name'])) {
                            $bookNames = implode(", ", $messageArray['skipped_name']);
                            $this->addFlash('error', 'Empty id, skiped import of book with name: ' . $bookNames);
                        }

                        if (!empty($lastImportedBookId)) {
                            $this->addFlash('success', 'Last imported book with id: ' . $lastImportedBookId);
                        }

                        fclose($handle);  
                        $this->addFlash('success', "CSV data successfully saved to db!");

                    }
                
                    return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
                }
                
            } else {
                $this->addFlash('error', "File not uploaded successfuly!");
            }
        }

        return $this->render('books_upload/books_upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

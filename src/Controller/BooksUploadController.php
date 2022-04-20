<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BooksUploadType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BooksUploadController extends AbstractController
{
    #[Route('/', name: 'app_books_upload')]
    public function uploadFile(Request $request, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
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
                        $i = 0;
                        while (($getData = fgetcsv($handle, 32000, ",")) !== FALSE) {
                            $i++;
                            $bookId = (int)$getData[0];

                            if ($bookId < 1403661) continue;
              
                            $existingBook = FALSE;
                            if (!empty($bookId)) {
                              $existingBook = $bookRepository->findBy(['id' => $bookId]);
              
                              if (!empty($existingBook)) $this->addFlash('error', 'Skiped import of existing book with id: ' . $bookId);
                            }
              
                            if (!$existingBook && !empty($bookId)) {
                            $book = new Book;
                            $book->setId($getData[0]);
                            $book->setSeries($getData[1]);
                            $book->setNumber($getData[2]);
                            $book->setName($getData[3]);
                            $book->setType($getData[4]);
                            $book->setPublisher($getData[5]);
                            $book->setAuthor($getData[6]);
                            $book->setPrice($getData[7]);
                            if (!empty($getData[8])) {
                                $book->setReleaseDate($getData[8]);
                            }

                            $entityManager->persist($book);
                            } else if (empty($bookId)) {
                                $this->addFlash('error', 'Empty id, skiped import of book with name: ' . $getData[3]);
                            }
            
                            if ($i%100) {
                                $entityManager->flush();
                                $entityManager->clear();
                            }
                        }
                    }
                
                    $this->addFlash('success', 'Last imported book with id: ' . $bookId);

                    fclose($handle);  
                    $this->addFlash('success', "CSV data successfully saved to db!");

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

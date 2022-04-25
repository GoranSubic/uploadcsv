<?php

namespace App\Controller;

use App\Entity\BookRelational;
use App\Form\BookType;
use App\Repository\BookRelationalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/book')]
class BookController extends AbstractController
{
    #[Route('/', name: 'app_book_index', methods: ['GET'])]
    public function index(BookRelationalRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findBy([], ['id' => 'DESC'], 10, 0)
        ]);
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BookRelationalRepository $bookRepository): Response
    {
        $book = new BookRelational();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookRepository->add($book);
            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{bookId}', name: 'app_book_show', methods: ['GET'])]
    public function show(BookRelational $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{bookId}/edit', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BookRelational $book, BookRelationalRepository $bookRepository): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookRepository->add($book);
            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{bookId}', name: 'app_book_delete', methods: ['POST'])]
    public function delete(Request $request, BookRelational $book, BookRelationalRepository $bookRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getBookId(), $request->request->get('_token'))) {
            $bookRepository->remove($book);
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Repository\Elastic\LibraryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookUnit extends AbstractController
{
    public function __construct(
        private readonly LibraryRepository  $libraryRepository
    )
    {
    }

    #[Route(path: '/books/{id}', name: 'book')]
    public function __invoke(Request $request, string $id): Response
    {
        if (
            null !== ($username = $request->get('username'))
            && null !== ($note = $request->get('note'))
        ) {
            $this->libraryRepository->addBookRating($id, $username, $note);
        }

        $filters = $this->libraryRepository->getFilters();
        $book = $this->libraryRepository->findOneBook($id);

        return $this->render(
            view: 'unit.html.twig',
            parameters: [
                'queryString' => '',
                'suggestions' => [],
                'filters' => $filters,
                'book' => $book,
            ],
        );
    }
}

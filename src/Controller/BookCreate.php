<?php

namespace App\Controller;

use App\Model\Elastic\Book;
use App\Repository\Elastic\LibraryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookCreate extends AbstractController
{
    public function __construct(
        private readonly LibraryRepository  $libraryRepository
    )
    {
    }

    #[Route(path: '/books/create', name: 'book')]
    public function __invoke(Request $request, string $id): Response
    {
        if (
            null !== ($title = $request->get('title'))
            && null !== ($description = $request->get('description'))
            && null !== ($category = $request->get('category'))
            && null !== ($subCategory = $request->get('subCategory'))
            && null !== ($edition = $request->get('edition'))
            && null !== ($price = $request->get('price'))
        ) {
            $this->libraryRepository->addBook(Book::create(
                id: null,
                source: [
                    'title' => $title,
                    'description' => $description,
                    'category' => $category,
                    'subCategory' => $subCategory,
                    'edition' => $edition,
                    'price' => $price,
                ]
            ));
        }

        $filters = $this->libraryRepository->getFilters();

        return $this->render(
            view: 'create.html.twig',
            parameters: [
                'queryString' => '',
                'suggestions' => [],
                'filters' => $filters,
            ],
        );
    }
}

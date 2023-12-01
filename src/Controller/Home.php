<?php

namespace App\Controller;

use App\Repository\Elastic\LibraryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Home extends AbstractController
{
    public function __construct(
        private readonly LibraryRepository  $libraryRepository
    )
    {
    }

    #[Route(path: '/', name: 'home')]
    #[Route(path: '/books', name: 'books')]
    public function __invoke(Request $request): Response
    {
        $queryString = $request->get('search', '');
        $categories = $request->get('category', []);
        $subCategories = $request->get('subCategory', []);
        $editions = $request->get('edition', []);
        $filters = $this->libraryRepository->getFilters();
        $bookResult = $this->libraryRepository->findBooks($queryString, $categories, $subCategories, $editions);

        return $this->render(
            view: 'list.html.twig',
            parameters: [
                'queryString' => $queryString,
                'suggestions' => $bookResult->suggestions,
                'filters' => $filters,
                'books' => $bookResult->books,
            ],
        );
    }
}

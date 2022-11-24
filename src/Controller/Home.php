<?php

namespace App\Controller;

use App\Repository\Elastic\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Home extends AbstractController
{
    public function __construct(private BookRepository $bookRepository)
    {
    }

    #[Route(path: '/', name: 'home')]
    public function __invoke(Request $request): Response
    {
        $books = $this->bookRepository->findBooks($request->get('search', ''));

        return $this->render(
            view: 'result.html.twig',
            parameters: ['books' => $books],
        );
    }
}

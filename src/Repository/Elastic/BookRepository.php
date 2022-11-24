<?php

namespace App\Repository\Elastic;

use App\Model\Elastic\Book;
use Elastica\Client;
use Elastica\Index;
use Elastica\Result;

class BookRepository
{
    private Index $index;

    public function __construct(Client $elastic)
    {
        $this->index = $elastic->getIndex('library');
    }

    /** @return list<Book> */
    public function findBooks(string $search): array
    {
        $query = [];

        return array_map(
            static fn(Result $result): Book => Book::create($result->getSource()),
            $this->index->search($query)->getResults()
        );
    }
}

<?php

namespace App\Repository\Elastic;

use App\Model\Elastic\Book;
use App\Model\Elastic\BookResult;
use App\Model\Elastic\Filter;
use App\Model\Elastic\Filters;
use Elastica\Aggregation;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Result;
use Elastica\Script\Script;
use Elastica\Suggest;

class LibraryRepository
{
    public function __construct(private readonly Index $index)
    {
    }

    public function findBooks(string $search, array $categories, array $subCategories, array $editions): BookResult
    {
        $boolQuery = (new Query\BoolQuery())
            ->addMust(
                $search
                    ? (new Query\MultiMatch())
                    ->setFields(['title^2', 'author.fullname', 'description'])
                    ->setQuery($search)
                    : (new Query\MatchAll())
            )
        ;

        if ($categories) {
            $boolQuery->addFilter(new Query\Terms('category.keyword', $categories));
        }

        if ($subCategories) {
            $boolQuery->addFilter(new Query\Terms('subCategory.keyword', $subCategories));
        }

        if ($editions) {
            $boolQuery->addFilter(new Query\Terms('edition.name.keyword', $editions));
        }

        $query = (new Query())
            ->setQuery($boolQuery)
            ->setHighlight([
                'pre_tags' => ['<em class="highlight">'],
                'post_tags' => ['</em>'],
                'fields' => [
                    'title' => new \StdClass(),
                    'author.fullname' => new \StdClass(),
                    'description' => new \StdClass(),
                ],
            ])
            ->setSuggest((new Suggest())
                ->addSuggestion((new Suggest\Term('author_suggest', 'author.fullname'))->setText($search))
                ->addSuggestion((new Suggest\Term('title_suggest', 'title'))->setText($search))
                ->addSuggestion((new Suggest\Term('description_suggest', 'description'))->setText($search))
            )
        ;

        $resultSet = $this->index->search($query);

        $books = array_map(
            static fn(Result $result): Book => Book::create($result->getId(), $result->getSource(), $result->getHighlights()),
            $resultSet->getResults()
        );

        $suggestions = [];
        foreach ($resultSet->getSuggests() as $suggestResult) {
            foreach ($suggestResult as $suggest) {
                foreach ($suggest['options'] as $option) {
                    $suggestions[] = $option['text'];
                }
            }
        }

        return new BookResult($books, $suggestions);
    }

    public function findOneBook(string $id): Book
    {
        return Book::create($id, $this->index->getDocument($id)->getData());
    }

    public function addBook(Book $book): void
    {
        $this->index->addDocument(new Document(
            $book->id,
            [
                'author' => [
                    'fullname' => $book->authorFullName,
                ],
                'title' => $book->title,
                'description' => $book->description,
                'category' => $book->category,
                'subCategory' => $book->subCategory,
                'edition' => [
                    'name' => $book->edition,
                ]
            ]
        ));
    }

    public function addBookRating(string $id, string $username, int $note): void
    {
        $this->index->updateDocument(
            new Script(
                scriptCode: 'ctx._source.ratings.add(params.rating)',
                params: [
                    'rating' => [
                        'username' => $username,
                        'rating' => $note,
                    ]
                ],
                documentId: $id,
            ),
        );
    }

    public function getFilters(): Filters
    {
        $result = $this->index->search(
            (new Query())
            ->setSize(0)
            ->addAggregation(
                (new Aggregation\Terms('by_category'))
                    ->setField('category.keyword')
                    ->setOrder('_key', 'asc')
                    ->setSize(50)
                    ->addAggregation(
                        (new Aggregation\Terms('by_subcategory'))
                            ->setOrder('_key', 'asc')
                            ->setSize(50)
                            ->setField('subCategory.keyword')
                    )
            )
            ->addAggregation(
                (new Aggregation\Terms('by_edition'))
                    ->setOrder('_key', 'asc')
                    ->setSize(50)
                    ->setField('edition.name.keyword')
            )
        )->getAggregations();

        $categories = [];
        foreach ($result['by_category']['buckets'] as $bucketCategory) {
            $category = Filter::createFromBucket($bucketCategory);

            foreach ($bucketCategory['by_subcategory']['buckets'] as $bucketSubCategory) {
                $category->addChildren(Filter::createFromBucket($bucketSubCategory));
            }

            $categories[] = $category;
        }

        $editions = [];
        foreach ($result['by_edition']['buckets'] as $bucketEdition) {
            $editions[] = Filter::createFromBucket($bucketEdition);
        }

        return new Filters($categories, $editions);
    }
}

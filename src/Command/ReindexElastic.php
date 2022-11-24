<?php

namespace App\Command;

use App\Entity\Book;
use App\Entity\Rating;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Client;
use Elastica\Index;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:elastic:reindex')]
class ReindexElastic extends Command
{
    private const MAPPING = [
        'settings' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
        ],
        'mappings' => [
            'dynamic' => 'strict',
            'properties' => [
                'author' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'keyword'],
                        'fullname' => ['type' => 'text', 'analyzer' => 'french',  'fields' => ['keyword' => ['type' => 'keyword']]],
                        'email' => ['type' => 'keyword'],
                    ]
                ],
                'edition' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'text', 'analyzer' => 'french',  'fields' => ['keyword' => ['type' => 'keyword']]],
                        'location' => ['type' => 'geo_point'],
                    ]
                ],
                'isbn' => ['type' => 'keyword'],
                'title' => ['type' => 'text', 'analyzer' => 'french',  'fields' => ['keyword' => ['type' => 'keyword']]],
                'description' => ['type' => 'text', 'analyzer' => 'french'],
                'category' => ['type' => 'text', 'analyzer' => 'french',  'fields' => ['keyword' => ['type' => 'keyword']]],
                'subCategory' => ['type' => 'text', 'analyzer' => 'french',  'fields' => ['keyword' => ['type' => 'keyword']]],
                'releaseDate' => ['type' => 'date'],
                'price' => ['type' => 'float'],
                'sales' => ['type' => 'integer'],
                'ratings' => [
                    'type' => 'nested',
                    'properties' => [
                        'rating' => ['type' => 'integer'],
                        'username' => ['type' => 'keyword'],
                    ]
                ]
            ]
        ]
    ];
    private const ALIAS = 'library';
    private const BATCH = 100;

    private Client $elastic;
    private SymfonyStyle $io;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->elastic = new Client('elasticsearch:9200');

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $newIndex = $this->createIndex();
        $this->indexData($newIndex->getName());

        $indexNames = $this->elastic->getCluster()->getIndexNames();

        foreach ($indexNames as $indexName) {
            if (
                str_starts_with($indexName, self::ALIAS)
                && $indexName !== $newIndex->getName()
            ) {
                $oldIndex = $this->elastic->getIndex($indexName);
                $oldIndex->hasAlias(self::ALIAS)
                    ? $oldIndex->removeAlias(self::ALIAS) && $indexToRemove = $oldIndex
                    : $oldIndex->delete()
                ;
            }
        }

        $newIndex->addAlias(self::ALIAS);
        $newIndex->removeAlias(self::ALIAS.'_indexing');

        if (isset($indexToRemove)) {
            $this->io->info("DELETING OLD INDEX \"{$indexToRemove->getName()}\"");
            $indexToRemove->delete();
        }

        return Command::SUCCESS;
    }

    private function createIndex(): Index
    {
        $now = new \DateTimeImmutable();
        $indexName = self::ALIAS.'_'.$now->format('Y-m-d-His');
        $this->io->info("CREATING INDEX \"$indexName\"");

        $index = $this->elastic->getIndex($indexName);
        $index->create(self::MAPPING);
        $index->addAlias(self::ALIAS.'_indexing');

        return $index;
    }

    private function indexData(string $indexName): void
    {
        $this->io->info("INDEXING DATA TO \"$indexName\"");
        $this->io->progressStart(
            $this->entityManager->getRepository(Book::class)->count([])
        );

        $q = $this->entityManager->getRepository(Book::class)
            ->createQueryBuilder('b')
            ->getQuery()
        ;

        $documents = [];
        /** @var Book $book */
        foreach ($q->toIterable() as $book) {
            $documents[] = ['index' => ['_index' => $indexName]];
            $documents[] = [
                'author' => $book->getAuthor()->toArray(),
                'edition' => $book->getEdition()->toArray(),
                'isbn' => $book->getIsbn(),
                'title' => $book->getTitle(),
                'description' => $book->getDescription(),
                'category' => $book->getSubCategory()->getParent()->getName(),
                'subCategory' => $book->getSubCategory()->getName(),
                'releaseDate' => $book->getReleaseDate()->format('Y-m-d'),
                'price' => $book->getPrice(),
                'sales' => $book->getSales(),
                'ratings' => $book->getRatings()->map(
                    fn(Rating $rating) => $rating->toArray()
                )->toArray(),
            ];

            if (0 === (\count($documents) / 2) % self::BATCH) {
                $this->elastic->bulk($documents);
                $documents = [];
            }

            $this->io->progressAdvance();
            $this->entityManager->detach($book);
        }

        if ($documents) {
            $this->elastic->bulk($documents);
        }

        $this->io->progressFinish();
    }


}

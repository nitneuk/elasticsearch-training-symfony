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
    ];
    private const ALIAS = 'library';
    private const BATCH = 100;

    private SymfonyStyle $io;

    public function __construct(
        private Client $elastic,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $index = $this->elastic->getIndex('library-datedujour');

//        Etape 1 création de l'index avec son mapping
//        $index->create(...);

//        Etape 2 ajout d'un alias "library_indexing"
//        $index->addAlias()

//        Etape 3 indexation de tous les livres stockés dans PostgreSQL

//        Etape 4 suppression de l'alias "library_indexing"

//        Etape 5 ajout de l'alias "library"

        return Command::SUCCESS;
    }
}

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

    private Client $elastic;
    private SymfonyStyle $io;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->elastic = new Client('elasticsearch:9200');

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO : implement

        return Command::SUCCESS;
    }
}

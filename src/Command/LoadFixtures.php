<?php

namespace App\Command;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Author;
use App\Entity\Edition;
use App\Entity\Rating;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:fixtures:load')]
class LoadFixtures extends Command
{
    private const CATEGORIES = [
        'Littérature' => [
            'Romans',
            'Policiers et thrillers',
            'Romances',
            'Science-fiction et Fantasy',
            'Théâtre et poésie',
            'Lettres supérieures',
            'Récits de voyages',
            'Pléiades',
            'Livre audio',
        ],
        'Livres jeunesse' => [
            '0-3 ans',
            '3-6 ans',
            '6-9 ans',
            '9-12 ans',
            'Romans ados',
            'Jeux et loisirs créatifs',
            'Documentaires',
            'Lire en anglais',
            'Livres CD',
        ],
        'BD, Manga et Humour' => [
            'BD Adultes',
            'BD jeunesse',
            'Romans graphiques et BD indépendantes',
            'Mangas',
            'Comics',
            'Humour',
        ],
        'Livres scolaires' => [
            'Maternelle et éveil',
            'Primaire',
            'Collège',
            'Lycée filières générales',
            'Lycée filières professionnelles',
            'BTS',
            'Orientation et métiers',
            'Concours et Classes Prépas',
            'Pédagogie',
            'Dictionnaires et encyclopédies',
            'Cahiers de vacances',
        ],
        'Langues et livres en VO' => [
            'Français langue étrangère',
            'Anglais',
            'Allemand',
            'Espagnol',
            'Portugais',
            'Italien',
            'Arabe',
            'Chinois',
            'Japonais',
            'Russe',
            'Autres langues',
        ],
        'Arts, culture et société' => [
            'Cinéma',
            'Photo',
            'Danse et cirque',
            'Musique',
            'Architecture et urbanisme',
            'Peinture et sculpture',
            'Mode',
            'Histoire de l\'art',
            'Actualités médias et société',
            'Politique',
        ],
        'Tourisme, Voyages et Guides' => [
            'France',
            'Europe',
            'Amérique',
            'Asie',
            'Afrique',
            'Océanie et Australie',
            'Proche et Moyen-Orient',
            'Cartes, Atlas et Plans',
            'Beaux livres et récits de voyages',
        ],
        'Vie pratique' => [
            'Santé et Bien-être',
            'Esotérisme',
            'Développement personnel',
            'Cuisine et Vins',
            'Loisirs créatifs et jeux',
            'Bricolage et jardinage',
            'Informatique',
            'Bâtiment',
            'Calendriers et agendas',
        ],
        'Nature et sports' => [
            'Nature et Animaux',
            'Sports',
            'Mer',
            'Montagne et Randonnée',
            'Ecologie et développement durable',
        ],
        'Sciences humaines et sociales' => [
            'Histoire',
            'Géographie',
            'Droit',
            'Economie et finance',
            'Entreprise et management',
            'Philosophie',
            'Sociologie et ethnologie',
            'Religions',
        ],
        'Sciences et médecine' => [
            'Médecine et paramédical',
            'Psychologie et psychanalyse',
            'Physique, Chimie et Biologie',
            'Mathématiques',
            'Histoire et Philosophie des sciences',
        ],
        'Livres à prix réduits' => [
            'Cuisine et vins',
            'Nature et tourisme',
            'Loisirs et sports',
            'Bien-être et santé',
            'Jeunesse',
            'Arts',
            'BD et humour',
            'Savoirs',
            'Littérature',
            'Toute l\'offre',
        ],
    ];

    private const GENDERS = ['male', 'female'];
    private const BATCH_SIZE = 100;

    private Generator $faker;

    private int $numberOfAuthors;
    private int $numberOfEditions;
    private int $numberOfBooks;

    /** @var ArrayCollection<Category> */
    private ArrayCollection $categories;
    /** @var ArrayCollection<Author> */
    private ArrayCollection $authors;
    /** @var ArrayCollection<Edition> */
    private ArrayCollection $editions;

    private SymfonyStyle $io;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->faker = Factory::create('fr_FR');

        $this->categories = new ArrayCollection();
        $this->authors = new ArrayCollection();
        $this->editions = new ArrayCollection();

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument(name: 'numberOfAuthors', description: 'Numbers of Author to create', default: 1000);
        $this->addArgument(name: 'numberOfEditions', description: 'Numbers of Edition to create', default: 50);
        $this->addArgument(name: 'numberOfBooks', description: 'Numbers of Book to create', default: 5000);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->numberOfAuthors = (int) $input->getArgument('numberOfAuthors');
        $this->numberOfEditions = (int) $input->getArgument('numberOfEditions');
        $this->numberOfBooks = (int) $input->getArgument('numberOfBooks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->createCategories();
        $this->createAuthors();
        $this->createEditions();

        $this->createBooks();

        return Command::SUCCESS;
    }

    private function createCategories(): void
    {
        $this->io->info('CREATING CATEGORIES');
        $this->io->progressStart(count(self::CATEGORIES));

        foreach (self::CATEGORIES as $categoryName => $subCategoryNames) {
            $subCategories = new ArrayCollection(
                \array_map(
                    fn(string $subCategoryName) => new Category($subCategoryName),
                    $subCategoryNames,
                )
            );

            $category = new Category($categoryName, $subCategories);

            $this->categories->add($category);
            $this->entityManager->persist($category);
            $this->io->progressAdvance();
        }

        $this->entityManager->flush();
        $this->io->progressFinish();
    }

    private function createAuthors(): void
    {
        $this->io->info('CREATING AUTHORS');
        $this->io->progressStart($this->numberOfAuthors);

        for ($i = 1; $i <= $this->numberOfAuthors; $i++) {
            $gender = self::GENDERS[\array_rand(self::GENDERS)];

            $author = new Author(
                title: $this->faker->title($gender),
                fullName: $this->faker->name($gender),
                email: $this->faker->email(),
            );

            $this->authors->add($author);
            $this->entityManager->persist($author);
            $this->io->progressAdvance();

            $this->batchFlush($i);
        }

        $this->entityManager->flush();
        $this->io->progressFinish();
    }

    private function createEditions(): void
    {
        $this->io->info('CREATING EDITIONS');
        $this->io->progressStart($this->numberOfEditions);

        for ($i = 1; $i <= $this->numberOfEditions; $i++) {
            $edition = new Edition(
                name: $this->faker->company(),
                location: \json_encode([
                    'lat' => $this->faker->latitude(43, 50),
                    'lon' => $this->faker->longitude(-1, 3),
                ])
            );

            $this->editions->add($edition);
            $this->entityManager->persist($edition);
            $this->io->progressAdvance();

            $this->batchFlush($i);
        }

        $this->entityManager->flush();
        $this->io->progressFinish();
    }

    private function createBooks()
    {
        $this->io->info('CREATING BOOKS');
        $this->io->progressStart($this->numberOfBooks);

        for ($i = 1; $i <= $this->numberOfBooks; $i++) {
            $author = $this->authors->get($this->faker->numberBetween(int2: $this->numberOfAuthors - 1));
            $edition = $this->editions->get($this->faker->numberBetween(int2: $this->numberOfEditions - 1));
            /** @var Category $category */
            $category = $this->categories->get($this->faker->numberBetween(int2: $this->categories->count() - 1));
            $subCategory = $category->getChildren()->get($this->faker->numberBetween(int2: $category->getChildren()->count() - 1));

            $book = new Book(
                author: $author,
                edition: $edition,
                isbn: $this->faker->isbn13(),
                title: $this->faker->realTextBetween(3, 25),
                description: $this->faker->realText(),
                subCategory: $subCategory,
                releaseDate: \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween()),
                price: $this->faker->randomFloat(1, 5, 30),
                sales: $this->faker->randomNumber(4, false),
                ratings: $this->getRatings(),
            );

            $this->entityManager->persist($book);
            $this->io->progressAdvance();

            $this->batchFlush($i, Book::class);
        }

        $this->entityManager->flush();
        $this->io->progressFinish();
    }

    /** @return ArrayCollection<Rating> */
    private function getRatings(): ArrayCollection
    {
        $ratings = new ArrayCollection();
        $totalRatings = $this->faker->numberBetween(int2: 5);

        for ($i = 0; $i <= $totalRatings; $i++) {
            $ratings->add(
                new Rating(
                    note: $this->faker->numberBetween(int2: 5),
                    username: $this->faker->userName(),
                )
            );
        }

        return $ratings;
    }

    private function batchFlush(int $progress, ?string $entityClass = null)
    {
        if (0 === $progress % self::BATCH_SIZE) {
            $this->entityManager->flush();

            if ($entityClass) {
                $this->entityManager->clear($entityClass);
            }
        }
    }
}

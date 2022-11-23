<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity]
class Book
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private UuidV6 $id;

    #[ORM\ManyToOne(targetEntity: Author::class)]
    private Author $author;

    #[ORM\ManyToOne(targetEntity: Edition::class)]
    private Edition $edition;

    #[ORM\Column]
    private string $isbn;

    #[ORM\Column(length: 50)]
    private string $title;

    #[ORM\Column]
    private string $description;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    private Category $subCategory;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $releaseDate;

    #[ORM\Column(type: 'float')]
    private float $price;

    #[ORM\Column(type: 'integer')]
    private int $sales;

    #[ORM\OneToMany(mappedBy: 'book', targetEntity: Rating::class, cascade: ['all'])]
    private Collection $ratings;

    public function __construct(
        Author $author,
        Edition $edition,
        string $isbn,
        string $title,
        string $description,
        Category $subCategory,
        \DateTimeImmutable $releaseDate,
        float $price,
        int $sales,
        ArrayCollection $ratings,
    )
    {
        $this->id = new UuidV6();
        $this->author = $author;
        $this->edition = $edition;
        $this->isbn = $isbn;
        $this->title = $title;
        $this->description = $description;
        $this->subCategory = $subCategory;
        $this->releaseDate = $releaseDate;
        $this->price = $price;
        $this->sales = $sales;
        $this->ratings = $ratings;
    }

    public function getId(): UuidV6
    {
        return $this->id;
    }

    public function getAuthor(): Author
    {
        return $this->author;
    }

    public function getEdition(): Edition
    {
        return $this->edition;
    }

    public function getIsbn(): int
    {
        return $this->isbn;
    }

    public function getTitle(): int
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSubCategory(): Category
    {
        return $this->subCategory;
    }

    public function getReleaseDate(): \DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getSales(): int
    {
        return $this->sales;
    }

    public function getRatings(): Collection
    {
        return $this->ratings;
    }
}

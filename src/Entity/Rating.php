<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity]
class Rating
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private UuidV6 $id;

    #[ORM\Column(type: 'smallint')]
    private int $note;

    #[ORM\Column(length: 50)]
    private string $username;

    #[ORM\ManyToOne(targetEntity: Book::class, inversedBy: 'ratings')]
    private Book $book;

    public function __construct(int $note, string $username)
    {
        $this->id = new UuidV6();
        $this->note = $note;
        $this->username = $username;
    }

    public function getId(): UuidV6
    {
        return $this->id;
    }

    public function getNote(): int
    {
        return $this->note;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setBook(Book $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'rating' => $this->note,
            'username' => $this->username,
        ];
    }
}

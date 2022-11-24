<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity]
class Author
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private UuidV6 $id;

    #[ORM\Column(length: 5)]
    private string $title;

    #[ORM\Column(length: 50)]
    private string $fullName;

    #[ORM\Column(length: 50)]
    private string $email;

    public function __construct(
        string $title,
        string $fullName,
        string $email,
    )
    {
        $this->id = new UuidV6();
        $this->title = $title;
        $this->fullName = $fullName;
        $this->email = $email;
    }

    public function getId(): UuidV6
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'fullname' => $this->fullName,
            'email' => $this->email,
        ];
    }
}

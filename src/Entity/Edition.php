<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity]
class Edition
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private UuidV6 $id;

    #[ORM\Column(length: 50)]
    private string $name;

    #[ORM\Column(type: 'json')]
    private string $location;

    public function __construct(string $name, string $location)
    {
        $this->id = new UuidV6();
        $this->name = $name;
        $this->location = $location;
    }

    public function getId(): UuidV6
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'location' => \json_decode($this->location),
        ];
    }
}

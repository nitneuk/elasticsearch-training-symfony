<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity]
class Category
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private UuidV6 $id;

    #[ORM\Column(length: 50)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['all'])]
    private Collection $children;

    /** @param null|ArrayCollection<Category> $subCategories */
    public function __construct(string $name, ?ArrayCollection $subCategories = null)
    {
        $this->id = new UuidV6();
        $this->name = $name;
        $this->children = new ArrayCollection();

        /** @var Category $subCategory */
        foreach ($subCategories ?? [] as $subCategory) {
            $subCategory->setParent($this);
            $this->children->add($subCategory);
        }
    }

    public function getId(): UuidV6
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function setParent(Category $parent): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }
}

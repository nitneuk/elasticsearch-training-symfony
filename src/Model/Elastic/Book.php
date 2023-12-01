<?php

namespace App\Model\Elastic;

final class Book
{
    private function __construct(
        public readonly string|null $id,
        public readonly string $authorFullName,
        public readonly string $title,
        public readonly string $description,
        public readonly string $category,
        public readonly string $subCategory,
        public readonly string $edition,
        public readonly string $price,
        public readonly array $ratings
    )
    {
    }

    public static function create(string|null $id, array $source, array $highlight = []): self
    {
        return new self(
            id: $id,
            authorFullName: $highlight['author.fullname'][0] ?? $source['author']['fullname'],
            title: $highlight['title'][0] ?? $source['title'],
            description: $highlight['description'][0] ?? $source['description'],
            category: $source['category'],
            subCategory: $source['subCategory'],
            edition: $source['edition']['name'],
            price: $source['price'],
            ratings: $source['ratings']
        );
    }
}

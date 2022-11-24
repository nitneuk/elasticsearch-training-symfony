<?php

namespace App\Model\Elastic;

final class Book
{
    private function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $price,
    )
    {
    }

    public static function create(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            price: $data['price'],
        );
    }
}

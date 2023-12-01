<?php

namespace App\Model\Elastic;

final class Filter
{
    private function __construct(
        public readonly string $name,
        public readonly int|null $count = null,
        public array $children = [],
    )
    {
    }

    public static function create(string $name): self
    {
        return new self($name);
    }

    public static function createFromBucket(array $data): self
    {
        return new self(
            name: $data['key'],
            count: $data['doc_count'],
        );
    }

    public function addChildren(self $filter) {
        $this->children[] = $filter;
    }
}

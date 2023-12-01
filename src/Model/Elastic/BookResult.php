<?php

namespace App\Model\Elastic;

final class BookResult
{
    public function __construct(
        public readonly array $books,
        public readonly array $suggestions,
    )
    {
    }
}

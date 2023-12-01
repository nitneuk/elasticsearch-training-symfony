<?php

namespace App\Model\Elastic;

final class Filters
{
    public function __construct(
        public readonly array $categories,
        public readonly array $editions,
    )
    {
    }
}

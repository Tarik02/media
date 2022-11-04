<?php

namespace App\Support\Reddit;

class Listing
{
    public function __construct(
        public readonly ?string $after,
        public readonly ?string $before,
        public readonly array $children
    ) {
    }
}

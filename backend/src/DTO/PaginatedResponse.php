<?php

namespace App\DTO;

class PaginatedResponse
{
    public function __construct(
        public readonly array $data,
        public readonly int   $total,
        public readonly int   $page,
        public readonly int   $limit,
        public readonly int   $pages,
    ) {}
}

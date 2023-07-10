<?php

declare(strict_types=1);

namespace Camoo\Config\Dto;

class ParserDto
{
    public function __construct(
        public readonly array $data,
        public readonly array $files,
        public readonly int $loaded
    ) {
    }
}

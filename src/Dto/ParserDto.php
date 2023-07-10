<?php

declare(strict_types=1);

namespace Camoo\Config\Dto;

/**
 * @author     Camoo Sarl <config@camoo.sarl>
 *
 * @link       https://github.com/camoo/config
 *
 * @license    MIT
 */
class ParserDto
{
    public function __construct(
        public readonly array $data,
        public readonly array $files,
        public readonly int $loaded
    ) {
    }
}

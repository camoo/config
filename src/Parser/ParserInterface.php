<?php

declare(strict_types=1);

namespace Camoo\Config\Parser;

/**
 * Config file parser interface
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @author     Filip Š <projects@filips.si>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
interface ParserInterface
{
    /**
     * Parses a configuration from file `$filename` and gets its contents as an array
     *
     * @return array
     */
    public function parseFile(string $filename): ?array;

    /**
     * Parses a configuration from string `$config` and gets its contents as an array
     *
     * @return array
     */
    public function parseString(string $config): ?array;

    /** Returns an array of allowed file extensions for this parser */
    public function getSupportedExtensions(): array;
}

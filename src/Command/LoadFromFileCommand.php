<?php

declare(strict_types=1);

namespace Camoo\Config\Command;

use Camoo\Config\Parser\ParserFactoryInterface;
use Camoo\Config\Parser\ParserInterface;
use DirectoryIterator;

/**
 * @author     Camoo Sarl <config@camoo.sarl>
 *
 * @link       https://github.com/camoo/config
 *
 * @license    MIT
 */
final class LoadFromFileCommand
{
    public function __construct(
        public readonly array|DirectoryIterator $filename,
        public readonly ParserInterface|ParserFactoryInterface|null $parser = null
    ) {
    }
}

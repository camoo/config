<?php

declare(strict_types=1);

namespace Camoo\Config\Command;

use Camoo\Config\Dto\ParserDto;
use Camoo\Config\Enum\Parser;
use Camoo\Config\Exception\ParseException;
use Camoo\Config\Exception\UnsupportedFormatException;
use Camoo\Config\Parser\ParserInterface;
use DirectoryIterator;
use SplFileInfo;

final class LoadFromFileCommandHandler
{
    private const DIST_EXTENSION = 'dist';

    private int $loaded = 0;

    private array $data = [];

    private array $files = [];

    /** @throws ParseException */
    public function handle(LoadFromFileCommand $command): ParserDto
    {
        $parser = $command->parser;

        /** @var SplFileInfo|string|DirectoryIterator $fileInfo */
        foreach ($command->filename as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo && $fileInfo->isDot()) {
                continue;
            }
            $path = is_string($fileInfo) ? $fileInfo : $fileInfo->getPathname();
            if ($parser instanceof ParserInterface) {
                $this->data = array_replace_recursive($this->data, $parser->parseFile($path));
                $this->files[] = $path;
                ++$this->loaded;
                continue;
            }
            $this->handleParseFromExtension($fileInfo, $path);

            $parser = null;
            ++$this->loaded;
        }

        return new ParserDto($this->data, $this->files, $this->loaded);
    }

    /** @throws ParseException */
    private function handleParseFromExtension(SplFileInfo|DirectoryIterator|string $fileInfo, string $path): void
    {
        // Get file information
        $info = is_string($fileInfo) ? pathinfo($path) : null;
        $basename = $fileInfo instanceof SplFileInfo ? $fileInfo->getBasename() : $info['basename'];
        $parts = explode('.', $basename);

        $extension = $fileInfo instanceof SplFileInfo ? $fileInfo->getExtension() : array_pop($parts);

        // Skip the `dist` extension
        if ($extension === self::DIST_EXTENSION) {
            $extension = array_pop($parts);
        }

        $parser = $this->getParser($extension);
        $this->data = array_replace_recursive($this->data, $parser->parseFile($path));
        $this->files[] = $path;
    }

    /**
     * Gets a parser for a given file extension.
     *
     * @throws UnsupportedFormatException If `$extension` is an unsupported file format
     */
    private function getParser(string $extension): ParserInterface
    {
        foreach (Parser::cases() as $parser) {
            if (strtoupper($extension) !== $parser->name) {
                continue;
            }

            $instance = $parser->getInstance();
            if (in_array($parser, $instance->getSupportedExtensions(), true)) {
                return $instance;
            }
        }

        // If none exist, then throw an exception
        throw new UnsupportedFormatException('Unsupported configuration format');
    }
}

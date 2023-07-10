<?php

declare(strict_types=1);

namespace Camoo\Config;

use Camoo\Config\Enum\Parser;
use Camoo\Config\Enum\Writer;
use Camoo\Config\Exception\EmptyDirectoryException;
use Camoo\Config\Exception\FileNotFoundException;
use Camoo\Config\Exception\ParseException;
use Camoo\Config\Exception\UnsupportedFormatException;
use Camoo\Config\Exception\WriteException;
use Camoo\Config\Parser\ParserInterface;
use Camoo\Config\Writer\WriterInterface;
use DirectoryIterator;
use SplFileInfo;
use Stringable;
use Throwable;

/**
 * Configuration reader and writer for PHP.
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @author     Filip Š <projects@filips.si>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
class Config extends AbstractConfig implements Stringable
{
    private const DIST_EXTENSION = 'dist';

    private array $files;

    /**
     * Loads a Config instance.
     *
     * @param string|array         $values Filenames or string with configuration
     * @param ParserInterface|null $parser Configuration parser
     *
     * @throws ParseException
     */
    public function __construct(
        private readonly array|string $values,
        ?ParserInterface $parser = null,
        private readonly bool $isString = false
    ) {
        $this->initialize($values, $parser, $isString);
        parent::__construct($this->data);
    }

    public function __toString(): string
    {
        if ($this->isString) {
            return $this->values;
        }
        $content = '';
        $counter = 1;
        foreach ($this->files as $file) {
            $content .= $counter . '. ' . $file . PHP_EOL . file_get_contents($file) . PHP_EOL;
            ++$counter;
        }

        return $content;
    }

    /**
     * Static method for loading a Config instance.
     *
     * @param string|array         $values Filenames or string with configuration
     * @param ParserInterface|null $parser Configuration parser
     *
     * @throws ParseException
     */
    public static function load(array|string $values, ?ParserInterface $parser = null, bool $isString = false): self
    {
        return new self($values, $parser, $isString);
    }

    /**
     * Writes configuration to file.
     *
     * @param string               $filename Filename to save configuration to
     * @param WriterInterface|null $writer   Configuration writer
     *
     * @throws WriteException if the data could not be written to the file
     */
    public function toFile(string $filename, ?WriterInterface $writer = null): void
    {
        if ($writer instanceof WriterInterface) {
            $writer->toFile($this->all(), $filename);

            return;
        }

        $info = pathinfo($filename);
        $parts = explode('.', $info['basename']);
        $extension = array_pop($parts);

        // Skip the `dist` extension
        if ($extension === self::DIST_EXTENSION) {
            $extension = array_pop($parts);
        }
        $writer = $this->getWriter($extension);
        $writer->toFile($this->all(), $filename);

        $writer = null;
    }

    /**
     * Writes configuration to string.
     *
     * @param WriterInterface $writer Configuration writer
     * @param bool            $pretty Encode pretty
     *
     * @throws Throwable
     */
    public function toString(WriterInterface $writer, bool $pretty = true): string
    {
        return $writer->toString($this->all(), $pretty);
    }

    /**
     * Gets a parser for a given file extension.
     *
     * @throws UnsupportedFormatException If `$extension` is an unsupported file format
     */
    protected function getParser(string $extension): ParserInterface
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

    /**
     * Gets a writer for a given file extension.
     *
     * @throws UnsupportedFormatException If `$extension` is an unsupported file format
     */
    protected function getWriter(string $extension): WriterInterface
    {
        foreach (Writer::cases() as $writer) {
            if (strtoupper($extension) !== $writer->name) {
                continue;
            }
            $instance = $writer->getInstance();
            if (in_array($writer, $instance->getSupportedExtensions(), true)) {
                return $instance;
            }
        }

        // If none exist, then throw an exception
        throw new UnsupportedFormatException('Unsupported configuration format' . $extension);
    }

    /**
     * Gets an array of paths
     *
     * @throws FileNotFoundException If a file is not found at `$path`
     */
    protected function getPathFromArray(array $path): array
    {
        $paths = [];
        foreach ($path as $unverifiedPath) {
            try {
                // Check if `$unverifiedPath` is optional
                // If it exists, then it's added to the list
                // If it doesn't, it throws an exception which we catch
                if ($unverifiedPath[0] !== '?') {
                    $paths = array_merge($paths, $this->getValidPath($unverifiedPath));
                    continue;
                }

                $optionalPath = ltrim($unverifiedPath, '?');
                $paths = array_merge($paths, $this->getValidPath($optionalPath));
            } catch (FileNotFoundException $exception) {
                // If `$unverifiedPath` is optional, then skip it
                if ($unverifiedPath[0] === '?') {
                    continue;
                }
                throw $exception;
            }
        }

        return $paths;
    }

    /**
     * Checks `$path` to see if it is either an array, a directory, or a file.
     *
     * @throws EmptyDirectoryException If `$path` is an empty directory
     * @throws FileNotFoundException   If a file is not found at `$path`
     */
    protected function getValidPath(array|string $path): array|DirectoryIterator
    {
        // If `$path` is arrayed
        if (is_array($path)) {
            return $this->getPathFromArray($path);
        }

        if (is_string($path) && is_file($path)) {
            return [$path];
        }

        // If `$path` is not a file, throw an exception
        if (!is_dir($path)) {
            throw new FileNotFoundException("Configuration file: [{$path}] cannot be found");
        }
        // If `$path` is a directory

        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return new DirectoryIterator($path);
    }

    /** @throws ParseException */
    private function initialize(array|string $values, ?ParserInterface $parser = null, bool $isString = false): void
    {
        if ($isString === true) {
            $this->loadFromString($parser, $values);

            return;
        }
        $this->loadFromFile($values, $parser);
    }

    /**
     * Loads configuration from file.
     *
     * @param string|array         $path   Filenames or directories with configuration
     * @param ParserInterface|null $parser Configuration parser
     *
     * @throws ParseException
     */
    private function loadFromFile(array|string $path, ?ParserInterface $parser = null): void
    {
        $paths = $this->getValidPath($path);
        $this->data = [];
        $loaded = 0;
        /** @var SplFileInfo|string $fileInfo */
        foreach ($paths as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo && $fileInfo->isDot()) {
                continue;
            }

            $path = is_string($fileInfo) ? $fileInfo : $fileInfo->getPathname();

            if ($parser instanceof ParserInterface) {
                // Try to load file using specified parser
                $this->data = array_replace_recursive($this->data, $parser->parseFile($path));
                ++$loaded;
                continue;
            }

            // Get file information
            $info = is_string($fileInfo) ? pathinfo($path) : null;
            $basename = $fileInfo instanceof SplFileInfo ? $fileInfo->getBasename() : $info['basename'];
            $parts = explode('.', $basename);

            $extension = $fileInfo instanceof SplFileInfo ? $fileInfo->getExtension() : array_pop($parts);

            // Skip the `dist` extension
            if ($extension === self::DIST_EXTENSION) {
                $extension = array_pop($parts);
            }

            // Get file parser
            $parser = $this->getParser($extension);

            // Try to load file
            $this->data = array_replace_recursive($this->data, $parser->parseFile($path));
            $this->files[] = $path;

            // Clean parser
            $parser = null;
            ++$loaded;
        }
        if ($loaded === 0) {
            throw new EmptyDirectoryException(
                sprintf('Directory %s is empty', is_string($path) ? $path : json_encode($path))
            );
        }
    }

    /**
     * Loads configuration from string.
     *
     * @param string          $configuration String with configuration
     * @param ParserInterface $parser        Configuration parser
     *
     * @throws ParseException
     */
    private function loadFromString(ParserInterface $parser, string $configuration): void
    {
        $this->data = [];

        // Try to parse string
        $this->data = array_replace_recursive($this->data, $parser->parseString($configuration));
    }
}

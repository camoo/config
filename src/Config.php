<?php

declare(strict_types=1);

namespace Camoo\Config;

use Camoo\Config\Enum\Parser;
use Camoo\Config\Enum\Writer;
use Camoo\Config\Exception\EmptyDirectoryException;
use Camoo\Config\Exception\FileNotFoundException;
use Camoo\Config\Exception\UnsupportedFormatException;
use Camoo\Config\Exception\WriteException;
use Camoo\Config\Parser\ParserInterface;
use Camoo\Config\Writer\WriterInterface;
use DirectoryIterator;
use SplFileInfo;

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
class Config extends AbstractConfig
{
    /**
     * Loads a Config instance.
     *
     * @param string|array         $values Filenames or string with configuration
     * @param ParserInterface|null $parser Configuration parser
     * @param bool                 $string Enable loading from string
     */
    public function __construct(array|string $values, ?ParserInterface $parser = null, bool $string = false)
    {
        if ($string === true) {
            $this->loadFromString($parser, $values);
        } else {
            $this->loadFromFile($values, $parser);
        }

        parent::__construct($this->data);
    }

    /**
     * Static method for loading a Config instance.
     *
     * @param string|array         $values Filenames or string with configuration
     * @param ParserInterface|null $parser Configuration parser
     * @param bool                 $string Enable loading from string
     */
    public static function load(array|string $values, ?ParserInterface $parser = null, bool $string = false): self
    {
        return new static($values, $parser, $string);
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
        if ($writer === null) {
            // Get file information
            $info = pathinfo($filename);
            $parts = explode('.', $info['basename']);
            $extension = array_pop($parts);

            // Skip the `dist` extension
            if ($extension === 'dist') {
                $extension = array_pop($parts);
            }

            // Get file writer
            $writer = $this->getWriter($extension);

            // Try to save file
            $writer->toFile($this->all(), $filename);

            // Clean writer
            $writer = null;
        } else {
            // Try to load file using specified writer
            $writer->toFile($this->all(), $filename);
        }
    }

    /**
     * Writes configuration to string.
     *
     * @param WriterInterface $writer Configuration writer
     * @param bool            $pretty Encode pretty
     */
    public function toString(WriterInterface $writer, bool $pretty = true): string
    {
        return $writer->toString($this->all(), $pretty);
    }

    /**
     * Loads configuration from file.
     *
     * @param string|array         $path   Filenames or directories with configuration
     * @param ParserInterface|null $parser Configuration parser
     */
    protected function loadFromFile(array|string $path, ?ParserInterface $parser = null): void
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

            if ($parser === null) {
                // Get file information
                $info = is_string($fileInfo) ? pathinfo($path) : null;
                $basename = $fileInfo instanceof SplFileInfo ? $fileInfo->getBasename() : $info['basename'];
                $parts = explode('.', $basename);

                $extension = $fileInfo instanceof SplFileInfo ? $fileInfo->getExtension() : array_pop($parts);

                // Skip the `dist` extension
                if ($extension === 'dist') {
                    $extension = array_pop($parts);
                }

                // Get file parser
                $parser = $this->getParser($extension);

                // Try to load file
                $this->data = array_replace_recursive($this->data, $parser->parseFile($path));

                // Clean parser
                $parser = null;
            } else {
                // Try to load file using specified parser
                $this->data = array_replace_recursive($this->data, $parser->parseFile($path));
            }
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
     */
    protected function loadFromString(ParserInterface $parser, string $configuration): void
    {
        $this->data = [];

        // Try to parse string
        $this->data = array_replace_recursive($this->data, $parser->parseString($configuration));
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
            } catch (FileNotFoundException $e) {
                // If `$unverifiedPath` is optional, then skip it
                if ($unverifiedPath[0] === '?') {
                    continue;
                }

                // Otherwise rethrow the exception
                throw $e;
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
}

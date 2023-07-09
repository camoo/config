<?php

declare(strict_types=1);

namespace Camoo\Config\Parser;

use Camoo\Config\Enum\Parser;
use Camoo\Config\Exception\ParseException;
use Camoo\Config\Exception\UnsupportedFormatException;
use Exception;

/**
 * PHP parser
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @author     Filip Š <projects@filips.si>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
class Php implements ParserInterface
{
    /**
     * {@inheritDoc}
     * Loads a PHP file and gets its' contents as an array
     *
     * @throws ParseException             If the PHP file throws an exception
     * @throws UnsupportedFormatException If the PHP file does not return an array
     */
    public function parseFile(string $filename): array
    {
        // Run the fileEval the string, if it throws an exception, rethrow it
        try {
            $data = require_once $filename;
        } catch (Exception $exception) {
            throw new ParseException(
                [
                    'message' => 'PHP file threw an exception',
                    'exception' => $exception,
                ]
            );
        }

        // Complete parsing
        return (array)$this->parse($data);
    }

    /**
     * {@inheritDoc}
     * Loads a PHP string and gets its' contents as an array
     *
     * @throws ParseException             If the PHP string throws an exception
     * @throws UnsupportedFormatException If the PHP string does not return an array
     */
    public function parseString(string $config): array
    {
        // Handle PHP start tag
        $config = trim($config);
        if (str_starts_with($config, '<?')) {
            $config = '?>' . $config;
        }

        // Eval the string, if it throws an exception, rethrow it
        try {
            $data = $this->isolate($config);
        } catch (Exception $exception) {
            throw new ParseException(
                [
                    'message' => 'PHP string threw an exception',
                    'exception' => $exception,
                ]
            );
        }

        // Complete parsing
        return (array)$this->parse($data);
    }

    /** {@inheritDoc} */
    public function getSupportedExtensions(): array
    {
        return [Parser::PHP];
    }

    /**
     * Completes parsing of PHP data
     *
     * @param callable|array|null $data
     */
    protected function parse(callable|array|null $data = null): ?array
    {
        // If we have a callable, run it and expect an array back
        if (is_callable($data)) {
            $data = call_user_func($data);
        }

        // Check for array, if It's anything else, throw an exception
        if (!is_array($data)) {
            throw new UnsupportedFormatException('PHP data does not return an array');
        }

        return $data;
    }

    /** Runs PHP string in isolated method */
    protected function isolate(string $EGsfKPdue7ahnMTy): callable|array
    {
        return eval($EGsfKPdue7ahnMTy);
    }
}

<?php

declare(strict_types=1);

namespace Camoo\Config\Parser;

use Camoo\Config\Enum\Parser;
use Camoo\Config\Exception\ParseException;

/**
 * JSON parser
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @author     Filip Š <projects@filips.si>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
class Json implements ParserInterface
{
    /**
     * {@inheritDoc}
     * Parses an JSON file as an array
     *
     * @throws ParseException If there is an error parsing the JSON file
     */
    public function parseFile(string $filename): array
    {
        $data = json_decode(file_get_contents($filename), true);

        return (array)$this->parse($data, $filename);
    }

    /**
     * {@inheritDoc}
     * Parses an JSON string as an array
     *
     * @throws ParseException If there is an error parsing the JSON string
     */
    public function parseString(string $config): array
    {
        $data = json_decode($config, true);

        return (array)$this->parse($data);
    }

    /** {@inheritDoc} */
    public function getSupportedExtensions(): array
    {
        return [Parser::JSON];
    }

    /**
     * Completes parsing of JSON data
     *
     * @throws ParseException If there is an error parsing the JSON data
     */
    protected function parse(?array $data = null, ?string $filename = null): ?array
    {
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = 'Syntax error';
            if (function_exists('json_last_error_msg')) {
                $errorMessage = json_last_error_msg();
            }

            $error = [
                'message' => $errorMessage,
                'type' => json_last_error(),
                'file' => $filename,
            ];

            throw new ParseException($error);
        }

        return $data;
    }
}

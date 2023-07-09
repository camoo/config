<?php

declare(strict_types=1);

namespace Camoo\Config\Parser;

use Camoo\Config\Enum\Parser;
use Camoo\Config\Exception\ParseException;
use Exception;
use Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * YAML parser
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @author     Filip Š <projects@filips.si>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
class Yaml implements ParserInterface
{
    /**
     * {@inheritDoc}
     * Loads a YAML/YML file as an array
     *
     * @throws ParseException If there is an error parsing the YAML file
     */
    public function parseFile(string $filename): ?array
    {
        try {
            $data = YamlParser::parseFile($filename, YamlParser::PARSE_CONSTANT);
        } catch (Exception $exception) {
            throw new ParseException(
                [
                    'message' => 'Error parsing YAML file',
                    'exception' => $exception,
                ]
            );
        }

        return (array)$this->parse($data);
    }

    /**
     * {@inheritDoc}
     * Loads a YAML/YML string as an array
     *
     * @throws ParseException If there is an error parsing the YAML string
     */
    public function parseString(string $config): ?array
    {
        try {
            $data = YamlParser::parse($config, YamlParser::PARSE_CONSTANT);
        } catch (Exception $exception) {
            throw new ParseException(
                [
                    'message' => 'Error parsing YAML string',
                    'exception' => $exception,
                ]
            );
        }

        return (array)$this->parse($data);
    }

    /** {@inheritDoc} */
    public function getSupportedExtensions(): array
    {
        return [Parser::YAML, Parser::YML];
    }

    /**
     * Completes parsing of YAML/YML data
     *
     * @param array $data
     */
    protected function parse(?array $data = null): ?array
    {
        return $data;
    }
}

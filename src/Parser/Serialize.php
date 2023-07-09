<?php

declare(strict_types=1);

namespace Camoo\Config\Parser;

use Camoo\Config\Enum\Parser;
use Camoo\Config\Exception\ParseException;

/**
 * Class Serialize
 */
class Serialize implements ParserInterface
{
    /** {@inheritdoc} */
    public function parseFile(string $filename): ?array
    {
        $data = file_get_contents($filename);

        return (array)$this->parse($data);
    }

    /** {@inheritdoc} */
    public function parseString(string $config): ?array
    {
        return (array)$this->parse($config);
    }

    /** {@inheritdoc} */
    public function getSupportedExtensions(): array
    {
        return [Parser::SERIALIZE, Parser::TXT];
    }

    /**
     * Completes parsing of JSON data
     *
     * @param string $data
     *
     * @throws ParseException If there is an error parsing the serialized data
     */
    protected function parse(?string $data = null): ?array
    {
        if (null === $data) {
            return null;
        }
        $serializedData = @unserialize($data);
        if ($serializedData === false) {
            throw new ParseException(error_get_last());
        }

        return $serializedData;
    }
}

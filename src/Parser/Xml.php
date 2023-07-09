<?php

declare(strict_types=1);

namespace Camoo\Config\Parser;

use Camoo\Config\Enum\Parser;
use Camoo\Config\Exception\ParseException;
use SimpleXMLElement;

/**
 * XML parser
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @author     Filip Š <projects@filips.si>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
class Xml implements ParserInterface
{
    /**
     * {@inheritDoc}
     * Parses an XML file as an array
     *
     * @throws ParseException If there is an error parsing the XML file
     */
    public function parseFile(string $filename): ?array
    {
        libxml_use_internal_errors(true);
        $data = simplexml_load_file($filename, null, LIBXML_NOERROR);

        return (array)$this->parse($data, $filename);
    }

    /**
     * {@inheritDoc}
     * Parses an XML string as an array
     *
     * @throws ParseException If there is an error parsing the XML string
     */
    public function parseString(string $config): ?array
    {
        libxml_use_internal_errors(true);
        $data = simplexml_load_string($config, null, LIBXML_NOERROR);

        return (array)$this->parse($data);
    }

    /** {@inheritDoc} */
    public function getSupportedExtensions(): array
    {
        return [Parser::XML];
    }

    /**
     * Completes parsing of XML data
     *
     * @param SimpleXMLElement|null $data
     * @param string                $filename
     *
     * @throws ParseException If there is an error parsing the XML data
     */
    protected function parse(SimpleXMLElement|bool|null $data = null, ?string $filename = null): ?array
    {
        if ($data === null) {
            return null;
        }
        if ($data === false) {
            $errors = libxml_get_errors();
            $latestError = array_pop($errors);
            $error = [
                'message' => $latestError->message,
                'type' => $latestError->level,
                'code' => $latestError->code,
                'file' => $filename,
                'line' => $latestError->line,
            ];
            throw new ParseException($error);
        }

        return json_decode(json_encode($data), true);
    }
}

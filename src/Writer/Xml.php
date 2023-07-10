<?php

declare(strict_types=1);

namespace Camoo\Config\Writer;

use Camoo\Config\Enum\Writer;
use DOMDocument;
use Exception;
use SimpleXMLElement;

/**
 * Xml Writer.
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @author     Filip Š <projects@filips.si>
 * @author     Mark de Groot <mail@markdegroot.nl>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
class Xml extends AbstractWriter
{
    /** {@inheritdoc} */
    public function toString(array $config, bool $pretty = true): string
    {
        $xml = $this->toXML($config);
        if (!$pretty) {
            return $xml;
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml);

        return $dom->saveXML();
    }

    /** {@inheritdoc} */
    public function getSupportedExtensions(): array
    {
        return [Writer::XML];
    }

    /**
     * Converts array to XML string.
     *
     * @param array                 $arr         Array to be converted
     * @param string                $rootElement I specified will be taken as root element
     * @param SimpleXMLElement|null $xml         If specified content will be appended
     *
     * @throws Exception
     *
     * @return string|bool Converted array as XML
     *
     * @see https://www.kerstner.at/2011/12/php-array-to-xml-conversion/
     */
    protected function toXML(array $arr, string $rootElement = '<config/>', ?SimpleXMLElement $xml = null): string|bool
    {
        if ($xml === null) {
            $xml = new SimpleXMLElement($rootElement);
        }
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $this->toXML($v, $k, $xml->addChild($k));
            } else {
                $xml->addChild((string)$k, (string)$v);
            }
        }

        return $xml->asXML();
    }
}

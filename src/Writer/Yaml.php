<?php

declare(strict_types=1);

namespace Camoo\Config\Writer;

use Camoo\Config\Enum\Writer;
use Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * Yaml Writer.
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
class Yaml extends AbstractWriter
{
    /**
     * {@inheritdoc}
     * Writes an array to a Yaml string.
     */
    public function toString(array $config, bool $pretty = true): string
    {
        return YamlParser::dump($config);
    }

    /** {@inheritdoc} */
    public function getSupportedExtensions(): array
    {
        return [Writer::YAML, Writer::YML];
    }
}

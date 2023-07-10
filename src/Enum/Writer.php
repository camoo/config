<?php

declare(strict_types=1);

namespace Camoo\Config\Enum;

use Camoo\Config\Writer\Ini;
use Camoo\Config\Writer\Json;
use Camoo\Config\Writer\Properties;
use Camoo\Config\Writer\Serialize;
use Camoo\Config\Writer\WriterFactoryInterface;
use Camoo\Config\Writer\WriterInterface;
use Camoo\Config\Writer\Xml;
use Camoo\Config\Writer\Yaml;

/**
 * @author     Camoo Sarl <config@camoo.sarl>
 *
 * @link       https://github.com/camoo/config
 *
 * @license    MIT
 */
enum Writer implements WriterFactoryInterface
{
    public function getInstance(): WriterInterface
    {
        return match ($this) {
            self::INI => new Ini(),
            self::JSON => new Json(),
            self::XML => new Xml(),
            self::YAML, self::YML => new Yaml(),
            self::PROPERTIES => new Properties(),
            self::SERIALIZE, self::TXT => new Serialize(),
        };
    }

    case INI;
    case JSON;
    case XML;
    case YAML;
    case PROPERTIES;
    case SERIALIZE;
    case TXT;
    case YML;
}

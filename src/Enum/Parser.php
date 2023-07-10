<?php

declare(strict_types=1);

namespace Camoo\Config\Enum;

use Camoo\Config\Parser\Ini;
use Camoo\Config\Parser\Json;
use Camoo\Config\Parser\ParserFactoryInterface;
use Camoo\Config\Parser\ParserInterface;
use Camoo\Config\Parser\Php;
use Camoo\Config\Parser\Properties;
use Camoo\Config\Parser\Serialize;
use Camoo\Config\Parser\Xml;
use Camoo\Config\Parser\Yaml;

/**
 * @author     Camoo Sarl <config@camoo.sarl>
 *
 * @link       https://github.com/camoo/config
 *
 * @license    MIT
 */
enum Parser implements ParserFactoryInterface
{
    public function getInstance(): ParserInterface
    {
        return match ($this) {
            self::PHP => new Php(),
            self::INI => new Ini(),
            self::JSON => new Json(),
            self::XML => new Xml(),
            self::YAML, self::YML => new Yaml(),
            self::PROPERTIES => new Properties(),
            self::SERIALIZE, self::TXT => new Serialize(),
        };
    }
    case PHP;
    case INI;
    case JSON;
    case XML;
    case YAML;
    case PROPERTIES;
    case SERIALIZE;
    case TXT;
    case YML;
}

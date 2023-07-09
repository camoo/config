<?php

declare(strict_types=1);

namespace Camoo\Config\Writer;

use Camoo\Config\Enum\Writer;

/**
 * Class Serialize
 */
class Serialize extends AbstractWriter
{
    /** {@inheritdoc} */
    public function toString(array $config, bool $pretty = true): string
    {
        return serialize($config);
    }

    /** {@inheritdoc} */
    public function getSupportedExtensions(): array
    {
        return [Writer::TXT, Writer::SERIALIZE];
    }
}

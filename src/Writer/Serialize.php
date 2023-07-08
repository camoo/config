<?php

declare(strict_types=1);

namespace Noodlehaus\Writer;

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
    public static function getSupportedExtensions(): array
    {
        return ['txt'];
    }
}

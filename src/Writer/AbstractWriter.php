<?php

declare(strict_types=1);

namespace Camoo\Config\Writer;

use Camoo\Config\Exception\WriteException;

/**
 * Base Writer.
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
abstract class AbstractWriter implements WriterInterface
{
    /** {@inheritdoc} */
    public function toFile(array $config, string $filename): string
    {
        $contents = $this->toString($config);
        $success = @file_put_contents($filename, $contents);
        if ($success === false) {
            throw new WriteException(['file' => $filename]);
        }

        return $contents;
    }
}

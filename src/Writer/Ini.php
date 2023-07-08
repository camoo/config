<?php

declare(strict_types=1);

namespace Noodlehaus\Writer;

/**
 * Ini Writer.
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @author     Filip Š <projects@filips.si>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
class Ini extends AbstractWriter
{
    /**
     * {@inheritdoc}
     * Writes an array to an Ini string.
     */
    public function toString(array $config, bool $pretty = true): string
    {
        return $this->toINI($config);
    }

    /** {@inheritdoc} */
    public static function getSupportedExtensions(): array
    {
        return ['ini'];
    }

    /**
     * Converts array to INI string.
     *
     * @param array $arr    Array to be converted
     * @param array $parent Parent array
     *
     * @return string Converted array as INI
     *
     * @see https://stackoverflow.com/a/17317168/6523409/
     */
    protected function toINI(array $arr, array $parent = []): string
    {
        $converted = '';

        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $sec = array_merge((array)$parent, (array)$k);
                $converted .= '[' . implode('.', $sec) . ']' . PHP_EOL;
                $converted .= $this->toINI($v, $sec);
            } else {
                $converted .= $k . '=' . $v . PHP_EOL;
            }
        }

        return $converted;
    }
}

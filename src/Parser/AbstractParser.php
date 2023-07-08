<?php

declare(strict_types=1);

namespace Noodlehaus\Parser;

/**
 * Abstract parser
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 * @author     Filip Š <projects@filips.si>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * Sets the string with configuration
     *
     * @param string $filename
     *
     * @codeCoverageIgnore
     */
    public function __construct(protected readonly string $config, ?string $filename = null)
    {
    }
}

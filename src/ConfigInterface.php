<?php

declare(strict_types=1);

namespace Camoo\Config;

/**
 * Config interface
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
interface ConfigInterface
{
    /**
     * Gets a configuration setting using a simple or nested key.
     * Nested keys are similar to JSON paths that use the dot notation
     *
     * @param mixed $default
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Function for setting configuration values, using
     * either simple or nested keys.
     */
    public function set(string $key, mixed $value): void;

    /**
     * Function for checking if configuration values exist, using
     * either simple or nested keys.
     */
    public function has(string $key): bool;

    /** Get all the configuration items */
    public function all(): array;
}

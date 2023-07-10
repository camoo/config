<?php

declare(strict_types=1);

namespace Camoo\Config;

use ArrayAccess;
use Iterator;

/**
 * Abstract Config class
 *
 * @author     Jesus A. Domingo <jesus.domingo@gmail.com>
 * @author     Hassan Khan <contact@hassankhan.me>
 *
 * @link       https://github.com/noodlehaus/config
 *
 * @license    MIT
 */
abstract class AbstractConfig implements ArrayAccess, ConfigInterface, Iterator
{
    /** Stores the configuration data */
    protected array $data = [];

    /** Caches the configuration data */
    protected array $cache = [];

    /** Constructor method and sets default options, if any */
    public function __construct(array $data)
    {
        $this->data = array_merge($this->getDefaults(), $data);
    }

    /**
     * ConfigInterface Methods
     */

    /** {@inheritDoc} */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->has($key)) {
            return $this->cache[$key];
        }

        return $default;
    }

    /** {@inheritDoc} */
    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $root = &$this->data;
        $cacheKey = '';

        // Look for the key, creating nested keys if needed
        while ($part = array_shift($segments)) {
            if ($cacheKey != '') {
                $cacheKey .= '.';
            }
            $cacheKey .= $part;
            if (!isset($root[$part]) && count($segments)) {
                $root[$part] = [];
            }
            $root = &$root[$part];

            //Unset all old nested cache
            if (isset($this->cache[$cacheKey])) {
                unset($this->cache[$cacheKey]);
            }

            //Unset all old nested cache in case of array
            if (count($segments) == 0) {
                foreach ($this->cache as $cacheLocalKey => $cacheValue) {
                    if (str_starts_with($cacheLocalKey, $cacheKey)) {
                        unset($this->cache[$cacheLocalKey]);
                    }
                }
            }
        }

        // Assign value at target node
        $this->cache[$key] = $root = $value;
    }

    /** {@inheritDoc} */
    public function has(string $key): bool
    {
        // Check if already cached
        if (isset($this->cache[$key])) {
            return true;
        }

        $segments = explode('.', $key);
        $root = $this->data;

        // nested case
        foreach ($segments as $segment) {
            if (array_key_exists($segment, $root)) {
                $root = $root[$segment];
            } else {
                return false;
            }
        }

        // Set cache for the given key
        $this->cache[$key] = $root;

        return true;
    }

    /** Merge config from another instance */
    public function merge(ConfigInterface $config): self
    {
        $this->data = array_replace_recursive($this->data, $config->all());
        $this->cache = [];

        return $this;
    }

    /** {@inheritDoc} */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * ArrayAccess Methods
     */

    /**
     * Gets a value using the offset as a key
     *
     * @param string $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Checks if a key exists
     *
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Sets a value using the offset as a key
     *
     * @param string $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Deletes a key and its value
     *
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->set($offset, null);
    }

    /**
     * Iterator Methods
     */

    /**
     * Returns the data array element referenced by its internal cursor
     *
     * @return mixed The element referenced by the data array's internal cursor.
     *               If the array is empty or there is no element at the cursor, the
     *               function returns false. If the array is undefined, the function
     *               returns null
     */
    public function current(): mixed
    {
        return !empty($this->data) ? current($this->data) : null;
    }

    /**
     * Returns the data array index referenced by its internal cursor
     *
     * @return mixed The index referenced by the data array's internal cursor.
     *               If the array is empty or undefined or there is no element at the
     *               cursor, the function returns null
     */
    public function key(): mixed
    {
        return key($this->data);
    }

    /**
     * Moves the data array's internal cursor forward one element
     *
     * @return void The element referenced by the data array's internal cursor
     *              after the move is completed. If there are no more elements in the
     *              array after the move, the function returns false. If the data array
     *              is undefined, the function returns null
     */
    public function next(): void
    {
        if (empty($this->data)) {
            return;
        }
        next($this->data);
    }

    /**
     * Moves the data array's internal cursor to the first element
     *
     * @return void
     *              The element referenced by the data array's internal cursor
     *              after the move is completed. If the data array is empty, the function
     *              returns false. If the data array is undefined, the function returns
     *              null
     */
    public function rewind(): void
    {
        if (empty($this->data)) {
            return;
        }
        reset($this->data);
    }

    /**
     * Tests whether the iterator's current index is valid
     *
     * @return bool True if the current index is valid; false otherwise
     */
    public function valid(): bool
    {
        return !empty($this->data) && key($this->data) !== null;
    }

    /** Remove a value using the offset as a key */
    public function remove(string $key): void
    {
        $this->offsetUnset($key);
    }

    /**
     * Override this method in your own subclass to provide an array of default
     * options and values
     *
     * @codeCoverageIgnore
     */
    protected function getDefaults(): array
    {
        return [];
    }
}

<?php

namespace PhpPkg\Config;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Serializable;

/**
 * Collection Interface
 */
interface CollectionInterface extends Serializable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    public function set(string $key, $value);

    public function get(string $key, $default = null);

    /**
     * @param array $items
     */
    public function replace(array $items);

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function remove(string $key): void;

    /**
     * clear all data
     */
    public function clear();
}

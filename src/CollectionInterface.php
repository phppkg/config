<?php declare(strict_types=1);

namespace PhpPkg\Config;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * Collection Interface
 */
interface CollectionInterface extends  ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    public function set(string $key, mixed $value): void;

    public function get(string $key, $default = null): mixed;

    /**
     * @param array $items
     */
    public function replace(array $items): void;

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
     * @return void
     */
    public function remove(string $key): void;

    /**
     * clear all data
     */
    public function clear(): void;
}

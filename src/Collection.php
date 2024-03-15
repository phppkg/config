<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/14
 * Time: 19:44
 */

namespace PhpPkg\Config;

use RecursiveArrayIterator;
use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Php;
use Traversable;
use function is_array;
use function is_string;
use function serialize;
use function strpos;
use function unserialize;

/**
 * Class Collection
 *
 * @package PhpPkg\Config
 *
 * 支持 链式的子节点 设置 和 值获取
 * e.g:
 * ```
 * $data = [
 *      'foo' => [
 *          'bar' => [
 *              'yoo' => 'value'
 *          ]
 *       ]
 * ];
 *
 * $config = new Collection();
 * $config->get('foo.bar.yoo')` equals to $data['foo']['bar']['yoo'];
 * ```
 */
class Collection extends \Toolkit\Stdlib\Std\Collection
{
    /**
     * @var int
     */
    public int $mergeDepth = 3;

    /**
     * The key path separator.
     *
     * @var  string
     */
    public string $keyPathSep = '.';

    /**
     * set config value by key/path
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function set(string $key, mixed $value): static
    {
        if ($this->keyPathSep && strpos($key, $this->keyPathSep) > 0) {
            Arr::setByPath($this->data, $key, $value, $this->keyPathSep);
            return $this;
        }

        return parent::set($key, $value);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->keyPathSep && strpos($key, $this->keyPathSep) > 0) {
            return Arr::getByPath($this->data, $key, $default, $this->keyPathSep);
        }

        return parent::get($key, $default);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->exists($key);
    }

    /**
     * @return string
     */
    public function getKeyPathSep(): string
    {
        return $this->keyPathSep;
    }

    /**
     * @param string $keyPathSep
     */
    public function setKeyPathSep(string $keyPathSep): void
    {
        $this->keyPathSep = $keyPathSep;
    }

    /**
     * @param iterable $data
     *
     * @return $this
     */
    public function load(iterable $data): static
    {
        $this->bindData($this->data, $data);
        return $this;
    }

    /**
     * @param iterable $data
     *
     * @return $this
     */
    public function loadData(iterable $data): static
    {
        $this->bindData($this->data, $data);
        return $this;
    }

    /**
     * @param array $parent
     * @param iterable $data
     * @param int $depth
     */
    protected function bindData(array &$parent, iterable $data, int $depth = 1): void
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value) && isset($parent[$key]) && is_array($parent[$key])) {
                if ($depth > $this->mergeDepth) {
                    $parent[$key] = $value;
                } else {
                    $this->bindData($parent[$key], $value, ++$depth);
                }
            } else {
                $parent[$key] = $value;
            }
        }
    }

    /**
     * @param string $key
     * @param class-string|object $obj
     *
     * @return object
     */
    public function bindTo(string $key, string|object $obj): object
    {
        // is class string
        if (is_string($obj)) {
            $obj = new $obj();
        }

        if ($data = $this->getArray($key)) {
            Php::initObject($obj, $data);
        }

        return $obj;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->data);
    }

    /**
     * @return RecursiveArrayIterator
     */
    public function getIterator(): Traversable
    {
        return new RecursiveArrayIterator($this->data);
    }

    /**
     * Unset an offset in the iterator.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        $this->set($offset, null);
    }

    public function __clone(): void
    {
        $this->data = unserialize(serialize($this->data), [
            'allowed_classes' => self::class
        ]);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return Php::dumpVars($this->data);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}

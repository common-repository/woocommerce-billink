<?php

namespace Tussendoor\Billink\Helpers;

use Closure;
use Countable;
use ArrayIterator;
use IteratorAggregate;

class Collection implements IteratorAggregate, Countable
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Add (collect) an array of items into a new collection.
     * @param  array  $items
     * @return static
     */
    public static function collect(array $items)
    {
        return new static($items);
    }

    /**
     * Return all items
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Check if the given name exists as key in the items.
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->items[$name]);
    }

    /**
     * Get a value from the items by it's key. Supply a default value for
     * when the given key is not found.
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->has($name) ? $this->items[$name] : $default;
    }

    /**
     * Return the first value in the item list.
     * @return mixed
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * Return the last value in the item list.
     * @return mixed
     */
    public function last()
    {
        return end($this->items);
    }

    /**
     * Count all items.
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Check if the collection is considered empty.
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Check if the collection is not empty.
     * @return bool
     */
    public function isNotEmpty()
    {
        return $this->isEmpty() === false;
    }

    /**
     * Remove (pull) an item from the collection
     * @param  string $name
     * @return $this
     */
    public function pull($name)
    {
        unset($this->items[$name]);

        return $this;
    }

    /**
     * Append an item to the end of the collection. Creates a new numeric key.
     * @param  mixed $value
     * @return $this
     */
    public function append($value)
    {
        $this->items[] = $value;

        return $this;
    }

    /**
     * Push an item into the collection. The name becones the key. If an value
     * exists in the Collection with the same name, it's overwritten.
     * @param  string $name
     * @param  mixed  $value
     * @return $this
     */
    public function push($name, $value)
    {
        $this->items[$name] = $value;

        return $this;
    }

    /**
     * Replace an value in the collection. Alias for the push() method.
     * @param  string $name
     * @param  mixed  $value
     * @return $this
     */
    public function replace($name, $value)
    {
        return $this->push($name, $value);
    }

    /**
     * Set an item in the collection. Alias for the push() method.
     * @param [type] $name  [description]
     * @param [type] $value [description]
     */
    public function set($name, $value)
    {
        return $this->push($name, $value);
    }

    /**
     * Apply a callback to all items. The resulting array is set as the item stack.
     * @param  Closure $callback
     * @return $this
     */
    public function map(Closure $callback)
    {
        $this->items = array_map($callback, $this->items);

        return $this;
    }

    /**
     * 'Walk' over all items and apply the given callback. The result is set as the
     * item stack. The closure should return an array: [$itemName, $value].
     * @param  Closure $callback
     * @return $this
     */
    public function walk(Closure $callback)
    {
        foreach ($this->items as $name => $value) {
            list($newName, $newValue) = $callback($name, $value);
            $this->pull($name)->push($newName, $newValue);
        }

        return $this;
    }

    /**
     * Filter the items by applying a callback. If it returns false, the item is removed.
     * @param  Closure $callback
     * @param  int     $flag     @see PHPDocs array_filter
     * @return $this
     */
    public function filter(Closure $callback, $flag = 0)
    {
        $this->items = array_filter($this->items, $callback, $flag);

        return $this;
    }

    /**
     * Reduce the collection and return the result. Does not return the collection.
     * @param  Closure $callback
     * @param  mixed   $default
     * @return mixed
     */
    public function reduce(Closure $callback, $default = null)
    {
        return array_reduce($this->items, $callback, $default);
    }

    /**
     * Create a new ArrayIterator so this collection works with foreach() calls.
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}

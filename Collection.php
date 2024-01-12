<?php


class Collection
{
    private $items;

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function where($key, $operator, $value)
    {
        return new static(array_filter($this->items, function ($item) use ($key, $operator, $value) {
            switch ($operator) {
                case '=':
                    return $item[$key] == $value;
                case '>':
                    return $item[$key] > $value;
                case '<':
                    return $item[$key] < $value;
                // Add more operators as needed
                default:
                    return false;
            }
        }));
    }

    public function whereNotNull($key)
    {
        return new static(array_filter($this->items, function ($item) use ($key) {
            return isset($item[$key]) && !is_null($item[$key]);
        }));
    }

    public function whereNull($key)
    {
        return new static(array_filter($this->items, function ($item) use ($key) {
            return !isset($item[$key]) || is_null($item[$key]);
        }));
    }

    public function first()
    {
        return reset($this->items);
    }

    public function last()
    {
        return end($this->items);
    }

    public function push($item)
    {
        $this->items[] = $item;
    }

    public function pull($key)
    {
        $value = $this->items[$key];
        unset($this->items[$key]);
        return $value;
    }

    public function shift()
    {
        return array_shift($this->items);
    }

    public function unshift($item)
    {
        array_unshift($this->items, $item);
    }

    public function map($callback)
    {
        return new static(array_map($callback, $this->items));
    }

    public function filter($callback)
    {
        return new static(array_filter($this->items, $callback));
    }

    public function each($callback)
    {
        foreach ($this->items as $key => $item) {
            $callback($item, $key);
        }
        return $this;
    }

    public function get()
    {
        return $this->items;
    }

    public function whereIn($key, $values)
    {
        return new static(array_filter($this->items, function ($item) use ($key, $values) {
            return in_array($item[$key], $values);
        }));
    }

    public function pluck($column)
    {
        return array_map(function ($item) use ($column) {
            return $item[$column];
        }, $this->items);
    }

    public function toArray()
    {
        return $this->items;
    }
}

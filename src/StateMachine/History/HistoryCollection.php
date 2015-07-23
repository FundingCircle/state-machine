<?php

namespace StateMachine\History;

class HistoryCollection implements \Countable
{
    /**
     * An array containing the entries of this collection.
     *
     * @var array
     */
    private $elements;

    /**
     * Initializes a new ArrayCollection.
     *
     * @param array $elements
     */
    public function __construct(array $elements = array())
    {
        $this->elements = $elements;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->elements;
    }

    /**
     * @return History
     */
    public function first()
    {
        return reset($this->elements);
    }

    /**
     * @return History
     */
    public function last()
    {
        return end($this->elements);
    }

    /**
     * @param History $value
     */
    public function add(History $value)
    {
        $this->elements[] = $value;
    }
}

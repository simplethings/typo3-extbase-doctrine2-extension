<?php

class Tx_Doctrine2_QueryResult implements Tx_Extbase_Persistence_QueryResultInterface
{
    /**
     * @var array
     * @FLOW3\Transient
     */
    protected $rows;

    /**
     * @var Tx_Doctrine2_Query
     */
    protected $query;

    /**
     * @param array $rows
     * @param Tx_Doctrine2_Query $query
     */
    public function __construct(Tx_Doctrine2_Query $query)
    {
        $this->query = $query;
    }

    /**
     * Loads the objects this QueryResult is supposed to hold
     *
     * @return void
     */
    protected function initialize()
    {
        if (!is_array($this->rows)) {
            $this->rows = $this->query->getResult();
        }
    }

    /**
     * Returns a clone of the query object
     *
     * @return Tx_Doctrine2_Query
     * @api
     */
    public function getQuery()
    {
        return clone $this->query;
    }

    /**
     * Returns the first object in the result set
     *
     * @return object
     * @api
     */
    public function getFirst()
    {
        if (is_array($this->rows)) {
            $rows = &$this->rows;
        } else {
            $query = clone $this->query;
            $query->setLimit(1);
            $rows = $query->getResult();
        }

        return (isset($rows[0])) ? $rows[0] : NULL;
    }

    /**
     * Returns the number of objects in the result
     *
     * @return integer The number of matching objects
     * @api
     */
    public function count()
    {
        return $this->query->count();
    }

    /**
     * Returns an array with the objects in the result set
     *
     * @return array
     * @api
     */
    public function toArray()
    {
        $this->initialize();
        return $this->rows;
    }

    /**
     * This method is needed to implement the \ArrayAccess interface,
     * but it isn't very useful as the offset has to be an integer
     *
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $this->initialize();
        return isset($this->rows[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $this->initialize();
        return isset($this->rows[$offset]) ? $this->rows[$offset] : NULL;
    }

    /**
     * This method has no effect on the persisted objects but only on the result set
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->initialize();
        $this->rows[$offset] = $value;
    }

    /**
     * This method has no effect on the persisted objects but only on the result set
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->initialize();
        unset($this->rows[$offset]);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $this->initialize();
        return current($this->rows);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        $this->initialize();
        return key($this->rows);
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->initialize();
        next($this->rows);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->initialize();
        reset($this->rows);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        $this->initialize();
        return current($this->rows) !== FALSE;
    }
}


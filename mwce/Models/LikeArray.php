<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 25.08.2017
 *
 **/

namespace mwce\Models;

class LikeArray implements \ArrayAccess, \Iterator
{

    /**
     * @var array
     * значения по перечню полей
     */
    protected $object;

    /**
     * @var int
     */
    protected $pos = 0;

    /**
     * @var array
     * перечень полей
     */
    protected $fields;

    /**
     * @return mixed|string
     */
    public function current()
    {
        if (isset($this->fields[$this->pos])) {
            return isset($this->object[$this->fields[$this->pos]]) ? $this->object[$this->fields[$this->pos]] : '';
        }
        return '';
    }

    public function next(): void
    {
        ++$this->pos;
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->fields[$this->pos];
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        if (isset($this->fields[$this->pos])) {
            return isset($this->object[$this->fields[$this->pos]]) || null === $this->object[$this->fields[$this->pos]] ? true : false;
        }

        return false;
    }

    public function rewind(): void
    {
        $this->pos = 0;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if (isset($this->object[$offset])) {
            return true;
        }
        return false;
    }

    /**
     * @param mixed $offset
     * @return bool|mixed
     */
    public function offsetGet($offset)
    {
        if (isset($this->object[$offset])) {
            return $this->object[$offset];
        }
        return false;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->_adding($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->object[$offset]);
    }


    /**
     * универальный метод добавления в модель данных
     * @param $name mixed
     * @param $value mixed
     */
    protected function _adding($name, $value)
    {
        if (!isset($this->object[$name])) {
            $this->fields[] = $name;
        }

        $this->object[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool|mixed
     */
    public function __get($name)
    {

        if (isset($this->object[$name])) {
            return $this->object[$name];
        }
        return false;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_adding($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->object[$name]);
    }
}
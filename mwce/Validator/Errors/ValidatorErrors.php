<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Errors;


class ValidatorErrors extends \Exception implements \Countable, \ArrayAccess, \Iterator
{
    protected $errors = array();
    protected $num = 0;

    /**
     * @param \Exception $e
     */
    public function Add(\Exception $e){
        $this->errors[] = $e;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return \count($this->errors);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->errors[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->errors[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) : void
    {
        $this->errors[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) : void
    {
        if(isset($this->errors[$offset])){
            unset($this->errors[$offset]);
        }
    }

    public function current()
    {
        if (isset($this->errors[$this->num])) {
            return $this->errors[$this->num];
        }
        return '';
    }

    public function next() : void
    {
        ++$this->num;
    }

    public function key()
    {
        return $this->num;
    }

    public function valid() : bool
    {
        return isset($this->errors[$this->num]);
    }

    public function rewind() : void
    {
        $this->num = 0;
    }
}
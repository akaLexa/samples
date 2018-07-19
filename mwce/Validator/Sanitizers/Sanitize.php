<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 30.03.2018
 *
 **/

namespace mwce\Validator\Sanitizers;
abstract class Sanitize
{
    /**
     * @param $value
     * @return mixed
     */
    abstract public function sanitize($value);

    /**
     * @param $value
     * @return mixed
     */
    final public function __invoke($value)
    {
        return $this->sanitize($value);
    }
}
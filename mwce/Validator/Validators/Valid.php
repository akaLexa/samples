<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 30.03.2018
 *
 **/

namespace mwce\Validator\Validators;

abstract class Valid
{
    /**
     * @param $value
     * @return bool
     */
    abstract public function validate($value) : bool;

    /**
     * @param $value
     * @return bool
     */
    final public function __invoke($value) : bool
    {
        return $this->validate($value);
    }
}
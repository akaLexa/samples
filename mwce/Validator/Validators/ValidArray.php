<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Validators;

class ValidArray extends Valid
{
    /**
     * @param $value
     * @return bool
     */
    public function validate($value): bool
    {
        return \is_array($value) || (\is_object($value) && $value instanceof \ArrayAccess);
    }
}
<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Validators;

class ValidBoolean extends Valid
{
    /**
     * @param $value
     * @return bool
     */
    public function validate($value): bool
    {
        return \is_bool($value);
    }
}
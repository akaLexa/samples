<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Validators;

class ValidInt extends Valid
{

    /**
     * @param $value
     * @return bool
     */
    public function validate($value): bool
    {

        if (\is_string($value) && is_numeric($value)) {
            $value = (int)$value;
        }

        return !(false === filter_var($value, \FILTER_VALIDATE_INT));
    }
}
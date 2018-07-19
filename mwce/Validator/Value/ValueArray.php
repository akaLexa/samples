<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Value;

use mwce\Validator\Errors\ArrayNotAnArrayError;
use mwce\Validator\Sanitizers\SanitizeArray;
use mwce\Validator\Validators\ValidInt;

class ValueArray extends Value
{

    /**
     * ValueInt constructor.
     * @param $value
     * @param null $legend
     * @throws ArrayNotAnArrayError
     */
    public function __construct($value,$legend = null)
    {
        if((new ValidInt)($value)){
            $this->value = (new SanitizeArray)($value);
        }
        else{
            throw new ArrayNotAnArrayError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
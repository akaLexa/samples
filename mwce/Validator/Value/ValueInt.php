<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Value;

use mwce\Validator\Errors\IntNotAnIntError;
use mwce\Validator\Sanitizers\SanitizeInt;
use mwce\Validator\Validators\ValidInt;

class ValueInt extends Value
{

    /**
     * ValueInt constructor.
     * @param $value
     * @param null $legend
     * @throws IntNotAnIntError
     */
    public function __construct($value,$legend = null)
    {
        if((new ValidInt)($value)){
            $this->value = (new SanitizeInt)($value);
        }
        else{
            throw new IntNotAnIntError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Value;

use mwce\Validator\Errors\BoolNotABooleanError;
use mwce\Validator\Sanitizers\SanitizeBoolean;
use mwce\Validator\Validators\ValidInt;

class ValueBool extends Value
{

    /**
     * ValueInt constructor.
     * @param $value
     * @param null $legend
     * @throws BoolNotABooleanError
     */
    public function __construct($value,$legend = null)
    {
        if((new ValidInt)($value)){
            $this->value = (new SanitizeBoolean)($value);
        }
        else{
            throw new BoolNotABooleanError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
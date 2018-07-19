<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Value;

use mwce\Validator\Errors\FloatNotAFloatError;
use mwce\Validator\Sanitizers\SanitizeFloat;
use mwce\Validator\Validators\ValidFloat;

class ValueFloat extends Value
{
    /**
     * ValueInt constructor.
     * @param $value
     * @param null $legend
     * @throws FloatNotAFloatError
     */
    public function __construct($value,$legend = null)
    {
        if((new ValidFloat)($value)){
            $this->value = (new SanitizeFloat)($value);
        }
        else{
            throw new FloatNotAFloatError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
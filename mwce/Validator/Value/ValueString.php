<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Value;

use mwce\Validator\Errors\StrNotAStringError;
use mwce\Validator\Sanitizers\SanitizeString;
use mwce\Validator\Validators\ValidString;

class ValueString extends Value
{

    /**
     * ValueInt constructor.
     * @param $value
     * @param null $legend
     * @throws StrNotAStringError
     */
    public function __construct($value,$legend = null)
    {
        if(empty($value)){
            throw new StrNotAStringError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }

        if((new ValidString)($value)){
            $this->value = (new SanitizeString)($value);
        }
        else{
            throw new StrNotAStringError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
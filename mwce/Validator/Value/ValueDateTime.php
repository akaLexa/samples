<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Value;

use mwce\Validator\Errors\DateNotADateError;
use mwce\Validator\Sanitizers\SanitizeDateTime;
use mwce\Validator\Validators\ValidDateTime;

class ValueDateTime extends Value
{
    /**
     * ValueDate constructor.
     * @param $value
     * @param null $legend
     * @throws DateNotADateError
     */
    public function __construct($value,$legend = null)
    {
        if(empty($value)){
            throw new DateNotADateError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }

        if((new ValidDateTime)($value)){
            $this->value = (new SanitizeDateTime)($value);
        }
        else{
            throw new DateNotADateError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }
    }


    public function jsonSerialize()
    {
        return $this->value;
    }
}
<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Value;

//use mwce\Validator\Errors\HtmlStringHasBadWordError;
use mwce\Validator\Errors\StrNotAStringError;
use mwce\Validator\Sanitizers\SanitizeHtmlString;

class ValueHtmlString extends Value
{

    /**
     * ValueInt constructor.
     * @param $value
     * @param null $legend
     * @throws StrNotAStringError
     */
    public function __construct($value,$legend = null)
    {
        if(empty(trim($value))){
            throw new StrNotAStringError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }
        //if((new ValidHtmlString)($value)){
            $this->value = (new SanitizeHtmlString)($value);
        //}
       /* else{
            throw new HtmlStringHasBadWordError($legend ?? $value,htmlspecialchars($value,ENT_QUOTES));
        }*/
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
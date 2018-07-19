<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 30.03.2018
 *
 **/

namespace mwce\Validator\Errors;

use Throwable;

class HtmlStringHasBadWordError extends ValidatorError
{
    public function __construct($value, string $message = '', int $code = 1006, Throwable $previous = null)
    {
        parent::__construct($value, $message, $code, $previous);
    }
}
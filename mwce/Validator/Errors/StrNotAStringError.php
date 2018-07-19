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

class StrNotAStringError extends ValidatorError
{
    public function __construct($value, string $message = '', int $code = 1005, Throwable $previous = null)
    {
        parent::__construct($value, $message, $code, $previous);
    }
}
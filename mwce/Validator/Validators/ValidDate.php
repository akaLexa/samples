<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Validators;

class ValidDate extends Valid
{
    /**
     * @param $value
     * @return bool
     */
    public function validate($value): bool
    {
        if ($value instanceof \DateTimeInterface) {
            return true;
        }

        try{
            $dt = new \DateTime($value);
            return true;
        }
        catch (\Exception $e){
            return false;
        }
        //return (string)\date('Y-m-d', strtotime($value)) === (string)$value;
        //return \DateTime::createFromFormat('Y-m-d', $value) !== FALSE;
    }
}
<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Sanitizers;

class SanitizeDateTime extends Sanitize
{

    /**
     * @param $value
     * @return mixed
     */
    public function sanitize($value)
    {
        if(empty($value)){
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return (string)$value->format('Y-m-d H:i:s');
        }

        $dt = new \DateTime($value);
        return (string)$dt->format('Y-m-d H:i:s');
    }
}
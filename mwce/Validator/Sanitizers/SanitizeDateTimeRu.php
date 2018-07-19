<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Sanitizers;

class SanitizeDateTimeRu extends Sanitize
{

    /**
     * @param $value
     * @return mixed
     */
    public function sanitize($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d-m-Y H:i:s');
        }

        if(empty($value) || $value === ''){
            return null;
        }

        $dt = new \DateTime($value);
        return $dt->format('d-m-Y H:i:s');
    }
}
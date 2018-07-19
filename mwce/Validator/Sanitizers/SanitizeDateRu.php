<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Sanitizers;

class SanitizeDateRu extends Sanitize
{
    /**
     * @param $value
     * @return mixed
     */
    public function sanitize($value)
    {
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('d-m-Y');
        }

        if(empty($value) || $value === ''){
            return null;
        }

        return (new \DateTime($value))->format('d-m-Y');
    }
}
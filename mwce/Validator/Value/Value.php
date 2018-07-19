<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 30.03.2018
 *
 **/

namespace mwce\Validator\Value;

abstract class Value implements \JsonSerializable
{
    protected $value;

    public function getValue(){
        return $this->value;
    }
}
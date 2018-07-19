<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Validators;

class ValidHtmlString extends Valid
{

    /**
     * @param $value
     * @return bool
     */
    public function validate($value): bool
    {
        return (bool)\preg_match("!<script[^>]*>|</script>|<(\s{0,})iframe(\s{0,})>|</(\s{0,})iframe(\s{0,})>!isU",$value);
    }
}
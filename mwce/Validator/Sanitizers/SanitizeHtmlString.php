<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Sanitizers;

class SanitizeHtmlString extends Sanitize
{

    /**
     * @param $value
     * @return mixed
     */
    public function sanitize($value)
    {
        return htmlspecialchars(preg_replace("!<script[^>]*>|</script>|<(\s{0,})iframe(\s{0,})>|</(\s{0,})iframe(\s{0,})>!isU", ' !removed bad word! ', $value), ENT_QUOTES);
    }
}
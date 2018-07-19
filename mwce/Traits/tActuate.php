<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * 24.12.2016
 *
 **/

namespace mwce\Traits;


use mwce\Tools\Date;

trait tActuate
{
    /**
     * @param array $array
     * актуализирует текущую модель
     */
    public function setActuate(array $array): void
    {
        if (!empty($array) && \is_array($array)) {
            foreach ($array as $id => $value) {

                $_t = strtolower(trim($value));
                if($_t === 'null'){
                    $value = null;
                }
                else if($_t === 'now()'){
                    $value = Date::intransDate('now',true);
                }
                else if (strpos($value,'fn_' ) === 0 || strpos($value,'f_' ) === 0){
                    continue;
                }

                $this->_adding($id,$value);
            }
        }
    }

}
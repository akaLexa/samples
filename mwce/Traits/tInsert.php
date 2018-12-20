<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * 24.12.2016
 *
 **/

namespace mwce\Traits;


trait tInsert
{
    /**
     * возвращает сгененированный кусок SQL кода для простого интерта в базу данных
     * где $array является ассоциативным и ключ = название столбца
     * @param array $array
     * @return string
     */
    private static function genInsert($array): string
    {
        $genQpice = '';
        if (!empty($array) && \is_array($array)) {

            $left = '';
            $right = '';

            foreach ($array as $id => $value) {
                if (!empty($left)) {
                    $left .= ',';
                }

                if (!empty($right)) {
                    $right .= ',';
                }

                $_t = strtolower(trim($value));
                if ($_t !== 'null' && $_t !== 'now()' && strpos($value,'fn_' ) !== 0 && strpos($value,'f_' ) !== 0) {
                    $value = "'$value'";
                }

                $left .= " $id";
                $right .= " $value";
            }

            if (!empty($left) && !empty($right)) {
                $genQpice = "($left) VALUE ($right)";
            }
        }

        return $genQpice;
    }
}
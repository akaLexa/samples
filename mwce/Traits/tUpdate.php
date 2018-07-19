<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * 24.12.2016
 *
 **/

namespace mwce\Traits;


trait tUpdate
{
    /**
     * генерирует строку как часть запроса на update Таблицы
     * где $array является ассоциативным и ключ = название столбца
     * @param array $array
     * @return string
     */
    public function genUpdate($array): string
    {
        $genQpice = '';
        if (!empty($array) && \is_array($array)) {
            foreach ($array as $id => $value) {
                if (!empty($genQpice)) {
                    $genQpice .= ',';
                }

                $_t = strtolower(trim($value));
                if ($_t !== 'null' && $_t !== 'now()' && strpos($value,'fn_' ) !== 0 && strpos($value,'f_' ) !== 0) {
                    $value = "'$value'";
                }

                $genQpice .= "$id = $value";
            }
        }

        return $genQpice;
    }

    /**
     * генерирует строку как часть запроса на update Таблицы
     * где $array является ассоциативным и ключ = название столбца
     * @param array $array
     * @return string
     */
    public static function genUpdateSt($array): string
    {
        $genQpice = '';
        if (!empty($array) && \is_array($array)) {
            foreach ($array as $id => $value) {
                if (!empty($genQpice)) {
                    $genQpice .= ',';
                }

                $_t = strtolower(trim($value));
                if ($_t !== 'null' && $_t !== 'now()' && strpos($value,'fn_' ) !== 0 && strpos($value,'f_' ) !== 0) {
                    $value = "'$value'";
                }

                $genQpice .= "$id = $value";
            }
        }

        return $genQpice;
    }
}
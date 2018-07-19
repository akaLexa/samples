<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 16.11.2015
 * работа с датой
 **/
namespace mwce\Tools;
class Date
{
    /**
     * @param string $date
     * @param bool|false $isDateTime
     * @return bool|string
     * конвертация даты из бд в человекопонятную дату
     */
    public static function transDate($date = '0000-00-00', $isDateTime = false)
    {
        if (empty($date)) {
            return '';
        }

        if (NULL === $date || '1970-01-01 00:00:00' === $date || '1970-01-01' === $date || trim($date) === '0000-00-00') {
            return '-/-';
        }

        if (!$isDateTime) {
            return date('d-m-Y', strtotime($date));
        }

        return date('d-m-Y H:i', strtotime($date));
    }

    /**
     * @param string $date
     * @param bool $isDateTime
     * @return string
     * конвертация даты в дату, пригодную для бд(смена формата даты))
     */
    public static function intransDate($date, $isDateTime = false)
    {
        if ($date === NULL) {
            return '-/-';
        }
        if (!$isDateTime) {
            return date('Y-m-d', strtotime($date));
        }
        return date('Y-m-d H:i:s', strtotime($date));
    }

    /**
     * @param string $a
     * @param string $b - вычитаемое
     * @param bool|false $type true - разница в днях, false - в часах
     * @return int|bool
     * узнать разницу между датами
     */
    public static function dateDif($a, $b, $type = false)
    {
        $a_ = strtotime($a);
        $b_ = strtotime($b);

        if($a_ === false || $b_ === false) {
            return false;
        }

        $c = !$type ? floor(($a_ - $b_) / 86400) : floor(($a_ - $b_) / 3600);

        return (int)$c;
    }

    public static function getMonth($isCurMonth = true): array
    {
        $months = [
            1 => 'Январь',
            2 => 'Ферваль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь'
        ];

        if ($isCurMonth) {
            $m = date('n');
            if (!empty($months[$m])) {
                $months[0] = $m;
            }
        }

        return $months;
    }

    /**
     * возвращает массив с годаме от $from до $to, если
     * $isCur = true, то в $years[0] будет текущий год
     * @param bool $isCur
     * @param int $from
     * @param int $to
     * @return array
     */
    public static function getYear($isCur = true, $from = 2015, $to = 2020): array
    {
        $years = [];

        for ($i = $from; $i <= $to; $i++) {
            $years[$i] = $i;
        }

        if ($isCur) {
            $m = date('Y');
            if (!empty($years[$m])) {
                $years[0] = $m;
            }
        }
        return $years;
    }
}
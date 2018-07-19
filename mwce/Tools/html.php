<?php

namespace mwce\Tools;
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 21.10.2015
 *
 **/
class html
{
    /**
     * @param array $args - массив с данными для заполнения элемента
     * @param mixed $chosen - какой элемент должен быть выбран по умолчанию
     * @param string $name - название и id элемента
     * @param array|mixed $others - любые html-атрибуты элемента.
     * либо в виде строки: "style='width:12px;' onchange='alert(123);'",
     * либо в виде ассоциативного массива: array("style"=>"width:12px;","onchange"=>"alert(123);")
     * @return string - html код элемента select
     */
    public static function select($args, $name, $chosen = -1, $others = ''): string
    {
        if (\is_array($others)) {
            $htmAttr = '';
            /**
             * @var $others array
             * @var  $id string
             * @var  $val string
             */
            foreach ($others as $id => $val) {
                $htmAttr .= " $id=\"$val\"";
            }
        }
        else {
            $htmAttr = $others;
        }

        $text = "<select name=\"{$name}\" id=\"{$name}\" {$htmAttr}>";
        $wassel = 0;

        if (!empty($args) && \is_array($args)) {
            foreach ($args as $id => $val) {
                $text .= "<option value=\"$id\"";
                if ($chosen === $id && $wassel === 0) {
                    $text .= ' SELECTED ';
                    $wassel = 1;
                }
                $text .= ">$val</option>";
            }
        }
        $text .= '</select>';
        return $text;
    }

    /**
     * список с группировками
     * @param array $args [group]=>[id]=>[legend]
     * @param string $name
     * @param int|string $chosen
     * @param mixed|array $others
     * @return string
     */
    public static function groupSelect($args, $name, $chosen = -1, $others = ''): string
    {

        if (\is_array($others)) {
            $htmAttr = '';
            foreach ($others as $id => $val) {
                $htmAttr .= " $id=\"$val\"";
            }
        }
        else {
            $htmAttr = $others;
        }

        $text = "<select name=\"{$name}\" id=\"{$name}\" {$htmAttr}>";
        $wassel = 0;

        if (!empty($args) && \is_array($args)) {
            foreach ($args as $group => $arg) {
                if (!\is_array($arg)) {
                    $text .= '<option value="0" ';
                    if ($chosen === $group) {
                        $text .= ' SELECTED ';
                    }

                    $text .= " >$arg</option>";
                    $wassel = 1;
                    continue;
                }

                $text .= "<optgroup label='$group'>";
                foreach ($arg as $id => $val) {
                    $text .= "<option value=\"$id\"";
                    if ($chosen === $id && $wassel === 0) {
                        $text .= ' SELECTED ';
                        $wassel = 1;
                    }
                    $text .= ">$val</option>";
                }
                $text .= '</optgroup>';
            }

        }
        $text .= '</select>';
        return $text;
    }

    /**
     * Группа чекбоксов
     * @param array $chArray массив со значениями
     * [0] => [
     *    [value] = ... ,
     *    [isChecked] = true/false,
     *    [legend] = ... ,
     *    [params] = array|string - css,js, html атрибуты для каждого checkBox
     *    [span] = array|string - css,js, html атрибуты для каждого span
     *    [label] = array|string - css,js, html атрибуты для каждого span
     * ]
     * @return string html code
     */
    public static function checkBoxGroup($chArray): string
    {
        $html = '';
        if (!empty($chArray)) {
            foreach ($chArray as $checkBox) {
                $html .= '<label';
                if (!empty($checkBox['label'])) {
                    if (\is_array($checkBox['label'])) {
                        foreach ($checkBox['label'] as $name => $var) {
                            $html .= " $name = \"$var\"";
                        }
                    }
                    else {
                        $html .= ' ' . $checkBox['label'];
                    }
                }

                $html .= ' >';

                if (!empty($checkBox['legend'])) {
                    $html .= '<span ';
                    if (!empty($checkBox['span'])) {
                        if (\is_array($checkBox['span'])) {
                            foreach ($checkBox['span'] as $name => $var) {
                                $html .= " $name = \"$var\"";
                            }
                        }
                        else {
                            $html .= $checkBox['span'];
                        }
                    }

                    $html .= '>' . $checkBox['legend'] . '</span>';
                }

                $html .= '<input type="checkbox" ';

                if (isset($checkBox['value'])) {
                    $html .= ' value="' . $checkBox['value'] . '" ';
                }

                if (!empty($checkBox['isChecked']) && $checkBox['isChecked'] === true) {
                    $html .= ' checked ';
                }

                if (!empty($checkBox['span'])) {
                    if (\is_array($checkBox['params'])) {
                        foreach ($checkBox['params'] as $name => $var) {
                            $html .= " $name = \"$var\"";
                        }
                    }
                    else {
                        $html .= ' ' . $checkBox['params'];
                    }
                }

                $html .= ' >';

                $html .= '</label>';
            }
        }
        return $html;
    }

    /**
     * генерация text
     * @param string $name
     * @param mixed $value
     * @param mixed|array $others
     * @return string
     */
    public static function inputText($name, $value, $others = ''): string
    {
        if (\is_array($others)) {
            $htmAttr = '';
            /**
             * @var $others array
             * @var  $id string
             * @var  $val string
             */
            foreach ($others as $id => $val) {
                $htmAttr .= " $id=\"$val\"";
            }
        }
        else {
            $htmAttr = $others;
        }
        return "<input type='text' name='$name' id='$name' value='$value' $htmAttr>";
    }

    /**
     * html элемент input
     * @param string $type text,tel,checkbox, etc тип
     * @param string $name имя/ид
     * @param int|mixed $value значение
     * @param mixed|array $other класс, стиль и все что не указано выше
     * @return string
     */
    public static function input($type, $name, $value = 0, $other = ''): string
    {
        if (\is_array($other)) {
            $htmAttr = '';

            foreach ($other as $id => $val) {
                $htmAttr .= " $id=\"$val\"";
            }
        }
        else {
            $htmAttr = $other;
        }
        return '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" value="' . $value . '" ' . $htmAttr . '>';
    }

    /**
     * группа чекбоксов
     * @param string $name название для каждого чекбокса(выведет $name_н, где н- кол-во элементов, начиная с 0)
     * @param array $values массив значений и надписей для чекбоксов [легенда , значение , bool checked]
     * @param string $styles стили для дива, в котором чекбоксы
     * @return string html
     */
    public static function checkGroup($name, $values, $styles): string
    {
        if (!\is_array($values)) {
            return 'values must be an array["legend"=>value]';
        }

        $ret = "<div $styles>";
        $i = 0;
        foreach ($values as $item) {
            $ret .= " <label class='mwceCustomCheckGroup'><span class='mwceCustomCheckGroupSpan'>{$item[0]}</span> <input type='checkbox' name='{$name}_{$i}' value='{$item[1]}'";
            if ((bool)$item[2] === true) {
                $ret .= ' checked ';
            }
            $ret .= '> </label>';
            $i++;
        }

        return $ret . '</div>';
    }

    /**
     * @param string $name
     * @param mixed|string $value
     * @param mixed|array $other
     * @return string
     */
    public static function textArea($name,$value,$other) :string {
        if (\is_array($other)) {
            $htmAttr = '';
            /**
             * @var $others array
             * @var  $id string
             * @var  $val string
             */
            foreach ($other as $id => $val) {
                $htmAttr .= " $id=\"$val\"";
            }
        }
        else {
            $htmAttr = $other;
        }

        return '<textarea name="'.$name.'" id="'.$name.'" '.$htmAttr.'>'.$value.'</textarea>';
    }
}
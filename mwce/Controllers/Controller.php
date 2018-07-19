<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 09.04.2016
 *
 **/
namespace mwce\Controllers;
use mwce\Tools\Content;
use mwce\Tools\Date;
use mwce\Tools\Logs;
use mwce\Tools\Tools;

class Controller
{

    //region определение констант типов для валидации
    public const INT = 'int';
    public const FLOAT = 'float';
    public const STR = 'str';
    public const NOVALID = 'not';
    public const BOOL = 'bool';
    public const _ARRAY = 'array';
    public const DATE = 'date';
    public const DATETIME = 'datetime';
    //endregion

    /**
     * @var Tools
     * инстанс класса модели
     */
    protected $model;

    /**
     * @var Content
     * инстанс класса шаблонизатора
     */
    protected $view;

    /**
     * @var array
     * поля для валидации из POST - массива
     */
    protected $postField;
    /**
     * короткий формат ввода параметров проверяемых значений
     * 0 => type 1=>maxLength
     * @var bool
     */
    protected $shortFormat = false;

    /**
     * @var array
     * поля для валидации из GET - массива
     */
    protected $getField;

    /**
     * @var bool
     * проверять или нет пост и гет массивы
     */
    protected $needValid = true;

    /**
     * @var array
     * конфигурации к модулю, если есть
     */
    protected $configs = array();

    /**
     * @var bool
     * использование логов при ошибках в валидации
     */
    protected $useLogs = true;

    /**
     * @var string
     * название класса без неймспейсов
     */
    protected $className;

    /**
     * @var array
     */
    protected static $checkedPOST = [];

    /**
     * @var array
     */
    protected static $checkedGET = [];

    /**
     * реальный вызов экшена
     * @param $action string
     */
    public function action($action): void
    {
        $this->init();
        $this->$action();
        $this->callback();
    }

    /**
     * показывает ошибку по номеру
     * @param int $er номер ошибки
     */
    public function showError($er = 2): void{
        $this->view->error($er);
    }

    /**
     * метод, что запускается срау после констуктора
     */
    public function init(): void{}

    /**
     * метод, запускается после экшена
     */
    public function callback(): void {}

    /**
     * метод по умолчанию
     */
    public function actionIndex(){}

    /**
     * GET массив. валидация
     */
    protected function clearGet(): void
    {
        if (empty($_GET)) {
            return;
        }

        $ai = new \ArrayIterator($_GET);
        foreach ($ai as $id => $v) {

            if (empty(trim($_GET[$id]))) {
                unset($_GET[$id]);
                continue;
            }

            if(!empty(self::$checkedGET[$id]) && self::$checkedGET[$id] === true){
                continue;
            }

            $v = trim(htmlspecialchars($v, ENT_QUOTES));
            $v = preg_replace("/(\&lt\;br \/\&gt\;)|(\&lt\;br\&gt\;)/", ' <br /> ', $v);

            if ($_GET[$id] != $v && $this->useLogs) {
                Logs::log(7, "GET -> {$_GET[$id]} != {$v}");
            }

            $_GET[$id] = $v;
            self::$checkedGET[$id] = true;
        }
    }

    /**
     * POST массив. валидация
     */
    protected function clearPost(): void
    {
        if (empty($_POST)) {
            return;
        }
        $ai = new \ArrayIterator($_POST);

        foreach ($ai as $id => $v) {

            if (!empty(self::$checkedPOST[$id]) && self::$checkedPOST[$id] === true) {
                continue;
            }

            if (empty(trim($_POST[$id]))) {
                unset($_POST[$id]);
                continue;
            }

            if (\is_array($v))
                continue;

            $v = trim(htmlspecialchars(self::checkText($v), ENT_QUOTES));

            if (\function_exists('get_magic_quotes_gpc')) {
                if (get_magic_quotes_gpc()) {
                    $v = stripslashes($v);
                }
                $v = str_replace('`', '&quot;', $v);
            }

            if (trim($_POST[$id]) != $v && $this->useLogs) {
                Logs::log(7, "POST -> {$_POST[$id]} != {$v}");
            }

            $_POST[$id] = $v;
            self::$checkedPOST[$id] = true;
        }
    }

    /**
     * возврат исходного текста после htmlspecialchars
     * почти аналог htmspecialchars_decode
     * @param string $str
     * @return string
     * @deprecated
     */
    protected static function decode($str): string
    {
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        $ret = strtr($str, $trans_tbl);
        return str_replace('scri', '', $ret);
    }

    /**
     * снятие последствий htmlspecialchars для ссылок
     *
     * @param $link
     * @return string
     */
    public static function linkDec($link): string
    {
        return str_replace('&amp;', '&', $link);
    }

    /**
     * проверка текста на сюрпризы со вложенными тегами
     *
     * @param string $text
     * @return string
     */
    public static function checkText($text): string
    {
        return preg_replace(/** @lang text */
            "!<script[^>]*>|</script>|<(\s{0,})iframe(\s{0,})>|</(\s{0,})iframe(\s{0,})>!isU", '!removed bad word!', $text);
    }

    /**
     * фильтр пост массива, если не пустой $postField
     */
    protected function customPostValid(): void
    {
        if (!empty($_POST)) {
            $ai = new \ArrayIterator($_POST);
            foreach ($ai as $id => $val) {

                if (!empty(self::$checkedPOST[$id]) && self::$checkedPOST[$id] === true) {
                    continue;
                }

                if (!empty($this->postField[$id])) {
                    $val = trim($val);

                    if ($val === '') {
                        unset($_POST[$id]);
                        continue;
                    }

                    if (!$this->shortFormat){
                        if (!empty($this->postField[$id]['type'])) {
                            $type = $this->postField[$id]['type'];
                        }
                        else {
                            $type = \gettype($val);
                        }

                        if (!empty($this->postField[$id]['maxLength'])) {
                            $val = substr($val, 0, (int)$this->postField[$id]['maxLength']);
                        }
                    }
                    else{
                        $type = $this->postField[$id][0]; // type
                        if (!empty($this->postField[$id][1])) { //maxLength
                            $val = substr($val, 0, (int)$this->postField[$id][1]);
                        }
                    }

                    if (\function_exists('get_magic_quotes_gpc')) {
                        if (\function_exists('stripslashes')) {
                            if (get_magic_quotes_gpc()) {
                                $val = stripslashes($val);
                            }
                        }

                        $val = str_replace('`', '&quot;', $val);
                    }

                    $val = $this->paramsControl($val, $type);

                    if ($_POST[$id] != $val && $this->useLogs) {
                        Logs::log(7, "-> POST[$id] = $val");
                    }

                    $_POST[$id] = $val;
                    self::$checkedPOST[$id] = true;
                }
            }
        }
    }

    /**
     * фильтр пост массива, если не пустой $getField
     */
    protected function customGetValid(): void
    {
        if (!empty($_GET)) {
            $ai = new \ArrayIterator($_GET);
            foreach ($ai as $id => $val) {
                if (!empty(self::$checkedGET[$id]) && self::$checkedGET[$id] === true) {
                    continue;
                }

                if ($val === '') {
                    unset($_GET[$id]);
                    continue;
                }

                if (!empty($this->getField[$id])) {
                    $val = trim($val);

                    if (!$this->shortFormat){
                        if (!empty($this->getField[$id]['type'])) {
                            $type = $this->getField[$id]['type'];
                        }
                        else {
                            $type = \gettype($val);
                        }

                        if (!empty($this->getField[$id]['maxLength'])) {
                            $val = substr($val, 0, (int)$this->getField[$id]['maxLength']);
                        }
                    }
                    else{
                        $type = $this->getField[$id][0]; // type
                        if (!empty($this->getField[$id][1])) { //maxLength
                            $val = substr($val, 0, (int)$this->getField[$id][1]);
                        }
                    }

                    $val = $this->paramsControl($val, $type);

                    if (\function_exists('get_magic_quotes_gpc')) {
                        if (\function_exists('stripslashes')) {
                            if (get_magic_quotes_gpc()) {
                                $val = stripslashes($val);
                            }
                        }

                        $val = str_replace('`', '&quot;', $val);
                    }

                    $val = preg_replace("/(\&lt\;br \/\&gt\;)|(\&lt\;br\&gt\;)/", ' <br /> ', $val);

                    if ($_GET[$id] !== $val && $this->useLogs) {
                        Logs::log(7, "-> GET[$id] = $val");
                    }

                    $_GET[$id] = $val;
                    self::$checkedGET[$id] = true;
                }
            }
        }
    }

    /**
     * приведение типов по парамету
     * @param number|string|mixed $param
     * @param string $type
     * @return bool|float|int|string|NULL|mixed|Date
     */
    protected function paramsControl($param, $type)
    {
        switch ($type) {
            case self::FLOAT:
            case 'double':
            case 'float':
                $param = trim($param);
                $param = (float)$param;
                break;
            case 'int':
            case self::INT:
            case 'integer':
                $param = trim($param);
                $param = (int)$param;
                break;
            case 'str':
            case self::STR:
            case 'string':
                $param = htmlspecialchars(self::checkText($param), ENT_QUOTES);
                break;
            case 'bool':
            case self::BOOL:
            case 'boolean':
                $param = (bool)$param;
                break;
            case 'array':
            case self::_ARRAY:
                break;
            case 'date':
            case self::DATE:
                $param = Date::intransDate($param);
                if ($param === '-/-') {
                    $param = '';
                }
                break;
            case 'datetime':
            case self::DATETIME:
                $param = Date::intransDate($param, true);
                if ($param === '-/-') {
                    $param = '';
                }
                break;
            case self::NOVALID:
                /*nop*/
                break;
            default:
                $param = htmlspecialchars(self::checkText($param), ENT_QUOTES);
                break;
        }

        return $param;
    }


    /**
     * ловим не существующие методы
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        $this->actionIndex();
        if (\strtolower(trim($name)) !== 'actonindex') {
            Logs::log(3, static::class . " hasn't action $name");
        }
    }
}
<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.04.2016
 *
 **/

namespace mwce\Models;

use mwce\db\Connect;
use mwce\Exceptions\ModException;
use mwce\Interfaces\Imodel;
use mwce\Tools\Configs;
use mwce\Validator\Sanitizer;

abstract class Model extends LikeArray implements Imodel
{
    /**
     * @var Connect
     */
    protected $db;

    /**
     * @var array кешированные данные со статических функций, где предусмотрено
     */
    protected static $sdata = array();

    /**
     * @var array типы полей для однозначной валидации из данных из бд
     */
    protected $fieldsType = array();

    /**
     * Model constructor.
     * @param int $con
     * @throws \mwce\Exceptions\CfgException
     * @throws \mwce\Exceptions\DBException
     */
    public function __construct($con = 0)
    {
        $this->db = Connect::start($con);
        $this->init();
    }

    /**
     * функция инициализации,
     * запускается сразу после конструктора
     */
    protected function init(){}

    /**
     * @param string|mixed $name
     * @param mixed $arguments
     * @throws ModException
     */
    public function __call($name, $arguments)
    {
        throw new ModException('undefuned method "' . $name . '" in ' . basename(static::class));
    }

    //region чистка кешей

    public static function dellCurPageCache()
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . '_dat' . DIRECTORY_SEPARATOR . Configs::curLang() . '_pages.php';
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * @param string $menuName
     */
    public static function dellCurMenuCache($menuName)
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . '_dat' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . Configs::curLang() . '_plugin_' . $menuName;

        if (file_exists($path)) {
            unlink($path);
        }
    }
    //endregion

    /**
     * @return string
     */
    public function __toString(): string
    {
        $string = '';

        if (!empty($this->object)) {
            foreach ($this->object as $oId => $item) {
                if (!empty($string)) {
                    $string .= ',';
                }
                $string .= " $oId => '$item' ";
            }
        }

        return $string;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    protected function _adding($name, $value)
    {
        if(!empty($this->fieldsType[$name]) && isset($value)){
            $value = Sanitizer::sanitize($value,$this->fieldsType[$name]);
        }

        parent::_adding($name, $value);
    }
}
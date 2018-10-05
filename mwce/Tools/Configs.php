<?php

/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 08.04.2016
 *
 **/

namespace mwce\Tools;

/**
 * Class Configs
 * запись/чтение/восстановление/хранение конфигов сайта
 * @method static array|string|int|mixed buildCfg(string $parameterName = null) конфиг текущего билда
 * @method static array|string|int|mixed globalCfg(string $parameterName = null) конфиг глобальный
 * @method static string curLang() текущий язык
 * @method static string currentBuild() текущий билд

 * @method static int userID()
 * @method static int curRole()
 * @method static int curGroup()
 *
 */
class Configs
{
    /**
     * @var array
     */
    private $Cfgs;

    /**
     * @var null|Configs
     */
    private static $instance;

    /**
     * Configs constructor.
     * @param array $params
     */
    protected function __construct($params)
    {
        $this->Cfgs = $params;
    }

    /**
     * создание/запись в новый/существующий конфиг
     * @param array $config - массив с параметрами
     * @param string $filename - название конфига (без расширения)
     * @param string $build - билд, по умолчанию default
     */
    public static function writeCfg($config, $filename, $build = 'default'): void
    {
        if ($build !== 'main') {
            $configDir = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $build . DIRECTORY_SEPARATOR . 'configs';
        }
        else {
            $configDir = baseDir . DIRECTORY_SEPARATOR . 'configs';
        }

        $path = $configDir . DIRECTORY_SEPARATOR . $filename . '.cfg';
        $repath = $configDir . DIRECTORY_SEPARATOR . $filename . '.cfg.bkc';

        if (file_exists($path)) //если есть конфиг - делаем бекапчик
        {
            rename($path, $repath);
        }

        $handle = fopen($path, 'wb');
        fwrite($handle, serialize($config));
        fclose($handle);
    }

    /**
     * @param string $cname - название файла конфигурации (без расширения)
     * @param string $build - требуемый билд, по умолчанию "default"
     * @return bool|array - возвращает конфигурацию в виде ассоциативного массива или же false, в случае неудачи
     */
    public static function readCfg($cname, $build = null)
    {
        if (null === $build) {
            $build = self::currentBuild();
        }

        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $build . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . "$cname.cfg";

        if (file_exists($path)) {
            return unserialize(trim(file_get_contents($path)), [ 'allowed_classes' => false ]);
        }
        return false;
    }

    /**
     * Восстанавливает файл конфигурации в случае, если есть копия
     * @param string $cname название конфига (без расширения)
     * @param string $build билд, по умолчанию "default"
     * @return bool true в случае удачи и false в противном случае
     */
    public static function recoverCfg($cname, $build = 'default'): bool
    {
        if ($build !== 'main') {
            $configDir = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $build . DIRECTORY_SEPARATOR . 'configs';
        }
        else {
            $configDir = baseDir . DIRECTORY_SEPARATOR . 'configs';
        }

        $path = $configDir . DIRECTORY_SEPARATOR . $cname . '.cfg.bkc';

        if (file_exists($path)) //если есть бекап на конфиг, возвращаем конфиг
        {
            rename($path, $configDir . DIRECTORY_SEPARATOR . $cname . '.cfg');
            return true;
        }

        return false;
    }

    /**
     * @param null|array $params
     * @return Configs|null
     */
    public static function initConfigs($params = null): ?Configs
    {
        if (null === self::$instance) {
            self::$instance = new self($params);
        }

        return self::$instance;
    }

    /**
     * @param $name
     * @param null|string $args
     * @return bool|mixed
     */
    protected static function getParam($name, $args = null)
    {
        if (!empty(self::$instance->Cfgs[$name])) {
            if (null === $args) {
                return self::$instance->Cfgs[$name];
            }

            if (!empty(self::$instance->Cfgs[$name][$args])) {
                return self::$instance->Cfgs[$name][$args];
            }
        }

        return false;
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::getParam($name, !empty($arguments[0]) ? $arguments[0] : null);
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public static function addParams($name, $value): void
    {
        if (null === self::$instance) {
            self::initConfigs([ $name => $value ]);
        }
        else {

            if (empty(self::$instance->Cfgs[$name])) {
                self::$instance->Cfgs[$name] = $value;
            }
            else if (\is_array(self::$instance->Cfgs[$name])) {
                if (\is_array($value)) {
                    self::$instance->Cfgs[$name] = array_merge(self::$instance->Cfgs[$name], $value);
                }
            }
            else {
                self::$instance->Cfgs[$name] = $value;
            }
        }
    }

    /**
     * @return array|mixed
     */
    public static function loadConnectionCfg()
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . self::currentBuild() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'connections.php';
        if (file_exists($path)) {
            return require $path;
        }
        return [];
    }
}
<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 12.04.2016
 * v 0.1
 **/

namespace mwce\Tools;
/**
 * Class DicBuilder
 * @package mwce
 *
 * помогает строить/перестраивать языковые словари
 */
class DicBuilder
{
    /**
     * @var null|string
     * полный адрес до файла словаря, включая расширение(если есть)
     */
    private $location;

    /**
     * DicBuilder constructor.
     * @param string|null $location
     * @throws \Exception
     */
    public function __construct($location = null)
    {
        $this->location = $location;
        if (null !== $location && !file_exists($location)) {
            $this->writeThis('<?php');
        }
    }

    /**
     * @param array $array
     * @param null|string $location
     * @throws \Exception
     */
    public function buildDic($array, $location = null): void
    {

        if (null !== $location) {
            $this->location = $location;
        }

        $content = '<?php return [' . PHP_EOL;
        $ai = new \ArrayIterator($array);

        foreach ($ai as $id => $value) {
            $content .= '\'' . $id . '\' => \'' . $value . '\',' . PHP_EOL;
        }

        $content .= '];';

        $this->writeThis($content);
    }

    /**
     * запись словаря
     * @param string $content
     * @throws \Exception
     */
    private function writeThis($content): void
    {
        if (null !== $this->location) {
            file_put_contents($this->location, $content, LOCK_EX);
        }
        else {
            throw new \RuntimeException('location parameter is empty!');
        }
    }

    /**
     * @param string $path
     * @return array|mixed
     */
    public static function getLang($path)
    {

        if (file_exists($path)) {
            $l = include $path;
        }
        else {
            return array();
        }

        if (!\is_array($l)) {
            return array();
        }

        return $l;
    }

    /**
     * Запись в словарь данных
     * @param string $value данные
     * @param string $preffix часть названия ключа массива
     * @param bool $isIterate обновлять или дописывать вконце 1,2...н
     * @return bool|string ключ от добавленного элеента
     * @throws \Exception
     */
    public function add2Dic($value, $preffix = 'auto_lang', $isIterate = false)
    {
        if (!file_exists($this->location)) {
            return false;
        }

        $container = include $this->location;

        if (empty($container) || !\is_array($container)) {
            $container = [];
        }

        $i = 0;

        if ($isIterate || empty($container) || empty($container[$preffix])) {
            $container[$preffix] = $value;
            $this->buildDic($container);
            return $preffix;
        }

        $tName = $preffix . $i;
        while (isset($container[$tName])) {
            $i++;
            $tName = $preffix . $i;
        }

        $container[$preffix . $i] = $value;
        $this->buildDic($container);
        return $preffix . $i;

    }

    /**
     * удалить
     * @param string|mixed $id
     * @return bool
     * @throws \Exception
     */
    public function delFromDic($id): bool
    {
        if (!file_exists($this->location)) {
            return false;
        }

        $container = include $this->location;

        if (!empty($container[$id])) {
            unset($container[$id]);
            $this->buildDic($container);
            return true;
        }
        return false;
    }
}
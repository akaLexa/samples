<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.04.2016
 *
 **/

namespace mwce\Controllers;

use mwce\db\Connect;
use mwce\Tools\Configs;
use mwce\Tools\Content;
use mwce\Tools\Logs;

class ModuleController extends Controller
{
    /**
     * массив со всеми страницами
     * @var array
     */
    protected $pages = [];

    /**
     * @var int  показывать ли полное окно или только кусок модуля
     */
    protected $showMain = 1;


    /**
     * Controller constructor.
     * @param Content $view
     * @param array $pages
     */
    public function __construct(Content $view, array $pages)
    {
        $this->view = $view;
        $this->pages = $pages;

        $build = Configs::currentBuild();

        $this->className = basename(static::class);
        if ($this->className === static::class) {
            $t = explode('\\', static::class);
            $this->className = end($t);
        }

        if (!empty($build)) {
            $this->configs = Configs::readCfg($this->className, $build);
        } //подгружаем конфиги модуля сразу
    }

    /**
     * @param string $action
     */
    public function action($action): void
    {
        $this->init();
        $this->validate();
        $this->$action();
        $this->callback();
    }

    /**
     * эмуляция не ооп работы модуля
     *
     * @param string $mpath где модуль
     */
    public function genNonMVC($mpath): void
    {
        $moduleName = basename($mpath, '.php');
        $this->view->showOnly(true);

        if (!empty($this->pages[$moduleName]['title'])) // полезно для кеширования
        {
            $this->view->replace($this->pages[$moduleName]['title'], 'title');
        }

        if ($this->isCached(__FUNCTION__, $moduleName)) //кешик
        {
            return;
        }

        try {
            $user = $this->model;
            $content = $this->view;
            $page = $this;
            $db = Connect::start();

            ob_start();
            require_once $mpath;
            $cnt = ob_get_contents();
            ob_end_clean();

            if (!empty($cnt)) {
                $this->view->setFromCache($cnt);
            }
            $this->view->showOnly(false);
        }
        catch (\Exception $e) {
            echo $e->getMessage();
            Logs::log($e);
            $this->view->showOnly(false);
        }

        if ($this->cacheNeed($moduleName)) //если нужен кеш
        {
            $this->doCache($moduleName . '_' . __FUNCTION__);
        }
    }

    /**
     * узнать настройки данного модуля
     *
     * @param null|string $name
     * @return bool|array
     */
    public function getPProperties($name = NULL)
    {
        if (null === $name) {
            $name = $this->className;
        }

        if (!empty($this->pages[$name])) {
            return $this->pages[$name];
        }
        return false;
    }

    /**
     * возвращает разницу времени создания файла и текущего
     *
     * @param  string $fname
     * @return int
     */
    protected function cacheDif($fname): int
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . '_dat' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->view->curLang() . "_$fname";

        if (file_exists($path)) {
            return time() - filemtime($path);
        }

        return 0;
    }

    /**
     * удаляем файлик кеша
     * @param string $fname название файлика(функции)
     * @return void
     */
    protected function cacheDelete($fname): void
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . '_dat' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->view->curLang() . "_$fname";

        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Определяет, нужно ли кешировать файл или уже есть кешик
     * @param string|null $name название модуля
     * @return bool
     */
    protected function cacheNeed($name = null): bool
    {
        if (null === $name) {
            $name = $this->className;
        }

        return $this->pages[$name]['caching'] > 0;
    }

    /**
     * возвращает закешированный модуль иначе, пустую строку
     * @param string $fname название функции
     * @return string
     */
    protected function cacheGive($fname): string
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . '_dat' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->view->curLang() . "_$fname";

        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return '';
    }

    /**
     * пишем кеш
     *
     * @param string $fname
     * @param string $content
     */
    protected function cacheWrite($fname, $content): void
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . '_dat' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->view->curLang() . "_$fname";
        $h = fopen($path, 'wb');
        fwrite($h, $content);
        fclose($h);
    }

    /**
     * функция подхвата кешироваиия вернет true в случае, если есть актуальная копия в кеше
     *
     * @param string $fname - название экшена
     * @param string|null $name название модуля
     * @return bool
     */
    protected function isCached($fname, $name = null): bool
    {
        $prop = $this->getPProperties($name);

        if (null !== $name) {
            $fname = $name . '_' . $fname;
        }

        if ($this->cacheNeed($name) && $this->cacheDif($fname) <= $prop['caching']) //если модуль кешируется и кеш еще актуален, вместо работы модуля берем кеш
        {
            $cache = $this->cacheGive($fname);
            if (empty($cache)) {
                return false;
            }

            $this->view->setFromCache($this->cacheGive($fname)); //суем в контейнер данные
            return true;
        }
        return false;
    }

    /**
     * пишем кеш для экшена
     *
     * @param string $fname - экшен
     */
    protected function doCache($fname): void
    {
        $cache = $this->view->getContainer();

        if (!empty($cache)) {
            $this->cacheWrite($fname, $cache);
        } //пишем кеш
    }

    /**
     * фиильтрация данных
     */
    protected function validate(): void
    {
        if (!$this->needValid) {
            return;
        }

        if (empty($this->postField)) {
            $this->clearPost();
        }
        else {
            $this->customPostValid();
        }

        if (empty($this->getField)) {
            $this->clearGet();
        }
        else {
            $this->customGetValid();
        }
    }
}
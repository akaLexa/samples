<?php

/**
 * MuWebCloneEngine
 * version: 1.6
 * by epmak
 * 25.08.2015
 * шаблонизатор
 **/

namespace mwce\Tools;

use mwce\Exceptions\ContentException;
use mwce\Models\LikeArray;
use mwce\Models\Model;

/**
 * Class Content
 * шаблонизатор.
 */
class Content
{
    /**
     * @var array
     * массив значений на которые будем заменять
     */
    private $vars = array();

    /**
     * @var string
     * текущее название темы
     */
    private $themName;

    /**
     * @var string
     * текущий язык
     */
    private $clang;

    /**
     * @var string
     * текущий адрес сайта
     */
    private $adr;

    /**
     * @var array
     * разделитель 0 - левый 1 - правый
     */
    private $separator;

    /**
     * @var string
     * своеобразный буфер
     * для хранения сгенерированного html
     */
    private $container = '';

    /**
     * @var int
     */
    private $notWrite = 0;

    /**
     * @var array
     * список подключенных словарей
     */
    private $adedDic = array();

    /**
     * @var array
     * подключенные скрипты css/js/ и т.д.
     */
    private $attScripts = [];

    /**
     * @var string
     * текущий модуль
     */
    private $curModule = '';

    /**
     * @var array
     * массивчек со словами,
     * которые нельзя занимать под объект
     */
    private static $deniedArray = array(
        'baseVals',
        'global_js',
        'global_css',
        'site',
        'theme',
    );

    /**
     * @var array
     * отрезки по тегам из общего шаблона
     */
    private $segments = [];
    /**
     * @var array
     * 0 => folder 1 => name
     */
    private $curTemplate = [];

    /**
     * @var string
     * отображаемая по умолчанию главная страница
     */
    public $defHtml = 'index';


    /**
     * @param string $adr - адресс сайта
     * @param string $theme - назщвание темы
     * @param string $lang - язык
     * @param array $separator - суффикс и преффикс показывающий признак, что слово ключевое
     * @throws ContentException
     */
    public function __construct(string $adr, string $theme = null, string $lang, array $separator = [ '|', '|' ])
    {
        $this->clang = $lang;
        $this->themName = $theme;
        $this->adr = $adr;
        $this->separator = $separator;

        $this->vars['baseVals'] = array(
            $this->separator[0] . 'site' . $this->separator[1] => $this->adr,
            $this->separator[0] . 'theme' . $this->separator[1] => $this->themName,
            $this->separator[0] . 'global_js' . $this->separator[1] => '',
            $this->separator[0] . 'global_css' . $this->separator[1] => '',
        );

        $this->add_dict('site'); //если есть общий словарь, то подгружаем

        if (null !== $theme) {
            $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.html';

            if (!file_exists($path)) {
                throw new ContentException("there is no theme \"{$this->themName}\" or " . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . "index.html doesn't exists.");
            }
        }
    }

    /**
     * контект из файла
     * @param string $path
     * @return string
     */
    public static function gContent($path): string
    {
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return '';
    }

    /**
     * анализ шаблона
     * @param string $tplName
     * @param string $folder
     * @return Content $this
     */
    public function parseTemplate($tplName, $folder): Content
    {
        if (!empty($this->curModule)) {
            $module = $this->curModule;
        }
        else {
            $module = 'commonStack';
        }

        $this->curTemplate = [ $folder, $tplName ];

        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $tplName . '.html';
        if (file_exists($path)) {
            $tpl = self::gContent($path);


            preg_match_all('/(' . preg_quote($this->separator[0],'') . ")+(\/)?(\w){1,}(" . preg_quote($this->separator[1],'') . ')+/', $tpl, $matches);

            if (!empty($matches[0])) {
                $tagAreas = [];
                $i = 0;

                foreach ($matches[0] as $_id => $keys) {
                    $i++;
                    $j = 0;
                    $keys = str_replace('|', '', $keys);

                    foreach ($matches[0] as $__id => $subKeys) {
                        $j++;

                        $subKeys = str_replace($this->separator, '', $subKeys);
                        if ($subKeys === '/' . $keys) {
                            $tagAreas[] = $keys;
                            unset($matches[0][$__id], $matches[0][$_id]);
                            break;
                        }
                    }
                }

                if (!empty($tagAreas)) {
                    foreach ($tagAreas as $tag) {
                        preg_match('#' . preg_quote($this->separator[0] . $tag . $this->separator[1],'') . '(.+?)' . preg_quote($this->separator[0] . '/' . $tag . $this->separator[1],'') . '#s', $tpl, $matches);

                        if (!empty($matches)) {
                            $this->segments[$module][$tag] = $matches;
                        }
                    }
                }
            }
        }

        return $this;
    }

    protected function findPairs($tpl): void
    {

        preg_match_all('/(' . preg_quote($this->separator[0],'') . ')+(\/)?(\w){1,}(' . preg_quote($this->separator[1],'') . ')+/', $tpl, $matches);
        if (!empty($matches[0])) {
            $tagAreas = [];
            $i = 0;

            foreach ($matches[0] as $_id => $keys) {
                $i++;
                $j = 0;
                $keys = str_replace('|', '', $keys);
                foreach ($matches[0] as $__id => $subKeys) {
                    $j++;

                    $subKeys = str_replace($this->separator, '', $subKeys);
                    if ($subKeys === '/' . $keys) {
                        $tagAreas[] = $keys;
                        unset($matches[0][$__id], $matches[0][$_id]);
                        break;
                    }
                }
            }

            if (!empty($tagAreas)) {
                foreach ($tagAreas as $tag) {
                    preg_match('#' . preg_quote($this->separator[0] . $tag . $this->separator[1],'') . '(.+?)' . preg_quote($this->separator[0] . '/' . $tag . $this->separator[1],'') . '#s', $tpl, $matches);

                    if (!empty($matches)) {
                        $this->segments[$this->curModule][$tag] = $matches;
                    }
                }
            }
        }
    }

    /**
     * парсинг шабона между тегами {some_tag} string... {/some_tag}
     * thx to codeigniter
     * @param string $tag тег, вокруг которого пляски
     * @param array $data словарь
     * @param string $content адрес
     * @param string $folder папка, где искать шаблон
     * @return Content
     */
    public function loops($tag, $data, $content, $folder = 'public'): Content
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $content . '.html';

        if (file_exists($path)) {
            $content = self::gContent($path);
        }
        else {
            return $this;
        }

        $this->_loop($tag, $data, $content);

        return $this;
    }

    /**
     * @param string $tag
     * @param array $data
     * @param string $content
     * @return Content
     */
    private function _loop($tag, $data, $content): Content
    {
        $matches = [];

        preg_match_all('#' . preg_quote($this->separator[0] . $tag . $this->separator[1],'') . '(.+?)' . preg_quote($this->separator[0] . '/' . $tag . $this->separator[1],'') . '#s', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $str = '';

            foreach ($data as $row) {

                $temp = array();
                /**
                 * @var $row array|Model
                 */
                foreach ($row as $key => $val) {
                    if (\is_array($val) || \is_object($val)) {
                        $this->_loop($key, $val, $match[1]);
                        continue;
                    }

                    $temp[$this->separator[0] . $key . $this->separator[1]] = $val;
                }

                $str .= strtr($match[1], $temp);
            }

            if (!empty($this->curModule)) {
                $this->vars[$this->curModule][$match[0]] = $str;
            }
            else {
                $this->vars[$match[0]] = $str;
            }
        }

        return $this;
    }

    /**
     * @param string $tag
     * @param array $data
     * @return Content $this
     */
    public function loopInSegment($tag, $data): Content
    {
        if (!empty($this->curTemplate)) {

            if (!empty($this->curModule)) {
                $module = $this->curModule;
            }
            else {
                $module = 'commonStack';
            }

            if (!empty($this->segments[$module][$tag])) {
                $this->_loop($tag, $data, $this->segments[$module][$tag][0]);
            }
        }
        return $this;
    }

    /**
     * вывод на экран только сегмента под тегами $segment
     * @param string $segment
     */
    public function outSegment($segment): void
    {
        if (!empty($this->curTemplate)) {

            if (!empty($this->curModule)) {
                $module = $this->curModule;
            }
            else {
                $module = 'commonStack';
            }
            if (!empty($this->segments[$module][$segment])) {
                $content = $this->segments[$module][$segment][0];
                if (!empty($this->curModule) && !empty($this->vars[$this->curModule]) && \is_array($this->vars[$this->curModule])) {
                    $content = strtr($content, $this->vars[$this->curModule]);
                }

                $ars = [];
                $ai = new \ArrayIterator($this->vars);
                foreach ($ai as $id => $val) {
                    if (!\is_array($val)) {
                        $ars[$id] = $val;
                    }
                }

                $content = strtr($content, $ars);
                $content = strtr($content, $this->vars['baseVals']);

                if (!empty($this->segments)) {
                    if (!empty($this->curModule)) {
                        $module = $this->curModule;
                    }
                    else {
                        $module = 'commonStack';
                    }

                    if (!empty($this->segments[$module])) {
                        $tags = array_keys($this->segments[$module]);

                        foreach ($tags as $tag) {
                            $content = str_replace($this->segments[$module][$tag][0], '', $content);
                            unset($this->segments[$module][$tag]);
                        }
                    }
                }

                $content = preg_replace('/[' . $this->separator[0] . ']+[A-Za-z0-9_]{1,25}[' . $this->separator[1] . ']+/', ' ', $content);

                $this->container .= $content;
            }
        }
    }

    /**
     * выставить имя текущего контейнера
     * @param string $name
     * @return Content
     * @throws \mwce\Exceptions\ContentException
     * @throws /Exception
     */
    public function setName($name): Content
    {
        if (\in_array($name, self::$deniedArray, true)) {
            throw new ContentException(" you can't use $name for object name");
        }

        $this->curModule = $name;

        if (!empty($this->adedDic[$name])) {
            unset($this->adedDic[$name]);
        }

        $this->add_dict($name);

        return $this;
    }

    /**
     * затереть текущий контроллер
     * @param int $clearAll совсем затереть или не очень
     * @return Content
     */
    public function emptyName($clearAll = 1): Content
    {
        if ($clearAll && !empty($this->vars[$this->curModule]) && \is_array($this->vars[$this->curModule])) {
            unset($this->vars[$this->curModule]);
        }

        $this->curModule = '';

        return $this;
    }

    /**
     * узнать текущий словарь
     * @return string
     */
    public function knowName(): string
    {
        return $this->curModule;
    }

    /**
     * Вывод отдельного слова по идентификатору
     *
     * @param mixed $id идентификатор
     * @param string|null $cname название модуля
     * @return string
     */
    public function getVal($id, $cname = NULL): string
    {
        $id = $this->separator[0] . $id . $this->separator[1];

        if (null === $cname && empty($this->curModule)) {
            if (!empty($this->vars[$id])) {
                return $this->vars[$id];
            }
            return false;
        }

        if (!empty($this->curModule) && !empty($this->vars[$this->curModule][$id])) {
            return $this->vars[$this->curModule][$id];
        }

        if (!empty($this->vars[$cname]) && !empty($this->vars[$cname][$id])) {
            return $this->vars[$cname][$id];
        }

        return '';
    }

    /**
     * возвращает адрес сервера
     *
     * @return mixed
     */
    public function getAdr()
    {
        return $this->adr;
    }

    /**
     * Добавляет язык к контенту
     *
     * @param  array|string $file - название файла "словаря"
     * @param  bool $isJSON - json ворфмат или нет
     * @return Content
     */
    public function add_dict($file, $isJSON = false): Content
    {
        if (\is_array($file) || $file instanceof LikeArray) {

            if ($isJSON) {
                $file = json_decode($file, true);
            }
            /**
             * @var $file array
             * @var  $d string
             * @var  $v string
             */
            foreach ($file as $d => $v) {

                if (!empty($this->curModule)) {
                    $this->vars[$this->curModule][$this->separator[0] . $d . $this->separator[1]] = $v;
                }
                else {
                    $this->vars[$this->separator[0] . $d . $this->separator[1]] = $v;
                }
            }
        }
        else {

            $lang = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $this->clang . DIRECTORY_SEPARATOR . $file . '.php');

            if (!empty($lang)) {

                if (!empty($this->adedDic[$file])) // если словарь уже подключен, второй раз лопатить смысла нет
                {
                    return $this;
                }

                if (\is_array($lang)) {

                    foreach ($lang as $d => $v) {
                        if (!empty($this->curModule)) {
                            $this->vars[$this->curModule][$this->separator[0] . $d . $this->separator[1]] = $v;
                        }
                        else {
                            $this->vars[$this->separator[0] . $d . $this->separator[1]] = $v;
                        }
                    }
                    $this->adedDic[$file] = 1;
                }
            }
        }
        return $this;
    }

    /**
     * возвращает текущий язык
     *
     * @return string
     */
    public function curLang(): string
    {
        return $this->clang;
    }

    /**
     * добаляет в словарь
     *
     * @param string|array $name - резервированное слово(без "|"), если массив, то ассоциативный кгде ключ -
     *     заресервированное слово, а значение, то, на что нужно слово заменить
     * @param mixed $val - значение зарезервированного слова
     * @param int $isJSON
     * @return  Content
     */
    public function set($name, $val = NULL, $isJSON = 0): Content
    {
        if (\is_array($name)) {
            $this->add_dict($name, $isJSON);
        }
        else {
            $name = $this->separator[0] . $name . $this->separator[1];
            if (!empty($this->curModule)) {
                $this->vars[$this->curModule][$name] = $val;
            }
            else {
                $this->vars[$name] = $val;
            }
        }

        return $this;
    }

    /**
     * создать пустое значение
     * @param string $name
     * @return Content $this
     */
    public function setEmpty($name): Content
    {
        $name = $this->separator[0] . $name . $this->separator[1];

        if (!empty($this->curModule)) {
            $this->vars[$this->curModule][$name] = '';
        }
        else {
            $this->vars[$name] = '';
        }
        return $this;
    }

    /**
     * заменяет название элемента в "словаре" (!в словаре должно присутствовать выражение $where)
     * @param string $what - что вставить
     * @param string $where - за место чего
     * @return Content
     */
    public function replace($what, $where): Content
    {
        if (!empty($this->curModule)
            && !empty($this->vars[$this->curModule][$this->separator[0] . $what . $this->separator[1]])
        ) {
            $this->set($where, $this->vars[$this->curModule][$this->separator[0] . $what . $this->separator[1]]);
        }
        else {
            if (!empty($this->vars[$this->separator[0] . $what . $this->separator[1]])) {
                $this->set($where, $this->vars[$this->separator[0] . $what . $this->separator[1]]);
            }
        }

        return $this;
    }

    /**
     * Функция очищаент контенер
     */
    public function clearContainer(): void
    {
        $this->container = '';
    }

    /**
     * возвращает информацию из конетенра
     *
     * @return string
     */
    public function getContainer(): string
    {
        return $this->container;
    }

    /**
     * позволяет включить или отключить режими забиси в буфер и выводить сразу на экран
     * @param bool|int $val
     */
    public function showOnly($val): void
    {
        if ((int)$val > 0) {
            $this->notWrite = 1;
        }
        else {
            $this->notWrite = 0;
        }
    }

    /**
     * пишет в контейнер данные
     * @param string $value
     */
    public function setFromCache($value): void
    {
        $this->container = $value;
    }

    /**
     * задает, в какую переменную будут помещены данные из контенера
     *
     * @param string $cname - название переменной
     * @param int $isClean - если >0 то после добавления в словарь данные из контерена будут удалены
     * @return Content
     */
    public function setFContainer($cname, $isClean = 0): Content
    {

        if (!empty($this->container)) {
            $this->set($cname, $this->container);
            if ((int)$isClean > 0) {
                $this->clearContainer();
            }
        }
        return $this;
    }

    public function tplExists($tpl,$folder = '') : bool
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $tpl . '.html';
        return file_exists($path);
    }

    /**
     * функция выводит на экран или возвращает строку с содержимым шаблона и скрипта
     * @param string $tpl - название шаблона
     * @param string $folder - папка под группу файлов (обычно для модуля)
     * @return mixed|string
     */
    public function out($tpl, $folder = '')
    {
        if (empty($folder)) {
            $folder = 'public';
        }

        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $tpl . '.html';

        if (file_exists($path)) {

            //region load scripts
            $this->loadScripts('js' . DIRECTORY_SEPARATOR, $folder . '.' . $tpl . '.js');
            $this->loadScripts('html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR, $folder . '.' . $tpl . '.js');

            $this->loadScripts('css' . DIRECTORY_SEPARATOR, $folder . '.' . $tpl . '.css', 2);
            $this->loadScripts('html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR, $folder . '.' . $tpl . '.css', 2);
            //endregion

            //region collect_dictionary
            $content = self::gContent($path);

            if (!empty($this->curModule)
                && !empty($this->vars[$this->curModule])
                && \is_array($this->vars[$this->curModule])) {
                $content = strtr($content, $this->vars[$this->curModule]);
            }

            $ars = [];
            $ai = new \ArrayIterator($this->vars);
            foreach ($ai as $id => $val) {
                if (!\is_array($val)) {
                    $ars[$id] = $val;
                }
            }

            $content = strtr($content, $ars);
            $content = strtr($content, $this->vars['baseVals']);
            //endregion

            //region clean unused tags

            if (empty($this->segments)) {
                $this->parseTemplate($tpl, $folder);
            }

            if (!empty($this->segments)) {
                if (!empty($this->curModule)) {
                    $module = $this->curModule;
                }
                else {
                    $module = 'commonStack';
                }

                if (!empty($this->segments[$module])) {
                    $tags = array_keys($this->segments[$module]);

                    foreach ($tags as $tag) {
                        $content = str_replace($this->segments[$module][$tag][0], '', $content);
                        unset($this->segments[$module][$tag]);
                    }
                }
            }

            $content = preg_replace('/[' . $this->separator[0] . ']+[A-Za-z0-9_]{1,25}[' . $this->separator[1] . ']+/', ' ', $content);
            //endregion

            if ($this->notWrite === 0) //если собираем
            {
                $this->container .= $content;
            }
            else {
                echo $content;
            }

            return $content;
        }

        echo \chr(10) . \chr(13) . '[error]: file "' . $path . '" doesn\'t exists' . \chr(10) . \chr(13);
        return '';
    }

    /**
     * отображает на экране только массив, преобраозованный в JSON
     * @param array $data
     */
    public function showJSON($data): void
    {
        $this->container = json_encode($data);
    }

    /**
     * подключить скрипты
     * @param string $address адрес до директории со скриптом (от дирректории с темой)
     * @param string $name название скрипта
     * @param int $type 1 = js, 2 = css
     */
    public function loadScripts($address, $name, $type = 1): void
    {

        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . $address . $name;
        if (file_exists($path)) {

            $script = strtr(trim(file_get_contents($path)), $this->vars['baseVals']);

            if (!empty($script)) {
                if (!empty($this->curModule)) {
                    $script = strtr($script, $this->vars[$this->curModule]);
                }

                if ($type === 1) {
                    $this->vars['baseVals'][$this->separator[0] . 'global_js' . $this->separator[1]] .= "\r\n/* injected script */\r\n" . $script;
                }
                else {
                    $this->vars['baseVals'][$this->separator[0] . 'global_css' . $this->separator[1]] .= "\r\n/* injected script */\r\n" . $script;
                }

                $this->attScripts[$name] = 1;
            }
        }
    }

    /**
     * глобальный вывод на экран
     *
     * @param string $args - зарезервированное слово, в которое сольется весь накомпленный контейнер
     * @param string $tpl - файл шаблона, в который все будет сливаться
     * @param string $folder - папка
     */
    public function global_out($tpl, $folder = '', $args = 'page'): void
    {
        $this->setFContainer($args); //суем из контенера в переменную
        $this->showOnly(true);
        $this->out($tpl, $folder);
    }

    /**
     * культурно показывает ошибки на экран
     *
     * @param string $msg - заглавие ошибки
     * @param string $descr - подробности ошибки
     */
    public static function showError($msg, $descr = ' '): void
    {
        if (file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'error.html')) {
            $content = file_get_contents(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'error.html');
            $c = array( '|msg|' => $msg, '|msg_desc|' => $descr );
            foreach ($c as $key => $val) {
                $content = str_replace($key, $val, $content);
            }
            echo $content;
        }
        else {
            die($msg);
        }
    }

    /**
     * вывод ошибки по номеру на экран
     * @param \Exception | int $erNum номер ошибки
     */
    public function error($erNum): void
    {
        $this->add_dict('errors');
        if ($this->getVal('errTitle') === false) {
            $this->set('title', '...');
        }
        else {
            $this->replace('errTitle', 'title');
        }

        if (($erNum instanceof \Exception || $erNum instanceof \Throwable) && !empty(Configs::globalCfg('errorLevel')) && Configs::globalCfg('errorLevel') > 0) {
            $this->set('msg_desc', $this->getVal('err' . $erNum->getCode()) . ': ' . $erNum->getMessage());
        }
        else {
            if ($this->getVal('err' . $erNum) !== false) {
                $this->replace('err' . $erNum, 'msg_desc');
            }
            else {
                $this->set('msg_desc', 'Unknown error ' . $erNum);
            }
        }
        $this->out('error', 'public');
    }

    /**
     * вывод ошибки с заданным текстом
     *
     * @param string $text
     * @param null $title
     */
    public function showErrorForm($text,$title = null): void
    {
        Logs::textLog(4, $text, true);
        if (null !== $this->themName) {
            $this
                ->set('msg_desc', $text)
                ->set('errTitle', (null !== $title ? $title : ''))
                ->out('error', 'public')
            ;
        }
        else {
            echo \chr(10) . \chr(13) . '[error]:' . $text . \chr(10) . \chr(13);
        }
    }

    /**
     * вывод на кран информации из эксепшена
     * @param \Exception $e
     * @param string $separator резделитель по умолчанию для шаблонизатора
     */
    public static function errorException(\Exception $e, $separator = '|'): void
    {

        if (file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'error.html')) {
            $content = file_get_contents(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'error.html');

            if (!empty(Configs::globalCfg('errorLevel')) && Configs::globalCfg('errorLevel') > 0) {
                $c = array(
                    $separator . 'message' . $separator => $e->getMessage(),
                    $separator . 'line' . $separator => $e->getLine(),
                    $separator . 'code' . $separator => $e->getCode(),
                    $separator . 'file' . $separator => $e->getFile(),
                    $separator . 'trace' . $separator => $e->getTraceAsString()
                );
            }
            else {
                $c = array(
                    $separator . 'message' . $separator => 'Something went wrong. Maybe page not found or maybe you should contact with administrator...',
                    $separator . 'line' . $separator => '',
                    $separator . 'code' . $separator => '',
                    $separator . 'file' . $separator => '',
                    $separator . 'trace' . $separator => ''
                );
            }


            $c[$separator . 'site' . $separator] = '/';


            foreach ($c as $key => $val) {
                $content = str_replace($key, $val, $content);
            }
            echo $content;
        }
        else {
            die($e->getMessage());
        }
    }

    /**
     * @param string $msg
     * @param bool $showTime
     * @param string $timeFormat
     */
    public static function cmdMessage($msg, $showTime = false, $timeFormat = 'H:i:s d-m-Y'): void
    {
        print ($showTime ? '[' . date($timeFormat) . ']' : '') . ' ' . $msg . \chr(10) . \chr(13);
    }

    //region magic
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'address':
            case 'adr':
                return $this->adr;
            case 'theme':
                return $this->themName;
            case 'dictionary':
                return $this->vars;
            case 'lang':
                return $this->clang;
            case 'idic':
                return $this->adedDic;
            default:
                return false;
        }
    }

    public function __set($name, $value)
    {
    }

    public function __isset($name)
    {
        switch (strtolower($name)) {
            case 'address':
            case 'adr':
                if (!empty($this->adr)) {
                    return true;
                }
                return false;
            case 'theme':
                if (!empty($this->themName)) {
                    return true;
                }
                return false;
            case 'lang':
                if (!empty($this->clang)) {
                    return true;
                }
                return false;
            default:
                return false;
        }
    }
    //endregion
}
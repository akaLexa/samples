<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 01.08.2018
 **/

namespace mwce\Templater;

/**
 * Class Templater
 * @package mwce\Templater
 * @version 2.0
 * Шаблонизатор. Состоит из 2 классов: Templater и Fragment. Templater копит в себе Fragment через renderFragment и
 * имеет возможность либо вернуть только выбранный Fragment через getFragment, либо вернуть результирующий Fragment
 * через release
 */
class Templater
{
    /**
     * название части, где хранятся общие статичные данные
     */
    public const main = 'staticMain';

    /**
     * адрес дирректории с файлами шаблонов
     * @var string
     */
    private $themeRoot;

    /**
     * @var array
     */
    private $dictionary = array();

    /**
     * текущий модуль/плагин, для которого составляется словарь
     * @var string
     */
    private $currentModuleName;

    /**
     * @var Templater
     */
    private $currentModule;

    /**
     * @var array
     */
    private $fragments = array();

    /**
     * @var string
     */
    private $openTag = '{{';
    /**
     * @var string
     */
    private $closeTag = '}}';

    /**
     * Themeplater constructor.
     * @param string $rootThemePath адрес до корневой дирректории с файлами шаблонов
     */
    public function __construct(string $rootThemePath = '')
    {
        $this->themeRoot = $rootThemePath;
        $this->dictionary[self::main] = array();
        $this->dictionary['module'] = array();
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->currentModuleName;
    }

    /**
     * название модуля, без адреса
     * @param string $currentModuleName
     * @return Templater
     */
    public function setModuleName(string $currentModuleName): Templater
    {
        $this->currentModuleName = $currentModuleName;
        return $this;
    }

    /**
     * @param mixed $id
     * @param mixed $value
     * @param string|null $name
     * @param bool $changeActiveModule
     * @return Templater
     */
    public function set($id,$value, string $name = null, bool $changeActiveModule = false) : Templater {

        if(null === $name){
            $name = $this->currentModuleName;
        }
        else if ($changeActiveModule){
            $this->currentModuleName = $name;
        }

        if(!isset($this->dictionary['module'][$name]) || !\is_array($this->dictionary['module'][$name])){
            $this->dictionary['module'][$name] = array();
        }

        $this->dictionary['module'][$name] = array_merge($this->dictionary['module'][$name],$this->setDictionaryItems([$id=>$value]));

        return $this;
    }

    /**
     * @param array $array
     * @return array
     */
    protected function setDictionaryItems (array $array) : array {
        $r = array();

        foreach ($array as $alias => $item) {
            $r[$this->openTag . $alias . $this->closeTag] = \is_array($item) ? $this->setDictionaryItems($item) : $item;
        }

        return $r;
    }

    /**
     * @param string $templateName
     * @param string $fragmentName
     * @param bool $useRootPath
     * @return Templater
     */
    public function renderFragment(string $templateName, string $fragmentName, bool $useRootPath = true): Templater {

        $this->fragments[$this->currentModuleName][$fragmentName]['fragment'] = new Fragment(
            !$useRootPath || empty($this->themeRoot) ? $templateName : $this->themeRoot . DIRECTORY_SEPARATOR . $templateName,
            $this->currentModuleName,
            $this->openTag,
            $this->closeTag
        );
        $this->fragments[$this->currentModuleName][$fragmentName]['q'] = \count($this->fragments[$this->currentModuleName]);

        $this->fragments[$this->currentModuleName][$fragmentName]['fragment']->render(
            $this->dictionary[self::main],
            !empty($this->dictionary['module'][$this->currentModuleName]) ? $this->dictionary['module'][$this->currentModuleName] : []
        );

        return $this;
    }

    /**
     * Вставка одного фрагмента в другой
     * @param Fragment $fragment фрагмент, который нужно вставить
     * @param Fragment $target фрагмент, В который нужно вставить
     * @param string $inTag тег, в который будет вставлен фрагмент
     * @param bool $refresh нужно ли обновить рендеринг фрагмента $fragment перед вставкой
     * @return Templater
     */
    public function merge (Fragment $fragment, Fragment $target, string $inTag, bool $refresh = false) : Templater {

        if($refresh){
            $fragment->render(
                $this->dictionary[self::main],
                !empty($this->dictionary['module'][$fragment->getParentModule()]) ? $this->dictionary['module'][$fragment->getParentModule()] : [],
                true
            );
        }

        $target->merge($fragment,$inTag);

        return $this;
    }

    /**
     * @param string $name название фрагмента
     * @param string|null $moduleName название модуля, где был впервые использован
     * @return Fragment|null
     */
    public function getFragment(string $name,string $moduleName = null) : ?Fragment {
        return $this->fragments[$moduleName ?? $this->currentModuleName][$name]['fragment'] ?? null;
    }

    /**
     * результирующий фрагмент, в который через @<имя_модуля> можно подключить все фрагменты
     * @param string $templateName
     * @param bool $useRootPath
     * @return Fragment
     */
    public function release(string $templateName, bool $useRootPath = true) : Fragment{

        $modules = array();

        foreach ($this->fragments as $module){
            $fragmentsCount = \count($module);

            foreach ($module as $fragment) {
                if($fragmentsCount === $fragment['q']){
                    $fragment['fragment']->release();
                    $modules[$this->openTag . '@'.$fragment['fragment']->getParentModule() . $this->closeTag] = $fragment['fragment']->getContainer();
                    unset($this->fragments[$fragment['fragment']->getParentModule()]);
                }
            }
        }

        $this->currentModule = new Fragment(
            !$useRootPath || empty($this->themeRoot) ? $templateName : $this->themeRoot . DIRECTORY_SEPARATOR . $templateName,
            $this->currentModuleName,
            $this->openTag,
            $this->closeTag
        );

        $this->currentModule->render(
            $this->dictionary[self::main],
            $modules
        );

        return $this->currentModule;
    }

}
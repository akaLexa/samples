<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 05.08.2018
 **/

namespace mwce\Templater;

/**
 * Class Fragment
 * @package mwce\Templater
 * @version 1.0
 * Класс - парсер шаблонов. Является составной частью механизма шаблонизатора в паре с Templater. Данный класс отвечает
 * за парсинг и хранение текста шаблона, как распаршенного, так и исходного.
 */
class Fragment
{
    /**
     * текст шаблона после прасинга
     * @var string
     */
    private $container;

    /**
     * не отпаршенный текст шаблона
     * @var string
     */
    private $document;

    /**
     * @var string
     */
    private $openTag;

    /**
     * @var string
     */
    private $closeTag;

    /**
     * название модуля, в котором был иницииализирован фрагмент
     * @var string
     */
    private $baseModule;

    /**
     * @var bool состояние фрагмента. если true, уже значит, что фрагмент почищен от неиспользованных тегов.
     */
    private $isRelease = false;

    /**
     * Fragment constructor.
     * @param string $tpl
     * @param string $baseModule
     * @param string $openTag
     * @param string $closeTag
     */
    public function __construct(string $tpl, string $baseModule, string $openTag, string $closeTag){

        if(file_exists($tpl)){
            $this->document = file_get_contents($tpl);
        }

        //todo: exception об отсутсвии файла

        $this->openTag = $openTag;
        $this->closeTag = $closeTag;
        $this->baseModule = $baseModule;
    }

    /**
     * геттер названия модуля, в котором был инициализирован фрагмент
     * @return string
     */
    public function getParentModule() : string {
        return $this->baseModule ?? '';
    }

    /**
     * @param array $system
     * @param array $module
     * @param bool $refresh
     * @return string
     */
    public function render(array $system, array $module, bool $refresh = false) : string {

        if(!$refresh && !empty($this->container)){
            return $this->container;
        }

        $temp = $this->document;

        preg_match_all('/(' . preg_quote($this->openTag,'') . "\/){1}(.*)(" . preg_quote($this->closeTag,'') . '){1}/m', $temp, $tags);
        if(!empty($tags[2])){
            foreach ($tags[2] as $tagWord){
                preg_match_all('/(' . preg_quote($this->openTag,'') . $tagWord . preg_quote($this->closeTag,'') . '){1}(.*)(' . preg_quote($this->openTag,'') . "\/{$tagWord}" . preg_quote($this->closeTag,'') . '){1}/s', $temp, $parsedTags);

                if(
                    !empty($parsedTags)
                    && !empty($module)
                    && !empty($parsedTags[0])
                    && !empty($module[$parsedTags[1][0]])
                    && \is_array($module[$parsedTags[1][0]])
                ){
                    $fragment = $parsedTags[2][0];
                    $parsedFragments = '';

                    foreach ($module[$parsedTags[1][0]] as $lineNum => $data){
                        if(!\is_array($data)){
                            continue;
                        }

                        foreach ($data as $alias => $value) {
                            $parsedFragments .= str_replace($alias,$value,$fragment);
                        }
                    }
                    $parsedFragments = preg_replace('/(' . preg_quote($this->openTag,'') . "){1}(\/)?(.*)(" . preg_quote($this->closeTag,'') . '){1}/','',$parsedFragments);
                    $temp = str_replace($parsedTags[0][0],$parsedFragments,$temp);
                }
                else{
                    $temp = str_replace($parsedTags[0][0],'',$temp);
                }
            }
        }

        $temp = strtr($temp, $system);

        // все вложенные массивы очистить, чтобы небыло отрыжки.
        $collect = array_map(function ($a){
            if(\is_array($a)){
                return null;
            }
            return $a;
        },$module);

        $this->container = strtr($temp, $collect);

        return $this->getContainer();
    }

    /**
     * Вставляет выбранный фрагмент в текущий, в отмеченный тег
     * @param Fragment $fragment какой фрагмент прибавить
     * @param string $toTag в какой тег вписать
     * @return Fragment
     */
    public function merge (Fragment $fragment, string $toTag) : Fragment {

        $content = $fragment->getContainer();

        if(!empty($content)){
           $this->container = str_replace($this->openTag . $toTag . $this->closeTag, $content,$this->container);
        }

        return $this;
    }

    /**
     * распаршенный код шаблона.
     * @return string
     */
    public function getContainer() : string
    {
        return $this->container ?? '';
    }

    /**
     * нераспаршенный код шаблона
     * @return string
     */
    public function getSourceTemplate() : string {
        return $this->document ?? '';
    }

    /**
     * окончательный результат парсинга фрагмента, с затертыми неиспользуемыми тегами
     * @param bool $save сохранить ли конечный результат в контейнер фрагмента
     * @return string
     */
    public function release(bool $save = false) : string {

        if($save && null !== $this->container){
            $this->container = preg_replace('/(' . preg_quote($this->openTag,'') . "){1}(\/)?(.*)(" . preg_quote($this->closeTag,'') . '){1}/','',$this->container);
            $this->isRelease = true;
        }

        if($this->isRelease){
            return $this->container;
        }

        return null !== $this->container ? preg_replace('/(' . preg_quote($this->openTag,'') . "){1}(\/)?(.*)(" . preg_quote($this->closeTag,'') . '){1}/','',$this->container) : '';
    }

    public function show() : void {
        echo $this->container;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getContainer();
    }

    /*
    protected function recurseReplace(array $data, string $fragment) : string{
        $toReturn = '';
        foreach ($data as $alias => $value) {
            $toReturn .= str_replace($alias,\is_array($value) ? $this->recurseReplace($value,$fragment) : $value,$fragment);
        }
        return $toReturn;
    } */
}
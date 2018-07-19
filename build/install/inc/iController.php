<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 18.02.2017
 *
 **/
namespace  build\install\inc;

use mwce\Controllers\ModuleController;
use mwce\Tools\Content;

class iController extends ModuleController
{
    /**
     * iController constructor.
     * @param \mwce\Tools\Content $view
     * @param array $pages
     * @throws \mwce\Exceptions\ContentException
     */
    public function __construct(Content $view, array $pages)
    {
        parent::__construct($view, $pages);

        $this->view
            ->setName($this->className)
            ->add_dict('titles')
            ->add_dict($this->className) //подключаем словарь к модулю (если он, конечно, есть)
            ->set('currentPage',$this->className);

        if(!empty($this->pages[$this->className]['title'])){
            $this->view->replace($this->pages[$this->className]['title'],'title');
        } //выставляем заголовок текущего модуля заместо |title|
    }
}
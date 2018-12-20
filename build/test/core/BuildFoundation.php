<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 07.10.2018
 **/

namespace build\test\core;
use mwce\Interfaces\IBuildLoad;
use mwce\Routing\URLParser;
use mwce\Templater\Fragment;
use mwce\Templater\Templater;
use mwce\Tools\Tools;

class BuildFoundation implements IBuildLoad
{

    private $modulesList = array(
        'somePage',
        'page',
        'page1',
        'page2',
    );

    public function __construct() {

        if(!\in_array(URLParser::Parse()['controller'],$this->modulesList,false)){
            die('неизвестный котроллер');
        }

        Tools::debug(URLParser::Parse());
    }

    /**
     * глобальный показ результатов
     */
    public function getView() : void {
        echo ' show on screen';
    }

    public function checkModuleAccess() : bool {
        return false;
    }
/*
    public function checkPluginAccess() : bool {
        return false;
    }*/
}
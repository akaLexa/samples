<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 09.08.2018
 **/

namespace mwce\Routing;

use build\install\inc\AccessRouter;
use mwce\Session\Session;
use mwce\Tools\Tools;

class mwce
{
    /**
     * @var mwce
     */
    private static $instance;

    private function __construct()
    {
        Session::Init();

        //запуск не из cmd
        if(URLParser::Parse()['build'] === null){
            $startCFG = baseDir . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'configs.php';

            if(file_exists($startCFG)){
                $_cfg = require $startCFG;
                if(empty($_cfg['defaultBuild'])){
                    //todo: throw exception ?
                    die('parameter "defaultBuild" is empty. please check configs/configs.php file');
                }

                Session::Init()->build($_cfg['defaultBuild']);
            }
            else{
                //todo: throw exception ?
                die('configs/configs.php not found!');
            }
        }
        else{
            Session::Init()->build(URLParser::Parse()['build']);
        }
        Tools::debug(URLParser::Parse());
        // -> load main config to know build
        // -> configs
        // -> db
        // -> view
        // -> plugins
        // -> controller (module)

    }

    /**
     * @return mwce
     */
    public static function Start() : mwce {
        if (null === self::$instance){
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function show() : void {
        // -> view -> show
    }
}
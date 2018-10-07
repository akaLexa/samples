<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 09.08.2018
 **/

namespace mwce\Routing;

use mwce\Interfaces\IBuildLoad;
use mwce\Session\Session;
use mwce\Tools\Tools;

class mwce
{
    /**
     * @var mwce
     */
    private static $instance;

    /**
     * @var IBuildLoad
     */
    private $build;

    private function __construct()
    {
        Session::Init();

        $startCFG = baseDir . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'configs.php';

        if(file_exists($startCFG)){
            $_cfg = require $startCFG;

            if(empty($_cfg['build'])){
                //todo: throw exception ?
                die('file "configs/configs.php" is corrupted');
            }
        }
        else{
            //todo: throw exception ?
            die('configs/configs.php not found!');
        }

        //запуск не из CLI (cmd)
        if(URLParser::Parse()['build'] === null){
            if(empty(Session::Init()->build())){
                Session::Init()->build($_cfg['build']);
            }
        }
        else{
            Session::Init()->build(URLParser::Parse()['build']);
        }


        $buildPath = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Session::Init()->build();

        if (!file_exists($buildPath)) {
            die('Build "' . Session::Init()->build() . '" not found');
        }

        $buildStarter = 'build\\'.Session::Init()->build().'\\core\\BuildFoundation';

        if(!class_exists($buildStarter)){
            die('"' . $buildStarter . '"" not found');
        }

        try{
            $this->build = new $buildStarter();
            if(!$this->build instanceof IBuildLoad){
                die($buildStarter .' must implements IBuildLoad');
            }
        }
        catch (\Throwable $e){
            Tools::debug($e->getMessage(),$e->getTrace());
            exit;
        }
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
        $this->build->getView();
    }
}
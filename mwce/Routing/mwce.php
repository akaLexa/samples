<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 09.08.2018
 **/

namespace mwce\Routing;

use mwce\Session\Session;

class mwce
{
    /**
     * @var mwce
     */
    private static $instance;

    private function __construct()
    {
        Session::Init();
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
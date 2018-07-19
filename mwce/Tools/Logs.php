<?php

namespace mwce\Tools;

use mwce\db\Connect;

class Logs
{
    /**
     * @param \Exception | int $errNum
     * @param string $text
     */
    public static function log($errNum, $text = ''): void
    {
        try{
            $dbh = Connect::start((Configs::buildCfg('defLogConNum') !== false) ? Configs::buildCfg('defLogConNum') : Configs::globalCfg('defaultConNum'));

            if ($errNum instanceof \Exception) {
                if(method_exists($errNum,'getCountWrites')){
                    if($errNum->getCountWrites()>1) {
                        return;
                    }
                }
                $ec = $errNum->getCode() === 0 ? 3 : $errNum->getCode();
                $errf = substr($errNum->getFile(), 0, 254);
                $text = !method_exists($errNum, 'getLog') ? $errNum->getMessage() . ' Line: ' . $errNum->getLine() : $errNum->getLog() . ' Line: ' . $errNum->getLine();
            } else {
                $ec = $errNum;
                $errf = 'Router';
            }

            if(empty($_SERVER['REQUEST_URI'])) {
                $_SERVER['REQUEST_URI'] = 'under cmd';
            }

            $dbh->SQLog($text . '<br> Uri:' . $_SERVER['REQUEST_URI'], $errf, $ec);
        }
        catch (\Exception $e){
            self::textLog(1,!method_exists($errNum, 'getLog') ? $e->getMessage() : $errNum->getLog() .', file:'.$e->getFile().' WHEN try to log something else o0');
        }
    }

    /**
     * @param $errNum
     * @param string $text
     * @param bool $markTime
     */
    public static function textLog($errNum, $text = '',$markTime=false): void
    {
        if($markTime){
            $text = '['.date('H:i:s').'] '.$text;
        }
        file_put_contents(baseDir . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . '[' . date('d_m_Y') . ']' . Configs::currentBuild() . '_error_' . $errNum . '.log', $text . PHP_EOL, FILE_APPEND);
    }

}
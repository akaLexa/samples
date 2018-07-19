<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Errors;
use mwce\Tools\Configs;
use mwce\Tools\DicBuilder;
use Throwable;

class ValidatorError extends \Exception
{
    protected $value;

    public function __construct($value, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->value = $value;

        if($code !== 0){

            $lang = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() .DIRECTORY_SEPARATOR . 'lang' .DIRECTORY_SEPARATOR . Configs::curLang() .DIRECTORY_SEPARATOR . 'validateErrors.php');

            if(!empty($lang)){
                $message = sprintf($lang[$code],$message);
            }
        }

        parent::__construct($message, $code, $previous);
    }

    public function GetValue(){
        return $this->value;
    }
}
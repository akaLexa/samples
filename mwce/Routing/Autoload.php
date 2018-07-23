<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 24.03.2017
 *
 **/
if (PHP_VERSION_ID < 70100) {
    die('PHP version must be >= 7.1.0');
}

define('baseDir', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');

spl_autoload_register(function ($class) {

    $filename = baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($filename)) {
        include $filename;
    }
});
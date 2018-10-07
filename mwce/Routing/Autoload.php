<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 24.03.2017
 *
 **/

if (PHP_VERSION_ID < 70100) {
    die('PHP version must be >= 7.1.0');
}

spl_autoload_register(function ($class) {

    $filename = baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($filename)) {
        include $filename;
    }
});

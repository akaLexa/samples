<?php
/**
 * MuWebCloneEngine
 * Version: 1.6.4
 * User: epmak
 * 18.02.2017
 * ->
 **/

require __DIR__ . DIRECTORY_SEPARATOR . 'mwce' . DIRECTORY_SEPARATOR . 'Routing' . DIRECTORY_SEPARATOR . 'Autoload.php';
/*
$view = new \mwce\Templater\Templater('');
$view
    ->setModuleName('test')
    ->set('test','тестовое значение')
    ->set('test1',[0=>['some name0 '],1=>['some name1 ']])
    ->renderFragment('test2.html','included')
    ->renderFragment('test.html','firstTest')
    ->merge($view->getFragment('included'),$view->getFragment('firstTest'),'@include')
;

$view->release('mainTest.html')->show();
*/

$_  = \mwce\Routing\mwce::Start();

$app = mwce\Routing\Router::start();
$app->startPlugins();
$app->startModules();
$app->show();
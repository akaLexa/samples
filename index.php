<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 18.02.2017
 * ->.
 **/
//$start = microtime(true);

const baseDir = __DIR__ ;

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

$web  = \mwce\Routing\mwce::Start();
$web->show();

//echo microtime(true) - $start;
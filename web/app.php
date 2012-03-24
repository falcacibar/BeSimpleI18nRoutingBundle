<?php
if (in_array(@$_SERVER['REMOTE_ADDR'], array(
    '127.0.0.1',
    '1190.45.213.158',
    '::1',
))) {
    header('HTTP/1.0 403 Forbidden');
    exit('Estamos teniendo problemas, volveremos en unos minutos!');
}

require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('prod', true);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
$kernel->handle(Request::createFromGlobals())->send();

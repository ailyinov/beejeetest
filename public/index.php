<?php

use BeeJee\Kernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

if ($config->get('APP_DEBUG')) {
    ini_set('display_startup_errors', true);
    error_reporting(E_ALL);
    ini_set('display_errors', true);
}

try {
    $kernel = new Kernel($routeDispatcher, $config, $twig);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
} catch (Exception $ex) {
    echo '<pre>';
    echo $ex->getMessage();
    print_r($ex->getTrace());
}
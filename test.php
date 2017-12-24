<?php
require_once "vendor/autoload.php";

$server = new \CloudSky\JsonRpc\Server("test");

$server->addMethod('test', function($p){

    return ($p['a'] + $p['b']);

});

$server->listen('websocket://127.0.0.1:23380');

\Workerman\Worker::runAll();
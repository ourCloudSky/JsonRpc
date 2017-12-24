<?php
require_once "vendor/autoload.php";

$worker = new \Workerman\Worker();
$worker->onWorkerStart = function($s){
    
    $client = new \CloudSky\JsonRpc\Client('tcp://127.0.0.1:23380');

    $client->test(["a"=>1, "b"=>2], function($d){
        echo $d;
    });

};

\Workerman\Worker::runAll();
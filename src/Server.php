<?php
    /**
     * CloudSky JsonRpc
     * 
     * This program is part of CloudSky, licensed
     * under MIT. Please use it under the license.
     * 
     * @license MIT
     * @package JsonRpc
     * @author  xtl <xtl@xtlsoft.top>
     * 
     */

    namespace CloudSky\JsonRpc;

    class Server {

        protected $worker;
        protected $name;
        protected $method = [];

        public function __construct($name = "no_name"){

            $this->name = $name;

        }

        public function addMethod($name, $call){

            $this->method[$name] = $call;

            return $this;

        }

        public function addObject($name, $obj){

            $ref = new \ReflectionClass($obj);

            $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach($methods as $v){

                $this->method[$name . '.' . $v->name] = [$obj, $v->name];

            }

            return $this;

        }

        public function listen($uri, $count = 3){

            $worker = new \Workerman\Worker($uri);
            $worker->count = $count;
            $worker->name = "JsonRpc_" . $this->name;

            $this->worker = $worker;

            $this->worker->onMessage = [$this, "handleRequest"];

            return $this;

        }

        public function getWorker(){

            return $this->worker;

        }

        public function getName(){

            return $this->name;

        }

        public function handleRequest($conn, $data){

            $data = json_decode($data, 1);

            $method = $data['method'];

            if(!isset($this->method[$method])){
                
                $conn->send(json_encode(
                    [
                        "jsonrpc"=> "2.0",
                        "result" => null,
                        "error"  => -32601,
                        "id"     => $data['id']
                    ]
                ));

                return;

            }

            try{
                $rslt = call_user_func($this->method[$method], $data['params']);
            }catch(\Exception $e){
                $msg = $e->getMessage();
            }

            $conn->send(json_encode(
                [
                    "jsonrpc"=> "2.0",
                    "result" => $rslt,
                    "error"  => isset($msg) ? $msg : null,
                    "id"     => $data['id']
                ]
            ));

        }

    }

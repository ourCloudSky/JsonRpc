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

    class Client {

        protected $conn;
        protected $result = [];
        protected $callback = [];
        protected $id = 1;

        public function __construct($addr, $connect = 1){

            $this->conn = new \Workerman\Connection\AsyncTcpConnection($addr);

            if($connect){
                $this->connect();
            }

        }

        public function getConnection(){

            return $this->conn;

        }

        public function handleMessage($conn, $data){

            $data = json_decode($data, 1);

            if($data['error']){

                $this->result[$data['id']] = ['error' => $data['error']];

                throw new \Exception('CloudSky_JsonRpc_Error: ' . $data['error']);

            }

            $this->result[$data['id']] = $data['result'];

            $this->handleResultCallback($data['id']);

        }

        public function handleResultCallback($id){

            if(isset($this->callback[$id])){

                $callback = $this->callback[$id];

                $callback($this->result[$id]);

                unset($this->callback[$id]);
                unset($this->result[$id]);

                return true;

            }else{
                return false;
            }

        }

        public function call($name, $param = []){

            $id = $this->callAsync($name, $param);

            return $this->recv($id);

        }

        public function generateId(){

            ++$this->id;
            return $this->id;

        }

        public function callAsync($name, $param = [], $callback = null){

            $id = $this->generateId();

            if($callback !== null){
                $this->callback[$id] = $callback;
            }

            $this->conn->send(json_encode([
                "jsonrpc" => "2.0",
                "method"  => $name,
                "params"  => $param,
                "id"      => $id
            ]));

            if($callback !== null){
                return $this;
            }else{
                return $id;
            }

        }

        public function recv($id){

            while(@!$this->result[$id]){
                sleep(0.01);
            }

            return $this->result[$id];

        }

        public function __call($name, $p){

            if(isset($p[1])){
                return $this->callAsync($name, $p[0], $p[1]);
            }else{
                return $this->call($name, $p[0]);
            }

        }

        public function connect(){

            $ok = false;

            $this->conn->onConnect = function ($conn) use (&$ok){
                $ok = true;
            };

            $this->conn->onMessage = [$this, "handleMessage"];
            
            $this->conn->connect();

            // while($ok === false){
            //     sleep(0.01);
            // }

            return $this;

        }

        public function reconnect(){
            
            $this->conn->reconnect();

        }

    }
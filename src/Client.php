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
        protected $result;

        public function __construct($addr, $connect = 1){

            $this->conn = new \Workerman\Connection\AsyncTcpConnection($addr);

            if($connect){
                $this->connect();
            }

        }

        public function getConnection(){

            return $this->conn;

        }

        public function recv($conn, $data){

            $data = json_decode($data, 1);

            if($data['error']){

                $this->result[$data['id']] = ['error' => $data['error']];

                throw new \Exception('CloudSky_JsonRpc_Error: ' . $data['error']);

            }

            $this->result[$data['id']] = $data['result'];

            $this->handleResultCallback($data['id']);

        }

        public function handleResultCallback($id){



        }

        public function connect(){

            $ok = false;

            $this->conn->onConnect = function ($conn) use (&$ok){
                $ok = true;
            };

            $this->conn->onMessage = [$this, "recv"];

            $this->conn->connect();

            while($ok === false){
                sleep(0.01);
            }

            return $this;

        }

        public function reconnect(){
            
            $this->conn->reconnect();

        }

    }
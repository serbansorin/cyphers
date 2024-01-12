<?php

class DB {
    public $connection;
    private static $instance = null;
    private $host = config('db.host');
    private $port = config('db.port');
    private $username = config('db.username');
    private $password = config('db.password');
    private $database = config('db.database');


    private function __construct() {
        $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->database);
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    public static function query($query) 
    {
        $db = new self();
        $result = $db->connection->query($query);
        $db->connection->close();
        return new Collection($result->fetch_all(MYSQLI_ASSOC));
    }

    public static function selectRaw($query) 
    {
        $db = new self();
        $result = $db->connection->query($query);
        $db->connection->close();
        return new Collection($result->fetch_all(MYSQLI_ASSOC));
    }


    public function __destruct() {
        $this->connection->close();
    }

    public function __call($name, $arguments) {
        return $this->connection->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments) {
        $db = new self();
        return $db->$name(...$arguments);
    }

    public function __get($name) {
        return $this->connection->$name;
    }

    public function __set($name, $value) {
        $this->connection->$name = $value;
    }

    public function __isset($name) {
        return isset($this->connection->$name);
    }

    public function __unset($name) {
        unset($this->connection->$name);
    }

    public function __sleep() {
        return ['connection'];
    }

    public function __wakeup() {
        $this->connection = new \mysqli('localhost', 'username', 'password', 'database');
    }

    public function __toString() {
        return $this->connection->host_info;
    }

    public function __invoke() {
        return $this->connection->host_info;
    }

    public function __debugInfo() {
        return [
            // 'host' => $this->connection->host_info,
            // 'user' => $this->connection->user,
            // 'db' => $this->connection->db,
        ];
    }

}

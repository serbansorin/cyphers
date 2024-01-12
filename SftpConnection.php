<?php

class SftpConnection
{

    public static $instance;
    private $host;
    private $port;
    private $username;
    private $password;
    private $connection;

    private function __construct($host, $port, $username, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public static function getInstance($host, $port, $username, $password)
    {
        if (self::$instance) {
            return self::$instance;
        }
        
        return self::$instance = new self($host, $port, $username, $password);
    }

    public function connect()
    {
        $this->connection = ssh2_connect($this->host, $this->port);
        if (!$this->connection) {
            throw new Exception("Failed to connect to SFTP server");
        }

        if (!ssh2_auth_password($this->connection, $this->username, $this->password)) {
            throw new Exception("Failed to authenticate with SFTP server");
        }
    }

    public function disconnect()
    {
        if ($this->connection) {
            ssh2_disconnect($this->connection);
        }
    }

    public function uploadFile($localFilePath, $remoteFilePath)
    {
        if (!$this->connection) {
            throw new Exception("Not connected to SFTP server");
        }

        if (!ssh2_scp_send($this->connection, $localFilePath, $remoteFilePath)) {
            throw new Exception("Failed to upload file to SFTP server");
        }
    }

    public function downloadFile($remoteFilePath, $localFilePath)
    {
        if (!$this->connection) {
            throw new Exception("Not connected to SFTP server");
        }

        if (!ssh2_scp_recv($this->connection, $remoteFilePath, $localFilePath)) {
            throw new Exception("Failed to download file from SFTP server");
        }
    }

    public function run($localFilePath, $remoteFilePath)
    {

        try {
            $this->connect();
            $this->downloadFile($remoteFilePath, $localFilePath);
            $this->disconnect();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}

?>
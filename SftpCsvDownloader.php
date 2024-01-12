<?php

use phpseclib\Net\SFTP;

class SftpCsvDownloader
{
    private $sftp;

    public function __construct($host, $port, $username, $password)
    {
        $this->sftp = new SFTP($host, $port);
        if (!$this->sftp->login($username, $password)) {
            throw new Exception('SFTP login failed');
        }
    }

    public function downloadCsv($remoteFilePath, $localFilePath)
    {
        if (!$this->sftp->get($remoteFilePath, $localFilePath)) {
            throw new Exception('Failed to download CSV file');
        }
    }

    public static function init()
    {
        // Create an instance of SftpCsvDownloader
        $host = 'example.com';
        $port = 22;
        $username = 'your_username';
        $password = 'your_password';
        $downloader = new SftpCsvDownloader($host, $port, $username, $password);

        // Download a CSV file
        $remoteFilePath = '/path/to/remote/file.csv';
        $localFilePath = '/path/to/local/file.csv';
        try {
            $downloader->downloadCsv($remoteFilePath, $localFilePath);
            echo 'CSV file downloaded successfully!';
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}




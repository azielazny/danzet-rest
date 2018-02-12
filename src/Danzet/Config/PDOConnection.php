<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 09.02.2018
 * Time: 21:03
 */
namespace Danzet\Config;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class PDOConnection {
    private $db;
    private $logger;
    private $file_handler;

    public function __construct($c) {
        $this->db = $c;
        $this->logger = new Logger('PDOConnection');
        $this->file_handler=new StreamHandler('../logs/database.log');
        $this->logger->pushHandler($this->file_handler);
    }


    public function connection($dbType) {
        try {
            $options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            );
            $pdo = new \PDO($dbType.":host=" . $this->db['servername'] . ";dbname=" . $this->db['dbname'],
                $this->db['username'], $this->db['password'], $options);
            return $pdo;
        } catch (\Exception $ex) {
            $this->logger->addWarning('Connection error to: '.$dbType);
            return $ex->getMessage();
        }
    }




}
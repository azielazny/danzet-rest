<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 09.02.2018
 * Time: 21:03
 */

namespace Danzet\Config;

class PDOConnection
{
    private $db;
    private $logger;

    public function __construct($c)
    {
        $this->db = $c;
        $monologLogger = new MonologLogger();
        $this->logger = $monologLogger->log('PDOConnection');
    }


    public function connection($dbType)
    {
        try {
            $options = array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
            );
            $pdo = new \PDO($dbType . ":host=" . $this->db['servername'] . ";dbname=" . $this->db['dbname'],
                $this->db['username'], $this->db['password'], $options);
            $this->logger->debug('Success connection to: ' . $dbType);
            return $pdo;
        } catch (\Exception $ex) {
            $this->logger->warning('Connection error to: ' . $dbType);
            return $ex->getMessage();
        }
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 08.02.2018
 * Time: 18:54
 */
use \Monolog\Logger as Logger;
use \Monolog\Handler\StreamHandler as StreamHandler;


$routeFiles = (array)glob(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR
    . 'Danzet' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . '*.php');

require __DIR__ . '/../vendor/autoload.php';
include 'config.php';



$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$database = new \Danzet\Config\PDOConnection($config['db']);
$container['db'] = $database->connection('mysql');

$container['logger'] = function () {
    $logger = new \Monolog\Logger('router');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/activity.log');
    $logger->pushHandler($file_handler);
    return $logger;
};



foreach ($routeFiles as $routeFile) {
    require_once $routeFile;
}
$app->run();
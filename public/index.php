<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 08.02.2018
 * Time: 18:54
 */

$routeFiles = (array)glob(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR
    . 'Danzet' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . '*.php');

require __DIR__ . '/../vendor/autoload.php';
include 'config.php';

/**
 * initialize slim framework
 */
$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

/**
 * initialize database
 */
$database = new \Danzet\Config\PDOConnection($config['db']);
$container['db'] = $database->connection('mysql');

/**
 * initialize monolog logger
 */
$monologLogger = new \Danzet\Config\MonologLogger();
$container['logger'] = $monologLogger->log('Router');

foreach ($routeFiles as $routeFile) {
    require_once $routeFile;
}
$app->run();
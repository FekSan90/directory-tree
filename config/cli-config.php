<?php
require_once "vendor/autoload.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

$paths = array(__DIR__ . "/../module/DocumentManager/src/Model");
$isDevMode = true;

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);

$conn = array(
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'port' => '3306',
    'user' => 'root',
    'password' => '0123456',
    'dbname' => 'document-manager',
    'charset' => 'utf8',
    'driverOptions' => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    ]
);

$entityManager = EntityManager::create($conn, $config);

return ConsoleRunner::createHelperSet($entityManager);
<?php
// bootstrap.php
require_once "vendor/autoload.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

$paths = [__DIR__ . "/src/Entity"];
$isDevMode = true;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbParams = [
    'dbname' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'host' => $_ENV['DB_HOST'],
    'driver' => $_ENV['DB_DRIVER'],
    'charset' => $_ENV['DB_CHARSET'],
];

$config = Setup::createAttributeMetadataConfiguration($paths, $isDevMode);
$connection = DriverManager::getConnection($dbParams, $config);
$entityManager = new EntityManager($connection, $config);

return $entityManager;

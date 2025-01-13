<?php

namespace App\Utils;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Dotenv\Dotenv;

class EntityManagerCreator
{
    public static function createEntityManager(): EntityManager
    {
        //        $dotenv = Dotenv::createImmutable(__DIR__ );
        //        $dotenv->load();

        $paths = [__DIR__ . "/../../src/Entity"];
        $isDevMode = true;

        $dbParams = [
            "dbname" => $_ENV["DB_NAME"],
            "user" => $_ENV["DB_USER"],
            "password" => $_ENV["DB_PASSWORD"],
            "host" => $_ENV["DB_HOST"],
            "driver" => $_ENV["DB_DRIVER"],
            "charset" => $_ENV["DB_CHARSET"],
        ];

        $config = Setup::createAttributeMetadataConfiguration(
            $paths,
            $isDevMode
        );
        $connection = DriverManager::getConnection($dbParams, $config);
        return new EntityManager($connection, $config);
    }
}

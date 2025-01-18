<?php

namespace App\Utils;

use App\ORM\EntityManager;
use PDO;

class EntityManagerCreator
{
    public static function createEntityManager(): EntityManager
    {
        $dbParams = [
            "dbname" => $_ENV["DB_NAME"],
            "user" => $_ENV["DB_USER"],
            "password" => $_ENV["DB_PASSWORD"],
            "host" => $_ENV["DB_HOST"],
            "driver" => $_ENV["DB_DRIVER"],
            "charset" => $_ENV["DB_CHARSET"],
        ];


        try {
            $pdo = new PDO(
                "{$dbParams['driver']}:host={$dbParams['host']};dbname={$dbParams['dbname']};charset={$dbParams['charset']}",
                $dbParams["user"],
                $dbParams["password"]
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e){
            throw new \Exception("Failed connect to database: ".$e->getMessage());
        }

        return new EntityManager($pdo);
    }
}
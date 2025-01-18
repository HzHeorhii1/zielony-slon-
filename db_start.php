<?php
require_once __DIR__ . '/bootstrap.php';
use App\Utils\EntityManagerCreator;


// .env
$dotenvPath = __DIR__ . DIRECTORY_SEPARATOR . '../.env';
if (file_exists($dotenvPath)) {
    $env = parse_ini_file($dotenvPath);
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

$entityManager = EntityManagerCreator::createEntityManager();
$connection = $entityManager->getConnection();

$sql = <<<SQL
    SET FOREIGN_KEY_CHECKS = 0;
    
    DROP TABLE IF EXISTS schedules;
    DROP TABLE IF EXISTS study_groups;
    DROP TABLE IF EXISTS rooms;
    DROP TABLE IF EXISTS subjects;
    DROP TABLE IF EXISTS workers;
    DROP TABLE IF EXISTS User;
    
    CREATE TABLE User (
        userld INT AUTO_INCREMENT PRIMARY KEY,
        nrAlbumu INT
    );
    
    CREATE TABLE workers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255)
    );
    
    CREATE TABLE rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255)
    );
    
    CREATE TABLE study_groups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255)
    );
    
    CREATE TABLE subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255)
    );
    
    CREATE TABLE schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        description TEXT,
        startTime DATETIME,
        endTime DATETIME,
        worker_id INT NULL,
        room_id INT NULL,
        group_id INT NULL,
        subject_id INT NULL,
        user_id INT NULL,
        lessonForm VARCHAR(255) NULL,
        FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE SET NULL,
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
        FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE SET NULL,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES User(userld) ON DELETE SET NULL
    );
    
    SET FOREIGN_KEY_CHECKS = 1;
SQL;

try {
    $connection->exec($sql);
    echo "db created successfully!\n";
} catch (\PDOException $e) {
    echo "oops smth went wrong: " . $e->getMessage() . "\n";
}
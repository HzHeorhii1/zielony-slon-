<?php

require_once __DIR__ . '/bootstrap.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;

if (!isset($entityManager)) {
    throw new RuntimeException("EntityManager is not initialized.");
}

return ConsoleRunner::createHelperSet($entityManager);

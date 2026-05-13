<?php

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = getDatabaseConnection();

    $schemaPath = __DIR__ . '/schema.sql';
    $sql = file_get_contents($schemaPath);

    if ($sql === false) {
        throw new Exception('Could not read schema.sql.');
    }

    $pdo->exec($sql);

    echo 'Database created successfully.';
} catch (Throwable $error) {
    echo 'Database setup failed: ' . $error->getMessage();
}

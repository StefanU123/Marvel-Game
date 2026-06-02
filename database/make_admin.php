<?php
require_once __DIR__ . '/../includes/db.php';

$username = 'stefan';

$pdo = getDatabaseConnection();

$statement = $pdo->prepare("UPDATE users SET role = 'admin' WHERE username = :username");
$statement->execute([
    'username' => $username,
]);

echo 'User ' . htmlspecialchars($username) . ' is now an admin.';

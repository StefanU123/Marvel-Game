<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

requireAdmin();

$id = $_GET['id'] ?? '';

if (!is_string($id) || !ctype_digit($id) || (int) $id <= 0) {
    echo 'Invalid question id.';
    exit;
}

$pdo = getDatabaseConnection();

$deleteStatement = $pdo->prepare('DELETE FROM questions WHERE id = :id');
$deleteStatement->execute([
    'id' => (int) $id,
]);

header('Location: questions.php');
exit;

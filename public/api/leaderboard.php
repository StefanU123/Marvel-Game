<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

function sendJson(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$heroId = filter_input(INPUT_GET, 'hero_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$difficulty = $_GET['difficulty'] ?? null;
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, [
    'options' => ['default' => 20, 'min_range' => 1, 'max_range' => 100],
]);

$allowedDifficulties = ['easy', 'medium', 'hard'];
if ($difficulty !== null && $difficulty !== '' && !in_array($difficulty, $allowedDifficulties, true)) {
    sendJson(['error' => 'Invalid difficulty.'], 400);
}

try {
    $pdo = getDatabaseConnection();

    $where = [];
    $params = [];

    if ($heroId !== null && $heroId !== false) {
        $where[] = 's.hero_id = :hero_id';
        $params[':hero_id'] = $heroId;
    }
    if ($difficulty) {
        $where[] = 's.difficulty = :difficulty';
        $params[':difficulty'] = $difficulty;
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT u.username,
                   SUM(s.score) AS total_score,
                   COUNT(s.id) AS games_played,
                   MAX(s.score) AS best_score
            FROM scores s
            JOIN users u ON u.id = s.user_id
            $whereSql
            GROUP BY s.user_id, u.username
            ORDER BY total_score DESC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll();

    sendJson(['leaderboard' => $rows]);
} catch (Throwable $error) {
    sendJson(['error' => 'Could not load leaderboard.'], 500);
}

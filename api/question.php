<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

function sendJson(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$heroId = filter_input(INPUT_GET, 'hero_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$difficulty = $_GET['difficulty'] ?? '';
$allowedDifficulties = ['easy', 'medium', 'hard'];

if ($heroId === false || $heroId === null) {
    sendJson(['error' => 'Invalid hero_id.'], 400);
}

if (!in_array($difficulty, $allowedDifficulties, true)) {
    sendJson(['error' => 'Invalid difficulty.'], 400);
}

try {
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT id, question_text, option_a, option_b, option_c, option_d
         FROM questions
         WHERE hero_id = :hero_id AND difficulty = :difficulty
         ORDER BY RANDOM()
         LIMIT 1'
    );
    $statement->execute([
        ':hero_id' => $heroId,
        ':difficulty' => $difficulty,
    ]);

    $question = $statement->fetch();

    if (!$question) {
        sendJson(['error' => 'No question found.'], 404);
    }

    sendJson($question);
} catch (Throwable $error) {
    sendJson(['error' => 'Could not load question.'], 500);
}

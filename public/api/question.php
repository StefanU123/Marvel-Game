<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

startSessionIfNeeded();

function sendJson(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

const QUESTIONS_PER_QUIZ = 5;

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

    $heroStmt = $pdo->prepare('SELECT id, name FROM heroes WHERE id = :id');
    $heroStmt->execute([':id' => $heroId]);
    $hero = $heroStmt->fetch();

    if (!$hero) {
        sendJson(['error' => 'Hero not found.'], 404);
    }

    $statement = $pdo->prepare(
        'SELECT id, question_text, option_a, option_b, option_c, option_d
         FROM questions
         WHERE hero_id = :hero_id AND difficulty = :difficulty
         ORDER BY RANDOM()
         LIMIT :limit'
    );
    $statement->bindValue(':hero_id', $heroId, PDO::PARAM_INT);
    $statement->bindValue(':difficulty', $difficulty, PDO::PARAM_STR);
    $statement->bindValue(':limit', QUESTIONS_PER_QUIZ, PDO::PARAM_INT);
    $statement->execute();
    $questions = $statement->fetchAll();

    if (count($questions) === 0) {
        sendJson(['error' => 'No questions available for this combination.'], 404);
    }

    $_SESSION['active_quiz'] = [
        'hero_id'      => (int) $heroId,
        'difficulty'   => $difficulty,
        'question_ids' => array_map(static fn ($q) => (int) $q['id'], $questions),
        'started_at'   => time(),
    ];

    sendJson([
        'hero'       => ['id' => (int) $hero['id'], 'name' => $hero['name']],
        'difficulty' => $difficulty,
        'questions'  => $questions,
    ]);
} catch (Throwable $error) {
    sendJson(['error' => 'Could not load questions.'], 500);
}

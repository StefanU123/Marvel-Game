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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['error' => 'Method not allowed.'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    sendJson(['error' => 'Invalid request body.'], 400);
}

$answers   = $payload['answers']   ?? null;
$timeTaken = isset($payload['time_taken']) ? (int) $payload['time_taken'] : 0;

if (!is_array($answers)) {
    sendJson(['error' => 'Answers must be provided as an object.'], 400);
}

if (!isset($_SESSION['active_quiz'])) {
    sendJson(['error' => 'No active quiz. Start a new one first.'], 400);
}

$quiz       = $_SESSION['active_quiz'];
$heroId     = (int) $quiz['hero_id'];
$difficulty = (string) $quiz['difficulty'];
$questionIds = $quiz['question_ids'];

$pointsByDifficulty = [
    'easy'   => 10,
    'medium' => 20,
    'hard'   => 30,
];
$basePoints = $pointsByDifficulty[$difficulty] ?? 10;

try {
    $pdo = getDatabaseConnection();

    $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option
         FROM questions
         WHERE id IN ($placeholders)"
    );
    $stmt->execute($questionIds);
    $rows = $stmt->fetchAll();

    $byId = [];
    foreach ($rows as $row) {
        $byId[(int) $row['id']] = $row;
    }

    $results = [];
    $correctCount = 0;

    foreach ($questionIds as $qid) {
        if (!isset($byId[$qid])) {
            continue;
        }
        $question = $byId[$qid];
        $selected = isset($answers[$qid]) ? strtoupper(trim((string) $answers[$qid])) : null;
        if (!in_array($selected, ['A', 'B', 'C', 'D'], true)) {
            $selected = null;
        }
        $isCorrect = $selected !== null && $selected === $question['correct_option'];
        if ($isCorrect) {
            $correctCount++;
        }

        $results[] = [
            'question_id'    => (int) $qid,
            'question_text'  => $question['question_text'],
            'options'        => [
                'A' => $question['option_a'],
                'B' => $question['option_b'],
                'C' => $question['option_c'],
                'D' => $question['option_d'],
            ],
            'correct_option' => $question['correct_option'],
            'selected'       => $selected,
            'is_correct'     => $isCorrect,
        ];
    }

    $total = count($questionIds);
    $score = $correctCount * $basePoints;

    // Time bonus: up to 25% extra if completed quickly
    $expected = $total * 15;
    if ($timeTaken > 0 && $timeTaken < $expected && $correctCount > 0) {
        $bonusRatio = max(0.0, min(0.25, ($expected - $timeTaken) / max(1, $expected) * 0.25));
        $score += (int) round($score * $bonusRatio);
    }

    // Perfect-game flat bonus
    $perfect = ($correctCount === $total);
    if ($perfect) {
        $score += $basePoints * 2;
    }

    $user = currentUser();
    $saved = false;
    if ($user !== null) {
        // Defensive: the session might reference a user that no longer exists
        // (e.g., DB was reseeded after they logged in). Drop the stale session
        // rather than 500ing on the FK constraint.
        $userCheck = $pdo->prepare('SELECT 1 FROM users WHERE id = :id');
        $userCheck->execute([':id' => (int) $user['id']]);
        if ($userCheck->fetchColumn()) {
            $insert = $pdo->prepare(
                'INSERT INTO scores (user_id, hero_id, difficulty, score, total_questions, correct_count, time_taken)
                 VALUES (:user_id, :hero_id, :difficulty, :score, :total, :correct, :time_taken)'
            );
            $insert->execute([
                ':user_id'    => (int) $user['id'],
                ':hero_id'    => $heroId,
                ':difficulty' => $difficulty,
                ':score'      => $score,
                ':total'      => $total,
                ':correct'    => $correctCount,
                ':time_taken' => $timeTaken,
            ]);
            $saved = true;
        } else {
            unset($_SESSION['user']);
            $user = null;
        }
    }

    unset($_SESSION['active_quiz']);

    sendJson([
        'score'           => $score,
        'correct_count'   => $correctCount,
        'total_questions' => $total,
        'perfect'         => $perfect,
        'difficulty'      => $difficulty,
        'hero_id'         => $heroId,
        'time_taken'      => $timeTaken,
        'saved'           => $saved,
        'logged_in'       => $user !== null,
        'results'         => $results,
    ]);
} catch (Throwable $error) {
    error_log('submit.php failed: ' . $error->getMessage() . ' @ ' . $error->getFile() . ':' . $error->getLine());
    sendJson(['error' => 'Could not submit quiz.'], 500);
}

<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/i18n.php';

startSessionIfNeeded();
$useRo = (lang() === 'ro');

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
        'SELECT id, question_text, option_a, option_b, option_c, option_d,
                question_text_ro, option_a_ro, option_b_ro, option_c_ro, option_d_ro,
                correct_option
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

    // Shuffle the answer options per question so the correct answer is not
    // always in the same slot (the raw seed data is biased toward A/B). The
    // correct letter after shuffling is kept server-side in the session and is
    // never sent to the client, so grading stays tamper-proof.
    $letters = ['A', 'B', 'C', 'D'];
    $sessionQuestions = [];
    $clientQuestions = [];

    // Pick the localized value, falling back to English when a translation is missing.
    $loc = static function (?string $ro, string $en) use ($useRo): string {
        return ($useRo && $ro !== null && $ro !== '') ? $ro : $en;
    };

    foreach ($questions as $q) {
        $questionTextLoc = $loc($q['question_text_ro'] ?? null, $q['question_text']);
        $pairs = [
            ['orig' => 'A', 'text' => $loc($q['option_a_ro'] ?? null, $q['option_a'])],
            ['orig' => 'B', 'text' => $loc($q['option_b_ro'] ?? null, $q['option_b'])],
            ['orig' => 'C', 'text' => $loc($q['option_c_ro'] ?? null, $q['option_c'])],
            ['orig' => 'D', 'text' => $loc($q['option_d_ro'] ?? null, $q['option_d'])],
        ];
        shuffle($pairs);

        $displayOptions = [];
        $correctDisplay = 'A';
        foreach ($pairs as $i => $pair) {
            $letter = $letters[$i];
            $displayOptions[$letter] = $pair['text'];
            if ($pair['orig'] === $q['correct_option']) {
                $correctDisplay = $letter;
            }
        }

        $qid = (int) $q['id'];
        $sessionQuestions[$qid] = [
            'question_text' => $questionTextLoc,
            'options'       => $displayOptions,
            'correct'       => $correctDisplay,
        ];
        $clientQuestions[] = [
            'id'            => $qid,
            'question_text' => $questionTextLoc,
            'option_a'      => $displayOptions['A'],
            'option_b'      => $displayOptions['B'],
            'option_c'      => $displayOptions['C'],
            'option_d'      => $displayOptions['D'],
        ];
    }

    $_SESSION['active_quiz'] = [
        'hero_id'    => (int) $heroId,
        'difficulty' => $difficulty,
        'questions'  => $sessionQuestions,
        'started_at' => time(),
    ];

    sendJson([
        'hero'       => ['id' => (int) $hero['id'], 'name' => $hero['name']],
        'difficulty' => $difficulty,
        'questions'  => $clientQuestions,
    ]);
} catch (Throwable $error) {
    sendJson(['error' => 'Could not load questions.'], 500);
}

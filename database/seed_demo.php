<?php

/**
 * seed_demo.php — populates the database with demo players and scores so the
 * leaderboard looks alive during development / demos.
 *
 * Safe to re-run: it skips users that already exist (INSERT OR IGNORE) and only
 * adds score rows for freshly created demo users, so it will not pile up
 * duplicate games on repeated runs.
 *
 * Usage:  php database/seed_demo.php
 */

require_once __DIR__ . '/../includes/db.php';

$pdo = getDatabaseConnection();

// Demo players. The password for every demo account is "demo1234".
$demoUsers = [
    'PeterP', 'TonyS', 'SteveR', 'TChalla', 'ThunderGod', 'BruceB',
    'StephenV', 'WandaM', 'LoganX', 'NatashaR', 'CarolD', 'ScottL',
    'WadeW', 'MattM', 'GamoraZ', 'GrootIAm', 'RocketR', 'NickF',
    'SamW', 'BuckyB', 'ShuriW', 'PepperP',
];

$passwordHash = password_hash('demo1234', PASSWORD_DEFAULT);

$heroCount = (int) $pdo->query('SELECT COUNT(*) FROM heroes')->fetchColumn();
if ($heroCount === 0) {
    exit("No heroes found. Run database/setup.php first.\n");
}

$difficulties = ['easy', 'medium', 'hard'];
$basePoints   = ['easy' => 10, 'medium' => 20, 'hard' => 30];

$insertUser = $pdo->prepare(
    'INSERT OR IGNORE INTO users (username, email, password_hash, role)
     VALUES (:username, :email, :hash, :role)'
);
$insertScore = $pdo->prepare(
    'INSERT INTO scores (user_id, hero_id, difficulty, score, total_questions, correct_count, time_taken, played_at)
     VALUES (:user_id, :hero_id, :difficulty, :score, 5, :correct, :time_taken, :played_at)'
);

$createdUsers = 0;
$createdScores = 0;

$pdo->beginTransaction();

foreach ($demoUsers as $index => $username) {
    $username = trim($username);
    $email = strtolower($username) . '@demo.local';

    $insertUser->execute([
        ':username' => $username,
        ':email'    => $email,
        ':hash'     => $passwordHash,
        ':role'     => 'user',
    ]);

    // Only seed scores for users this run actually created.
    if ($insertUser->rowCount() === 0) {
        continue;
    }
    $createdUsers++;
    $userId = (int) $pdo->lastInsertId();

    // Each demo player gets between 2 and 5 past games with believable results.
    $games = random_int(2, 5);
    for ($g = 0; $g < $games; $g++) {
        $heroId     = random_int(1, $heroCount);
        $difficulty = $difficulties[array_rand($difficulties)];
        $correct    = random_int(1, 5);
        $timeTaken  = random_int(20, 70);

        $score = $correct * $basePoints[$difficulty];
        if ($correct === 5) {
            $score += $basePoints[$difficulty] * 2; // perfect-game bonus
        }
        // A little spread so totals are not all multiples of 10.
        $score += random_int(0, 9);

        // Spread games over the last ~30 days.
        $playedAt = gmdate('Y-m-d H:i:s', time() - random_int(0, 30 * 24 * 3600));

        $insertScore->execute([
            ':user_id'    => $userId,
            ':hero_id'    => $heroId,
            ':difficulty' => $difficulty,
            ':score'      => $score,
            ':correct'    => $correct,
            ':time_taken' => $timeTaken,
            ':played_at'  => $playedAt,
        ]);
        $createdScores++;
    }
}

$pdo->commit();

echo "Seeded {$createdUsers} demo users and {$createdScores} score rows.\n";
echo "All demo accounts use the password: demo1234\n";

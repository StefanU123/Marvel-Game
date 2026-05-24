<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$user = currentUser();
$hero = null;
$errorMessage = '';
$heroId = filter_input(INPUT_GET, 'hero_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($heroId === false || $heroId === null) {
    $errorMessage = 'Please choose a valid hero.';
} else {
    try {
        $pdo = getDatabaseConnection();
        $statement = $pdo->prepare('SELECT id, name, description, image_url FROM heroes WHERE id = :id');
        $statement->execute([':id' => $heroId]);
        $hero = $statement->fetch();

        if (!$hero) {
            $errorMessage = 'Hero not found.';
        }
    } catch (Throwable $error) {
        $errorMessage = 'Could not load the selected hero.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $hero ? htmlspecialchars($hero['name']) . ' — Quiz' : 'Marvel Quiz'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a class="header-left" href="index.php" style="text-decoration:none">
            <h1>Marvel Trivia</h1>
            <span class="header-tagline">Test Your Universe</span>
        </a>
        <nav class="header-right">
            <a class="nav-link" href="leaderboard.php">Leaderboard</a>
            <span class="nav-divider"></span>
            <?php if ($user): ?>
                <span class="nav-user">Hey, <span><?php echo htmlspecialchars($user['username']); ?></span></span>
                <a class="nav-btn nav-btn--outline" href="logout.php">Logout</a>
            <?php else: ?>
                <a class="nav-btn nav-btn--outline" href="login.php">Login</a>
                <a class="nav-btn" href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="game-page">
        <?php if ($errorMessage !== ''): ?>
            <section class="quiz-error-card">
                <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
                <p><a class="button" href="index.php">Choose another hero</a></p>
            </section>
        <?php else: ?>

            <!-- ───────── SETUP STAGE ───────── -->
            <section
                class="quiz-stage quiz-stage--setup"
                id="setup-stage"
                data-hero-id="<?php echo htmlspecialchars($hero['id']); ?>"
                data-hero-name="<?php echo htmlspecialchars($hero['name']); ?>"
            >
                <div class="hero-card hero-card--setup">
                    <img
                        src="<?php echo htmlspecialchars($hero['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($hero['name']); ?>"
                        class="hero-image hero-image--setup"
                    >
                    <div class="hero-content">
                        <span class="hero-eyebrow">Hero Selected</span>
                        <h2 id="hero-title"><?php echo htmlspecialchars($hero['name']); ?></h2>
                        <p><?php echo htmlspecialchars($hero['description']); ?></p>
                    </div>
                </div>

                <div class="difficulty-block">
                    <h3 class="block-title">Choose Difficulty</h3>
                    <div class="difficulty-cards" role="radiogroup" aria-label="Difficulty">
                        <button type="button" class="difficulty-card" data-difficulty="easy" role="radio" aria-checked="false">
                            <span class="diff-label">Easy</span>
                            <span class="diff-points">10 pts / correct</span>
                            <span class="diff-bar diff-bar--easy"><span></span></span>
                        </button>
                        <button type="button" class="difficulty-card" data-difficulty="medium" role="radio" aria-checked="false">
                            <span class="diff-label">Medium</span>
                            <span class="diff-points">20 pts / correct</span>
                            <span class="diff-bar diff-bar--medium"><span></span></span>
                        </button>
                        <button type="button" class="difficulty-card" data-difficulty="hard" role="radio" aria-checked="false">
                            <span class="diff-label">Hard</span>
                            <span class="diff-points">30 pts / correct</span>
                            <span class="diff-bar diff-bar--hard"><span></span></span>
                        </button>
                    </div>

                    <div class="setup-actions">
                        <button class="button button--lg" id="start-quiz" type="button" disabled>Start Quiz</button>
                        <p class="setup-hint" id="setup-hint">Select a difficulty to begin.</p>
                    </div>
                </div>
            </section>

            <!-- ───────── PLAY STAGE ───────── -->
            <section class="quiz-stage quiz-stage--play" id="play-stage" hidden>
                <div class="quiz-hud">
                    <div class="hud-item">
                        <span class="hud-label">Reward</span>
                        <span class="hud-value" id="hud-reward">— pts</span>
                    </div>
                    <div class="hud-item">
                        <span class="hud-label">Lives</span>
                        <span class="hud-value hud-lives" id="hud-lives">
                            <span class="life">♥</span><span class="life">♥</span><span class="life">♥</span>
                        </span>
                    </div>
                    <div class="hud-item">
                        <span class="hud-label">Question</span>
                        <span class="hud-value" id="hud-progress">1 / 5</span>
                    </div>
                    <div class="hud-item hud-item--timer">
                        <span class="hud-label">Time</span>
                        <span class="hud-value" id="hud-timer">15</span>
                        <div class="timer-bar"><div class="timer-bar-fill" id="timer-bar-fill"></div></div>
                    </div>
                </div>

                <article class="question-card" id="question-card" aria-live="polite">
                    <h2 class="question-text" id="question-text">Loading…</h2>
                    <div class="answer-grid" id="answer-grid"></div>
                    <div class="question-feedback" id="question-feedback" hidden></div>
                    <div class="question-actions">
                        <button class="button button--ghost" id="next-question" type="button" hidden>Next →</button>
                    </div>
                </article>
            </section>

            <!-- ───────── RESULTS STAGE ───────── -->
            <section class="quiz-stage quiz-stage--results" id="results-stage" hidden>
                <div class="results-card">
                    <span class="results-eyebrow" id="results-eyebrow">Quiz Complete</span>
                    <h2 class="results-title" id="results-title">Mission Report</h2>

                    <div class="results-score-ring">
                        <svg viewBox="0 0 120 120" class="score-ring-svg">
                            <circle cx="60" cy="60" r="52" class="ring-bg"/>
                            <circle cx="60" cy="60" r="52" class="ring-fg" id="results-ring"/>
                        </svg>
                        <div class="score-ring-inner">
                            <span class="ring-score" id="results-score">0</span>
                            <span class="ring-label">points</span>
                        </div>
                    </div>

                    <div class="results-stats">
                        <div class="results-stat">
                            <span class="stat-label">Correct</span>
                            <span class="stat-value" id="results-correct">0 / 5</span>
                        </div>
                        <div class="results-stat">
                            <span class="stat-label">Accuracy</span>
                            <span class="stat-value" id="results-accuracy">0%</span>
                        </div>
                        <div class="results-stat">
                            <span class="stat-label">Time</span>
                            <span class="stat-value" id="results-time">0s</span>
                        </div>
                    </div>

                    <p class="results-saved" id="results-saved"></p>

                    <div class="results-breakdown" id="results-breakdown"></div>

                    <div class="results-actions">
                        <button class="button" id="play-again" type="button">Play Again</button>
                        <a class="button button--ghost" href="index.php">Pick Another Hero</a>
                        <a class="button button--ghost" href="leaderboard.php">View Leaderboard</a>
                    </div>
                </div>
            </section>

        <?php endif; ?>
    </main>

    <script>
        window.__QUIZ_USER_LOGGED_IN__ = <?php echo $user ? 'true' : 'false'; ?>;
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>

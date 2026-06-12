<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/i18n.php';

$user = currentUser();
$hero = null;
$errorMessage = '';
$heroId = filter_input(INPUT_GET, 'hero_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($heroId === false || $heroId === null) {
    $errorMessage = t('game.invalidHero');
} else {
    try {
        $pdo = getDatabaseConnection();
        $statement = $pdo->prepare('SELECT id, name, description, description_ro, image_url FROM heroes WHERE id = :id');
        $statement->execute([':id' => $heroId]);
        $hero = $statement->fetch();

        if (!$hero) {
            $errorMessage = t('game.heroNotFound');
        }
    } catch (Throwable $error) {
        $errorMessage = t('game.loadHeroError');
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $hero ? htmlspecialchars($hero['name']) . ' — Quiz' : 'Marvel Quiz'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a class="header-left" href="index.php">
            <span class="brand-marvel">Marvel</span>
            <span class="brand-trivia">Trivia</span>
        </a>
        <nav class="header-right">
            <a class="lang-toggle" href="<?php echo htmlspecialchars(langSwitchUrl(otherLang())); ?>"><?php echo t('lang.switch'); ?></a>
            <a class="nav-link" href="leaderboard.php"><?php echo t('nav.leaderboard'); ?></a>
            <span class="nav-divider"></span>
            <?php if ($user): ?>
                <span class="nav-user"><?php echo t('nav.hey'); ?> <span><?php echo htmlspecialchars($user['username']); ?></span></span>
                <a class="nav-btn nav-btn--outline" href="logout.php"><?php echo t('nav.logout'); ?></a>
            <?php else: ?>
                <a class="nav-btn nav-btn--outline" href="login.php"><?php echo t('nav.login'); ?></a>
                <a class="nav-btn" href="register.php"><?php echo t('nav.register'); ?></a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="game-page">
        <?php if ($errorMessage !== ''): ?>
            <section class="quiz-error-card">
                <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
                <p><a class="button" href="index.php"><?php echo t('game.chooseAnother'); ?></a></p>
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
                        <span class="hero-eyebrow"><?php echo t('game.heroSelected'); ?></span>
                        <h2 id="hero-title"><?php echo htmlspecialchars($hero['name']); ?></h2>
                        <p><?php echo htmlspecialchars(heroDescription($hero)); ?></p>
                    </div>
                </div>

                <div class="difficulty-block">
                    <h3 class="block-title"><?php echo t('game.chooseDifficulty'); ?></h3>
                    <div class="difficulty-cards" role="radiogroup" aria-label="<?php echo t('game.chooseDifficulty'); ?>">
                        <button type="button" class="difficulty-card" data-difficulty="easy" role="radio" aria-checked="false">
                            <span class="diff-label"><?php echo t('diff.easy'); ?></span>
                            <span class="diff-points"><?php echo t('game.ptsPerCorrect', 10); ?></span>
                            <span class="diff-bar diff-bar--easy"><span></span></span>
                        </button>
                        <button type="button" class="difficulty-card" data-difficulty="medium" role="radio" aria-checked="false">
                            <span class="diff-label"><?php echo t('diff.medium'); ?></span>
                            <span class="diff-points"><?php echo t('game.ptsPerCorrect', 20); ?></span>
                            <span class="diff-bar diff-bar--medium"><span></span></span>
                        </button>
                        <button type="button" class="difficulty-card" data-difficulty="hard" role="radio" aria-checked="false">
                            <span class="diff-label"><?php echo t('diff.hard'); ?></span>
                            <span class="diff-points"><?php echo t('game.ptsPerCorrect', 30); ?></span>
                            <span class="diff-bar diff-bar--hard"><span></span></span>
                        </button>
                    </div>

                    <div class="setup-actions">
                        <button class="button button--lg" id="start-quiz" type="button" disabled><?php echo t('game.startQuiz'); ?></button>
                        <p class="setup-hint" id="setup-hint"><?php echo t('game.selectToBegin'); ?></p>
                    </div>
                </div>
            </section>

            <!-- ───────── PLAY STAGE ───────── -->
            <section class="quiz-stage quiz-stage--play" id="play-stage" hidden>
                <div class="quiz-hud">
                    <div class="hud-item">
                        <span class="hud-label"><?php echo t('hud.reward'); ?></span>
                        <span class="hud-value" id="hud-reward">— <?php echo t('js.pts'); ?></span>
                    </div>
                    <div class="hud-item">
                        <span class="hud-label"><?php echo t('hud.lives'); ?></span>
                        <span class="hud-value hud-lives" id="hud-lives">
                            <span class="life">♥</span><span class="life">♥</span><span class="life">♥</span>
                        </span>
                    </div>
                    <div class="hud-item">
                        <span class="hud-label"><?php echo t('hud.question'); ?></span>
                        <span class="hud-value" id="hud-progress">1 / 5</span>
                    </div>
                    <div class="hud-item hud-item--timer">
                        <span class="hud-label"><?php echo t('hud.time'); ?></span>
                        <span class="hud-value" id="hud-timer">15</span>
                        <div class="timer-bar"><div class="timer-bar-fill" id="timer-bar-fill"></div></div>
                    </div>
                </div>

                <article class="question-card" id="question-card" aria-live="polite">
                    <h2 class="question-text" id="question-text"><?php echo t('js.loading'); ?></h2>
                    <div class="answer-grid" id="answer-grid"></div>
                    <div class="question-feedback" id="question-feedback" hidden></div>
                    <div class="question-actions">
                        <button class="button button--ghost" id="next-question" type="button" hidden><?php echo t('js.next'); ?></button>
                    </div>
                </article>
            </section>

            <!-- ───────── RESULTS STAGE ───────── -->
            <section class="quiz-stage quiz-stage--results" id="results-stage" hidden>
                <div class="results-card">
                    <span class="results-eyebrow" id="results-eyebrow"><?php echo t('js.quizComplete'); ?></span>
                    <h2 class="results-title" id="results-title"><?php echo t('results.missionReport'); ?></h2>

                    <div class="results-score-ring">
                        <svg viewBox="0 0 120 120" class="score-ring-svg">
                            <circle cx="60" cy="60" r="52" class="ring-bg"/>
                            <circle cx="60" cy="60" r="52" class="ring-fg" id="results-ring"/>
                        </svg>
                        <div class="score-ring-inner">
                            <span class="ring-score" id="results-score">0</span>
                            <span class="ring-label"><?php echo t('js.points'); ?></span>
                        </div>
                    </div>

                    <div class="results-stats">
                        <div class="results-stat">
                            <span class="stat-label"><?php echo t('results.correct'); ?></span>
                            <span class="stat-value" id="results-correct">0 / 5</span>
                        </div>
                        <div class="results-stat">
                            <span class="stat-label"><?php echo t('results.accuracy'); ?></span>
                            <span class="stat-value" id="results-accuracy">0%</span>
                        </div>
                        <div class="results-stat">
                            <span class="stat-label"><?php echo t('results.time'); ?></span>
                            <span class="stat-value" id="results-time">0s</span>
                        </div>
                    </div>

                    <p class="results-saved" id="results-saved"></p>

                    <div class="results-breakdown" id="results-breakdown"></div>

                    <div class="results-actions">
                        <button class="button" id="play-again" type="button"><?php echo t('results.playAgain'); ?></button>
                        <a class="button button--ghost" href="index.php"><?php echo t('results.pickHero'); ?></a>
                        <a class="button button--ghost" href="leaderboard.php"><?php echo t('results.viewLeaderboard'); ?></a>
                    </div>
                </div>
            </section>

        <?php endif; ?>
    </main>

    <script>
        window.__QUIZ_USER_LOGGED_IN__ = <?php echo $user ? 'true' : 'false'; ?>;
        window.__LANG__ = <?php echo json_encode(lang()); ?>;
        window.__I18N__ = <?php echo json_encode(jsTranslations(), JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>

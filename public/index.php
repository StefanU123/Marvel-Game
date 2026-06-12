<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/i18n.php';

$heroes = [];
$errorMessage = '';
$user = currentUser();

try {
    $pdo = getDatabaseConnection();
    $statement = $pdo->query('SELECT id, name, description, description_ro, image_url FROM heroes ORDER BY name');
    $heroes = $statement->fetchAll();
} catch (Throwable $error) {
    $errorMessage = t('index.loadError');
}
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Trivia Game</title>
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
            <button class="nav-btn nav-btn--outline" id="enable-notifications" type="button">Enable notifications</button>
            <small id="notification-status" aria-live="polite"></small>
            <span class="nav-divider"></span>

            <?php if ($user): ?>
                <span class="nav-user"><?php echo t('nav.hey'); ?> <span><?php echo htmlspecialchars($user['username']); ?></span></span>
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a class="nav-btn" href="admin/index.php"><?php echo t('nav.admin'); ?></a>
                <?php endif; ?>
                <a class="nav-btn nav-btn--outline" href="logout.php"><?php echo t('nav.logout'); ?></a>
            <?php else: ?>
                <a class="nav-btn nav-btn--outline" href="login.php"><?php echo t('nav.login'); ?></a>
                <a class="nav-btn" href="register.php"><?php echo t('nav.register'); ?></a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="page-content">
        <div class="home-layout">
            <section class="home-main" aria-labelledby="heroes-title">
                <h1 id="heroes-title"><?php echo t('index.chooseHero'); ?></h1>

                <?php if ($errorMessage !== ''): ?>
                    <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
                <?php elseif (count($heroes) === 0): ?>
                    <p class="message"><?php echo t('index.noHeroes'); ?></p>
                <?php else: ?>
                    <div class="hero-grid">
                        <?php foreach ($heroes as $i => $hero): ?>
                            <article class="hero-card" style="animation-delay: <?php echo $i * 0.06; ?>s">
                                <img
                                    src="<?php echo htmlspecialchars($hero['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($hero['name']); ?>"
                                    class="hero-image"
                                >
                                <div class="hero-content">
                                    <h2><?php echo htmlspecialchars($hero['name']); ?></h2>
                                    <p><?php echo htmlspecialchars(heroDescription($hero)); ?></p>
                                    <a class="button" href="game.php?hero_id=<?php echo htmlspecialchars($hero['id']); ?>"><?php echo t('index.startGame'); ?></a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="home-sidebar">
                <section aria-labelledby="leaderboard-title" class="leaderboard-section">
                    <div class="leaderboard-header">
                        <h2 id="leaderboard-title"><?php echo t('index.topPlayers'); ?></h2>
                        <span class="leaderboard-live-badge">
                            <span class="leaderboard-live-dot"></span>
                            <?php echo t('index.live'); ?>
                        </span>
                    </div>

                    <?php
                    $leaderboard = [];
                    $lbError = false;
                    try {
                        $pdo = getDatabaseConnection();
                        $lbStmt = $pdo->query(
                            'SELECT u.username, SUM(s.score) AS total_score
                             FROM scores s
                             JOIN users u ON u.id = s.user_id
                             GROUP BY s.user_id, u.username
                             ORDER BY total_score DESC
                             LIMIT 15'
                        );
                        $leaderboard = $lbStmt->fetchAll();
                    } catch (Throwable $e) {
                        $lbError = true;
                    }
                    ?>

                    <?php if ($lbError || count($leaderboard) === 0): ?>
                        <p class="leaderboard-empty">
                            <?php echo $lbError ? t('index.couldNotLoadScores') : t('index.noScores'); ?>
                        </p>
                    <?php else: ?>
                        <table class="leaderboard-table">
                            <thead>
                                <tr>
                                    <th><?php echo t('lb.rank'); ?></th>
                                    <th><?php echo t('lb.player'); ?></th>
                                    <th><?php echo t('lb.score'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leaderboard as $rank => $row): ?>
                                    <tr>
                                        <td class="lb-rank lb-rank-<?php echo $rank + 1; ?>"><?php echo $rank + 1; ?></td>
                                        <td class="lb-username"><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td class="lb-score"><?php echo number_format($row['total_score']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <a class="leaderboard-viewall" href="leaderboard.php"><?php echo t('index.viewFull'); ?></a>
                </section>
            </aside>
        </div>

        <div class="home-report-link">
            <a class="button button--ghost" href="report.html">Project report</a>
        </div>
    </main>

    <script>
        // Auto-refresh leaderboard every 30s
        setTimeout(function refreshLb() {
            fetch(window.location.href)
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newLb = doc.querySelector('.leaderboard-section');
                    const curLb = document.querySelector('.leaderboard-section');
                    if (newLb && curLb) curLb.innerHTML = newLb.innerHTML;
                })
                .catch(() => {})
                .finally(() => setTimeout(refreshLb, 30000));
        }, 30000);
    </script>
    <script src="assets/js/notifications.js"></script>
</body>
</html>

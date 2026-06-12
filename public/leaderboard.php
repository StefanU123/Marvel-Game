<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/i18n.php';

$user = currentUser();

$heroFilter = filter_input(INPUT_GET, 'hero_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$diffFilter = $_GET['difficulty'] ?? '';
$allowedDiffs = ['easy', 'medium', 'hard'];
if (!in_array($diffFilter, $allowedDiffs, true)) {
    $diffFilter = '';
}

$leaderboard = [];
$recent = [];
$heroes = [];
$loadError = '';

try {
    $pdo = getDatabaseConnection();

    $heroes = $pdo->query('SELECT id, name FROM heroes ORDER BY name')->fetchAll();

    // ── Aggregate leaderboard (top players by total score)
    $lbWhere = [];
    $lbParams = [];
    if ($heroFilter) {
        $lbWhere[] = 's.hero_id = :hero_id';
        $lbParams[':hero_id'] = $heroFilter;
    }
    if ($diffFilter) {
        $lbWhere[] = 's.difficulty = :difficulty';
        $lbParams[':difficulty'] = $diffFilter;
    }
    $whereSql = $lbWhere ? 'WHERE ' . implode(' AND ', $lbWhere) : '';

    $lbStmt = $pdo->prepare(
        "SELECT u.username,
                SUM(s.score)            AS total_score,
                COUNT(s.id)             AS games_played,
                MAX(s.score)            AS best_score,
                SUM(s.correct_count)    AS total_correct,
                SUM(s.total_questions)  AS total_answered
         FROM scores s
         JOIN users u ON u.id = s.user_id
         $whereSql
         GROUP BY s.user_id, u.username
         ORDER BY total_score DESC, games_played ASC
         LIMIT 25"
    );
    foreach ($lbParams as $k => $v) {
        $lbStmt->bindValue($k, $v);
    }
    $lbStmt->execute();
    $leaderboard = $lbStmt->fetchAll();

    // ── Recent matches
    $recentStmt = $pdo->prepare(
        "SELECT u.username, h.name AS hero_name, s.difficulty, s.score,
                s.correct_count, s.total_questions, s.played_at
         FROM scores s
         JOIN users u   ON u.id = s.user_id
         JOIN heroes h  ON h.id = s.hero_id
         $whereSql
         ORDER BY s.played_at DESC
         LIMIT 10"
    );
    foreach ($lbParams as $k => $v) {
        $recentStmt->bindValue($k, $v);
    }
    $recentStmt->execute();
    $recent = $recentStmt->fetchAll();
} catch (Throwable $e) {
    $loadError = t('lbp.loadError');
}

function buildFilterUrl(?int $hero, string $diff): string
{
    $params = [];
    if ($hero) $params['hero_id']    = $hero;
    if ($diff) $params['difficulty'] = $diff;
    return 'leaderboard.php' . ($params ? '?' . http_build_query($params) : '');
}
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard — Marvel Trivia</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php
    // Build an RSS auto-discovery link that carries the active filters, so feed
    // readers subscribe to exactly the ranking the user is viewing.
    $rssQuery = [];
    if ($heroFilter) $rssQuery['hero_id'] = $heroFilter;
    if ($diffFilter) $rssQuery['difficulty'] = $diffFilter;
    $rssHref = 'api/rss.php' . ($rssQuery ? '?' . http_build_query($rssQuery) : '');
    ?>
    <link rel="alternate" type="application/rss+xml" title="Marvel Trivia — Top Players" href="<?php echo htmlspecialchars($rssHref); ?>">
</head>
<body>
    <header class="site-header">
        <a class="header-left" href="index.php">
            <span class="brand-marvel">Marvel</span>
            <span class="brand-trivia">Trivia</span>
        </a>
        <nav class="header-right">
            <a class="lang-toggle" href="<?php echo htmlspecialchars(langSwitchUrl(otherLang())); ?>"><?php echo t('lang.switch'); ?></a>
            <a class="nav-link" href="index.php"><?php echo t('nav.home'); ?></a>
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

    <main class="page-content leaderboard-page">
        <section class="leaderboard-hero">
            <span class="leaderboard-hero-eyebrow"><?php echo t('lbp.hallOfHeroes'); ?></span>
            <h1 class="leaderboard-hero-title"><?php echo t('lbp.title'); ?></h1>
            <p class="leaderboard-hero-sub"><?php echo t('lbp.subtitle'); ?></p>
        </section>

        <div class="leaderboard-filters" role="group" aria-label="<?php echo t('lbp.difficulty'); ?>">
            <div class="filter-group">
                <span class="filter-label"><?php echo t('lbp.hero'); ?></span>
                <div class="filter-chips">
                    <a class="filter-chip <?php echo !$heroFilter ? 'is-active' : ''; ?>"
                       href="<?php echo htmlspecialchars(buildFilterUrl(null, $diffFilter)); ?>"><?php echo t('lbp.all'); ?></a>
                    <?php foreach ($heroes as $h): ?>
                        <a class="filter-chip <?php echo (int)$h['id'] === (int)$heroFilter ? 'is-active' : ''; ?>"
                           href="<?php echo htmlspecialchars(buildFilterUrl((int)$h['id'], $diffFilter)); ?>">
                            <?php echo htmlspecialchars($h['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-group">
                <span class="filter-label"><?php echo t('lbp.difficulty'); ?></span>
                <div class="filter-chips">
                    <a class="filter-chip <?php echo $diffFilter === '' ? 'is-active' : ''; ?>"
                       href="<?php echo htmlspecialchars(buildFilterUrl($heroFilter ?: null, '')); ?>"><?php echo t('lbp.all'); ?></a>
                    <?php foreach ($allowedDiffs as $d): ?>
                        <a class="filter-chip filter-chip--<?php echo $d; ?> <?php echo $diffFilter === $d ? 'is-active' : ''; ?>"
                           href="<?php echo htmlspecialchars(buildFilterUrl($heroFilter ?: null, $d)); ?>">
                            <?php echo t('diff.' . $d); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($loadError): ?>
            <p class="message"><?php echo htmlspecialchars($loadError); ?></p>
        <?php else: ?>

            <?php if (count($leaderboard) >= 3): ?>
                <div class="podium" role="group" aria-label="Top 3">
                    <?php
                    // Order: 2nd, 1st, 3rd for visual podium
                    $top = array_slice($leaderboard, 0, 3);
                    $podiumOrder = [1, 0, 2];
                    ?>
                    <?php foreach ($podiumOrder as $idx): if (!isset($top[$idx])) continue; $p = $top[$idx]; $rank = $idx + 1; ?>
                        <div class="podium-spot podium-spot--<?php echo $rank; ?>">
                            <div class="podium-medal">
                                <?php echo $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : '🥉'); ?>
                            </div>
                            <div class="podium-name"><?php echo htmlspecialchars($p['username']); ?></div>
                            <div class="podium-score"><?php echo number_format((int)$p['total_score']); ?></div>
                            <div class="podium-meta"><?php echo (int)$p['games_played']; ?> <?php echo t('lbp.games'); ?></div>
                            <div class="podium-bar"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section class="leaderboard-section">
                <div class="leaderboard-header">
                    <h2><?php echo t('lbp.fullRankings'); ?></h2>
                    <button class="button button--ghost" id="enable-notifications" type="button">Enable leaderboard notifications</button>
                    <small id="notification-status" aria-live="polite"></small>
                    <span class="leaderboard-live-badge">
                        <span class="leaderboard-live-dot"></span>
                        <?php echo count($leaderboard); ?> <?php echo t('lbp.players'); ?>
                    </span>
                </div>

                <?php if (count($leaderboard) === 0): ?>
                    <p class="leaderboard-empty"><?php echo t('lbp.noScoresFilter'); ?></p>
                <?php else: ?>
                    <table class="leaderboard-table leaderboard-table--full">
                        <thead>
                            <tr>
                                <th><?php echo t('lb.rank'); ?></th>
                                <th><?php echo t('lb.player'); ?></th>
                                <th><?php echo t('lbp.games'); ?></th>
                                <th><?php echo t('lbp.accuracy'); ?></th>
                                <th><?php echo t('lbp.best'); ?></th>
                                <th><?php echo t('lbp.total'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $rank => $row):
                                $acc = ((int)$row['total_answered']) > 0
                                    ? round(((int)$row['total_correct']) / ((int)$row['total_answered']) * 100)
                                    : 0;
                                $isMe = $user && strcasecmp($user['username'], $row['username']) === 0;
                            ?>
                                <tr class="<?php echo $isMe ? 'lb-row--me' : ''; ?>">
                                    <td class="lb-rank lb-rank-<?php echo $rank + 1; ?>"><?php echo $rank + 1; ?></td>
                                    <td class="lb-username">
                                        <?php echo htmlspecialchars($row['username']); ?>
                                        <?php if ($isMe): ?><span class="lb-you-tag"><?php echo t('lbp.you'); ?></span><?php endif; ?>
                                    </td>
                                    <td><?php echo (int)$row['games_played']; ?></td>
                                    <td><?php echo $acc; ?>%</td>
                                    <td><?php echo number_format((int)$row['best_score']); ?></td>
                                    <td class="lb-score"><?php echo number_format((int)$row['total_score']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section class="leaderboard-section">
                <div class="leaderboard-header">
                    <h2><?php echo t('lbp.recentMatches'); ?></h2>
                    <span class="leaderboard-live-badge">
                        <span class="leaderboard-live-dot"></span>
                        <?php echo t('index.live'); ?>
                    </span>
                </div>

                <?php if (count($recent) === 0): ?>
                    <p class="leaderboard-empty"><?php echo t('lbp.noRecent'); ?></p>
                <?php else: ?>
                    <ul class="recent-list">
                        <?php foreach ($recent as $r): ?>
                            <li class="recent-item">
                                <div class="recent-main">
                                    <span class="recent-user"><?php echo htmlspecialchars($r['username']); ?></span>
                                    <span class="recent-vs"><?php echo t('lbp.vs'); ?></span>
                                    <span class="recent-hero"><?php echo htmlspecialchars($r['hero_name']); ?></span>
                                    <span class="recent-diff recent-diff--<?php echo htmlspecialchars($r['difficulty']); ?>">
                                        <?php echo t('diff.' . $r['difficulty']); ?>
                                    </span>
                                </div>
                                <div class="recent-side">
                                    <span class="recent-correct"><?php echo (int)$r['correct_count']; ?>/<?php echo (int)$r['total_questions']; ?></span>
                                    <span class="recent-score"><?php echo number_format((int)$r['score']); ?> <?php echo t('js.pts'); ?></span>
                                    <time class="recent-time" datetime="<?php echo htmlspecialchars($r['played_at']); ?>">
                                        <?php echo htmlspecialchars($r['played_at']); ?>
                                    </time>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

        <?php endif; ?>

        <div class="leaderboard-cta">
            <a class="button button--lg" href="index.php"><?php echo t('lbp.pickPlay'); ?></a>
            <a class="button button--ghost rss-link" href="<?php echo htmlspecialchars($rssHref); ?>" target="_blank" rel="noopener">
                <span class="rss-dot" aria-hidden="true"></span> <?php echo t('lbp.rss'); ?>
            </a>
        </div>
    </main>
    <script src="assets/js/notifications.js"></script>
</body>
</html>

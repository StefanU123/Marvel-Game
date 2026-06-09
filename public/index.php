<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$heroes = [];
$errorMessage = '';
$user = currentUser();

try {
    $pdo = getDatabaseConnection();
    $statement = $pdo->query('SELECT id, name, description, image_url FROM heroes ORDER BY name');
    $heroes = $statement->fetchAll();
} catch (Throwable $error) {
    $errorMessage = 'Could not load heroes from the database.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Trivia Game</title>
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
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a class="nav-btn" href="admin/index.php">Admin Panel</a>
                <?php endif; ?>
                <a class="nav-btn nav-btn--outline" href="logout.php">Logout</a>
            <?php else: ?>
                <a class="nav-btn nav-btn--outline" href="login.php">Login</a>
                <a class="nav-btn" href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="page-content">
        <section aria-labelledby="heroes-title">
            <h2 id="heroes-title">Choose Your Hero</h2>

            <?php if ($errorMessage !== ''): ?>
                <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php elseif (count($heroes) === 0): ?>
                <p class="message">No heroes are available yet. Run the database setup script first.</p>
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
                                <h3><?php echo htmlspecialchars($hero['name']); ?></h3>
                                <p><?php echo htmlspecialchars($hero['description']); ?></p>
                                <a class="button" href="game.php?hero_id=<?php echo htmlspecialchars($hero['id']); ?>">Start Game</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section aria-labelledby="leaderboard-title" class="leaderboard-section">
            <div class="leaderboard-header">
                <h2 id="leaderboard-title">Top Players</h2>
                <span class="leaderboard-live-badge">
                    <span class="leaderboard-live-dot"></span>
                    Live
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
                     LIMIT 10'
                );
                $leaderboard = $lbStmt->fetchAll();
            } catch (Throwable $e) {
                $lbError = true;
            }
            ?>

            <?php if ($lbError || count($leaderboard) === 0): ?>
                <p class="leaderboard-empty">
                    <?php echo $lbError ? 'Could not load scores.' : 'No scores yet — be the first to play!'; ?>
                </p>
            <?php else: ?>
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Player</th>
                            <th>Score</th>
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
        </section>
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
</body>
</html>

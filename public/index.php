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
    <title>Marvel Game</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <h1>Marvel Game</h1>
        <p>Choose a Marvel hero and test your trivia knowledge across easy, medium and hard questions.</p>
    </header>

    <main class="page-content">
        <section aria-label="Account">
            <?php if ($user): ?>
                <p>
                    Logged in as <?php echo htmlspecialchars($user['username']); ?>
                    <a href="logout.php">Logout</a>
                </p>
            <?php else: ?>
                <p>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                </p>
            <?php endif; ?>
        </section>

        <section aria-labelledby="heroes-title">
            <h2 id="heroes-title">Available Heroes</h2>

            <?php if ($errorMessage !== ''): ?>
                <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php elseif (count($heroes) === 0): ?>
                <p class="message">No heroes are available yet. Run the database setup script first.</p>
            <?php else: ?>
                <div class="hero-grid">
                    <?php foreach ($heroes as $hero): ?>
                        <article class="hero-card">
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
    </main>
</body>
</html>

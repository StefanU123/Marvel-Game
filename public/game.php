<?php

require_once __DIR__ . '/../includes/db.php';

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
    <title>Marvel Game</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <h1>Marvel Game</h1>
        <p>Start a quiz and test what you know about your selected hero.</p>
    </header>

    <main class="page-content">
        <?php if ($errorMessage !== ''): ?>
            <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
            <p><a class="button" href="index.php">Choose another hero</a></p>
        <?php else: ?>
            <section
                class="hero-card"
                id="game"
                data-hero-id="<?php echo htmlspecialchars($hero['id']); ?>"
                aria-labelledby="hero-title"
            >
                <img
                    src="<?php echo htmlspecialchars($hero['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($hero['name']); ?>"
                    class="hero-image"
                >
                <div class="hero-content">
                    <h2 id="hero-title"><?php echo htmlspecialchars($hero['name']); ?></h2>
                    <p><?php echo htmlspecialchars($hero['description']); ?></p>

                    <label for="difficulty">Difficulty</label>
                    <select id="difficulty" name="difficulty">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>

                    <button class="button" id="start-quiz" type="button">Start Quiz</button>
                </div>
            </section>

            <section aria-label="Quiz status">
                <p id="game-status">Score: 0 | Lives: 3 | Timer: 0</p>
            </section>

            <section id="question-area" aria-live="polite">
                <p class="message">Press Start Quiz to load a question.</p>
            </section>
        <?php endif; ?>
    </main>

    <script src="assets/js/app.js"></script>
</body>
</html>

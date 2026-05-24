<?php

require_once __DIR__ . '/../includes/db.php';

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $errorMessage = 'Please fill in all fields.';
    } else {
        try {
            $pdo = getDatabaseConnection();

            $checkStatement = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
            $checkStatement->execute([
                ':username' => $username,
                ':email' => $email,
            ]);

            if ($checkStatement->fetch()) {
                $errorMessage = 'Username or email already exists.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $insertStatement = $pdo->prepare(
                    'INSERT INTO users (username, email, password_hash, role)
                     VALUES (:username, :email, :password_hash, :role)'
                );
                $insertStatement->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':password_hash' => $passwordHash,
                    ':role' => 'user',
                ]);

                header('Location: login.php');
                exit;
            }
        } catch (Throwable $error) {
            $errorMessage = 'Could not create account. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Marvel Trivia</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a class="header-left" href="index.php" style="text-decoration:none">
            <h1>Marvel Trivia</h1>
            <span class="header-tagline">Test Your Universe</span>
        </a>
        <nav class="header-right">
            <a class="nav-link back-link" href="index.php">← Back</a>
        </nav>
    </header>

    <main class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <h2>Marvel Trivia</h2>
                <p>Join and compete on the leaderboard</p>
            </div>

            <h3 class="auth-title">Create Account</h3>

            <?php if ($errorMessage !== ''): ?>
                <div class="auth-error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form method="post" action="register.php">
                <div class="auth-field">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Choose a username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        autocomplete="username"
                    >
                </div>

                <div class="auth-field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="your@email.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        autocomplete="email"
                    >
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        autocomplete="new-password"
                    >
                </div>

                <button class="button auth-submit" type="submit">Create Account</button>
            </form>

            <div class="auth-divider">or</div>

            <p class="auth-footer">
                Already have an account? <a href="login.php">Sign in</a>
            </p>
        </div>
    </main>
</body>
</html>

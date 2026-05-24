<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

startSessionIfNeeded();

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $errorMessage = 'Please fill in all fields.';
    } else {
        try {
            $pdo = getDatabaseConnection();

            $statement = $pdo->prepare(
                'SELECT id, username, role, password_hash
                 FROM users
                 WHERE username = :username OR email = :email
                 LIMIT 1'
            );
            $statement->execute([
                ':username' => $usernameOrEmail,
                ':email' => $usernameOrEmail,
            ]);
            $user = $statement->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                ];

                header('Location: index.php');
                exit;
            }

            $errorMessage = 'Invalid username, email or password.';
        } catch (Throwable $error) {
            $errorMessage = 'Could not log in. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Marvel Trivia</title>
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
                <p>Sign in to track your scores</p>
            </div>

            <h3 class="auth-title">Welcome Back</h3>

            <?php if ($errorMessage !== ''): ?>
                <div class="auth-error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="auth-field">
                    <label for="username_or_email">Username or Email</label>
                    <input
                        type="text"
                        id="username_or_email"
                        name="username_or_email"
                        placeholder="Enter your username or email"
                        value="<?php echo htmlspecialchars($_POST['username_or_email'] ?? ''); ?>"
                        autocomplete="username"
                    >
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                </div>

                <button class="button auth-submit" type="submit">Sign In</button>
            </form>

            <div class="auth-divider">or</div>

            <p class="auth-footer">
                Don't have an account? <a href="register.php">Create one</a>
            </p>
        </div>
    </main>
</body>
</html>

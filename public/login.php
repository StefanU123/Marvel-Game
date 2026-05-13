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
            $errorMessage = 'Could not log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Marvel Game</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <h1>Login</h1>
        <p>Sign in to continue playing the Marvel Game.</p>
    </header>

    <main class="page-content">
        <?php if ($errorMessage !== ''): ?>
            <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <p>
                <label for="username_or_email">Username or Email</label><br>
                <input
                    type="text"
                    id="username_or_email"
                    name="username_or_email"
                    value="<?php echo htmlspecialchars($_POST['username_or_email'] ?? ''); ?>"
                >
            </p>

            <p>
                <label for="password">Password</label><br>
                <input type="password" id="password" name="password">
            </p>

            <button class="button" type="submit">Login</button>
            <a class="button" href="register.php">Register</a>
        </form>
    </main>
</body>
</html>

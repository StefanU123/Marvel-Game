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
            $errorMessage = 'Could not create account.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Marvel Game</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <h1>Create Account</h1>
        <p>Register to play the Marvel Game.</p>
    </header>

    <main class="page-content">
        <?php if ($errorMessage !== ''): ?>
            <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <form method="post" action="register.php">
            <p>
                <label for="username">Username</label><br>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </p>

            <p>
                <label for="email">Email</label><br>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </p>

            <p>
                <label for="password">Password</label><br>
                <input type="password" id="password" name="password">
            </p>

            <button class="button" type="submit">Register</button>
            <a class="button" href="login.php">Login</a>
        </form>
    </main>
</body>
</html>

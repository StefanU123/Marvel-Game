<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/i18n.php';

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $errorMessage = t('auth.fillFields');
    } else {
        try {
            $pdo = getDatabaseConnection();

            $checkStatement = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
            $checkStatement->execute([
                ':username' => $username,
                ':email' => $email,
            ]);

            if ($checkStatement->fetch()) {
                $errorMessage = t('reg.exists');
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
            $errorMessage = t('reg.createError');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Marvel Trivia</title>
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
            <a class="nav-link back-link" href="index.php"><?php echo t('nav.back'); ?></a>
        </nav>
    </header>

    <main class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <h2>Marvel Trivia</h2>
                <p><?php echo t('reg.joinCompete'); ?></p>
            </div>

            <h3 class="auth-title"><?php echo t('reg.createAccount'); ?></h3>

            <?php if ($errorMessage !== ''): ?>
                <div class="auth-error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form method="post" action="register.php">
                <div class="auth-field">
                    <label for="username"><?php echo t('reg.username'); ?></label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="<?php echo t('reg.phUsername'); ?>"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        autocomplete="username"
                    >
                </div>

                <div class="auth-field">
                    <label for="email"><?php echo t('reg.email'); ?></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="<?php echo t('reg.phEmail'); ?>"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        autocomplete="email"
                    >
                </div>

                <div class="auth-field">
                    <label for="password"><?php echo t('reg.password'); ?></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="<?php echo t('reg.phPassword'); ?>"
                        autocomplete="new-password"
                    >
                </div>

                <button class="button auth-submit" type="submit"><?php echo t('reg.createAccount'); ?></button>
            </form>

            <div class="auth-divider"><?php echo t('auth.or'); ?></div>

            <p class="auth-footer">
                <?php echo t('reg.haveAccount'); ?> <a href="login.php"><?php echo t('reg.signIn'); ?></a>
            </p>
        </div>
    </main>
</body>
</html>

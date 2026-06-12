<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/i18n.php';

startSessionIfNeeded();

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $errorMessage = t('auth.fillFields');
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

            $errorMessage = t('auth.invalidLogin');
        } catch (Throwable $error) {
            $errorMessage = t('auth.loginError');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Marvel Trivia</title>
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
                <p><?php echo t('auth.signInToTrack'); ?></p>
            </div>

            <h3 class="auth-title"><?php echo t('auth.welcomeBack'); ?></h3>

            <?php if ($errorMessage !== ''): ?>
                <div class="auth-error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="auth-field">
                    <label for="username_or_email"><?php echo t('auth.usernameOrEmail'); ?></label>
                    <input
                        type="text"
                        id="username_or_email"
                        name="username_or_email"
                        placeholder="<?php echo t('auth.phUsernameEmail'); ?>"
                        value="<?php echo htmlspecialchars($_POST['username_or_email'] ?? ''); ?>"
                        autocomplete="username"
                    >
                </div>

                <div class="auth-field">
                    <label for="password"><?php echo t('auth.password'); ?></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="<?php echo t('auth.phPassword'); ?>"
                        autocomplete="current-password"
                    >
                </div>

                <button class="button auth-submit" type="submit"><?php echo t('auth.signIn'); ?></button>
            </form>

            <div class="auth-divider"><?php echo t('auth.or'); ?></div>

            <p class="auth-footer">
                <?php echo t('auth.noAccount'); ?> <a href="register.php"><?php echo t('auth.createOne'); ?></a>
            </p>
        </div>
    </main>
</body>
</html>

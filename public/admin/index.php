<?php
require_once '../../includes/auth.php';
require_once '../../includes/i18n.php';

requireAdmin();

$user = currentUser();
$username = $user['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a class="header-left" href="index.php">
            <span class="brand-marvel">Marvel</span>
            <span class="brand-trivia">Trivia</span>
        </a>
        <nav class="header-right">
            <a class="lang-toggle" href="<?php echo htmlspecialchars(langSwitchUrl(otherLang())); ?>"><?php echo t('lang.switch'); ?></a>
            <span class="admin-tag"><?php echo t('admin.tag'); ?></span>
            <span class="nav-divider"></span>
            <a class="nav-link" href="../index.php"><?php echo t('nav.viewSite'); ?></a>
            <a class="nav-btn nav-btn--outline" href="../logout.php"><?php echo t('nav.logout'); ?></a>
        </nav>
    </header>

    <main class="admin-page">
        <div class="admin-head">
            <div>
                <h1 class="admin-title"><?php echo t('admin.panel'); ?></h1>
                <p class="admin-subtitle"><?php echo t('admin.welcome', htmlspecialchars($username)); ?></p>
            </div>
        </div>

        <div class="admin-cards">
            <a class="admin-card" href="questions.php">
                <span class="admin-card-icon">❓</span>
                <span class="admin-card-title"><?php echo t('admin.manageQuestions'); ?></span>
                <span class="admin-card-desc"><?php echo t('admin.descQuestions'); ?></span>
            </a>
            <a class="admin-card" href="heroes.php">
                <span class="admin-card-icon">🦸</span>
                <span class="admin-card-title"><?php echo t('admin.manageHeroes'); ?></span>
                <span class="admin-card-desc"><?php echo t('admin.descHeroes'); ?></span>
            </a>
            <a class="admin-card" href="import.php">
                <span class="admin-card-icon">📥</span>
                <span class="admin-card-title"><?php echo t('admin.importData'); ?></span>
                <span class="admin-card-desc"><?php echo t('admin.descImport'); ?></span>
            </a>
            <a class="admin-card" href="export.php">
                <span class="admin-card-icon">📤</span>
                <span class="admin-card-title"><?php echo t('admin.exportData'); ?></span>
                <span class="admin-card-desc"><?php echo t('admin.descExport'); ?></span>
            </a>
        </div>
    </main>
</body>
</html>

<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/i18n.php';

requireAdmin();

$pdo = getDatabaseConnection();

$heroStatement = $pdo->prepare('SELECT id, name, description, image_url FROM heroes ORDER BY id');
$heroStatement->execute();
$heroes = $heroStatement->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Heroes</title>
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
                <a class="admin-back" href="index.php"><?php echo t('admin.backToAdmin'); ?></a>
                <h1 class="admin-title"><?php echo t('admin.manageHeroes'); ?></h1>
                <p class="admin-subtitle"><?php echo t('admin.heroesCount', count($heroes)); ?></p>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?php echo t('admin.colId'); ?></th>
                        <th><?php echo t('admin.colImage'); ?></th>
                        <th><?php echo t('admin.colName'); ?></th>
                        <th><?php echo t('admin.colDescription'); ?></th>
                        <th><?php echo t('admin.colImageUrl'); ?></th>
                        <th><?php echo t('admin.colActions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($heroes as $hero): ?>
                        <tr>
                            <td class="admin-id"><?php echo htmlspecialchars($hero['id']); ?></td>
                            <td>
                                <img
                                    class="admin-hero-thumb"
                                    src="<?php echo htmlspecialchars('../' . $hero['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($hero['name']); ?>"
                                >
                            </td>
                            <td class="admin-hero-name"><?php echo htmlspecialchars($hero['name']); ?></td>
                            <td class="admin-q-text"><?php echo htmlspecialchars($hero['description']); ?></td>
                            <td><?php echo htmlspecialchars($hero['image_url']); ?></td>
                            <td>
                                <div class="admin-actions">
                                    <a class="admin-action" href="edit_hero.php?id=<?php echo htmlspecialchars($hero['id']); ?>"><?php echo t('admin.edit'); ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

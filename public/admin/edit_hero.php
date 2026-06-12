<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/i18n.php';

requireAdmin();

$id = $_GET['id'] ?? '';

if (!is_string($id) || !ctype_digit($id) || (int) $id <= 0) {
    echo 'Invalid hero id.';
    exit;
}

$pdo = getDatabaseConnection();

$heroStatement = $pdo->prepare('SELECT id, name, description, image_url FROM heroes WHERE id = :id');
$heroStatement->execute([
    'id' => (int) $id,
]);
$hero = $heroStatement->fetch();

if (!$hero) {
    echo 'Hero not found.';
    exit;
}

$error = '';
$name = $hero['name'];
$description = $hero['description'];
$imageUrl = $hero['image_url'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');

    if ($name === '' || $description === '' || $imageUrl === '') {
        $error = 'All fields are required.';
    } else {
        $updateStatement = $pdo->prepare("
            UPDATE heroes
            SET
                name = :name,
                description = :description,
                image_url = :image_url
            WHERE id = :id
        ");

        $updateStatement->execute([
            'name' => $name,
            'description' => $description,
            'image_url' => $imageUrl,
            'id' => (int) $id,
        ]);

        header('Location: heroes.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hero</title>
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
                <a class="admin-back" href="heroes.php"><?php echo t('admin.backToHeroes'); ?></a>
                <h1 class="admin-title"><?php echo t('admin.editHeroTitle'); ?></h1>
                <p class="admin-subtitle"><?php echo htmlspecialchars($name); ?></p>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="admin-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="admin-form" method="post" action="edit_hero.php?id=<?php echo htmlspecialchars($id); ?>">
            <div class="admin-field">
                <label for="name"><?php echo t('admin.fieldName'); ?></label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>">
            </div>

            <div class="admin-field">
                <label for="description"><?php echo t('admin.fieldDescription'); ?></label>
                <textarea name="description" id="description"><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div class="admin-field">
                <label for="image_url"><?php echo t('admin.fieldImageUrl'); ?></label>
                <input type="text" name="image_url" id="image_url" value="<?php echo htmlspecialchars($imageUrl); ?>">
                <span class="admin-note"><?php echo t('admin.imageUrlNote'); ?> <code>assets/images/thor.svg</code></span>
            </div>

            <div class="admin-form-actions">
                <button class="button" type="submit"><?php echo t('admin.saveHero'); ?></button>
                <a class="button button--ghost" href="heroes.php"><?php echo t('admin.cancel'); ?></a>
            </div>
        </form>
    </main>
</body>
</html>

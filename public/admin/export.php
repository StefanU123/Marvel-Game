<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/i18n.php';

requireAdmin();

$validTypes = ['questions', 'heroes'];
$validFormats = ['csv', 'json'];

if (count($_GET) === 0) {
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo lang(); ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Export Data</title>
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
                    <h1 class="admin-title"><?php echo t('admin.exportData'); ?></h1>
                    <p class="admin-subtitle"><?php echo t('admin.exportSubtitle'); ?></p>
                </div>
            </div>

            <ul class="admin-export-list">
                <li><a href="export.php?type=questions&amp;format=csv"><span class="admin-export-icon">📄</span> <?php echo t('admin.manageQuestions'); ?> &middot; CSV</a></li>
                <li><a href="export.php?type=questions&amp;format=json"><span class="admin-export-icon">🗂️</span> <?php echo t('admin.manageQuestions'); ?> &middot; JSON</a></li>
                <li><a href="export.php?type=heroes&amp;format=csv"><span class="admin-export-icon">📄</span> <?php echo t('admin.manageHeroes'); ?> &middot; CSV</a></li>
                <li><a href="export.php?type=heroes&amp;format=json"><span class="admin-export-icon">🗂️</span> <?php echo t('admin.manageHeroes'); ?> &middot; JSON</a></li>
            </ul>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? '';

if (!in_array($type, $validTypes, true)) {
    echo 'Invalid export type.';
    exit;
}

if (!in_array($format, $validFormats, true)) {
    echo 'Invalid export format.';
    exit;
}

$pdo = getDatabaseConnection();

if ($type === 'questions') {
    $fields = [
        'id',
        'hero_id',
        'hero_name',
        'difficulty',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
    ];

    $statement = $pdo->prepare("
        SELECT
            questions.id,
            questions.hero_id,
            heroes.name AS hero_name,
            questions.difficulty,
            questions.question_text,
            questions.option_a,
            questions.option_b,
            questions.option_c,
            questions.option_d,
            questions.correct_option
        FROM questions
        INNER JOIN heroes ON questions.hero_id = heroes.id
        ORDER BY questions.id
    ");
} else {
    $fields = [
        'id',
        'name',
        'description',
        'image_url',
    ];

    $statement = $pdo->prepare("
        SELECT
            id,
            name,
            description,
            image_url
        FROM heroes
        ORDER BY id
    ");
}

$statement->execute();
$rows = $statement->fetchAll();
$filename = $type . '.' . $format;

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, $fields);

    foreach ($rows as $row) {
        $csvRow = [];

        foreach ($fields as $field) {
            $csvRow[] = $row[$field];
        }

        fputcsv($output, $csvRow);
    }

    fclose($output);
    exit;
}

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo json_encode($rows, JSON_PRETTY_PRINT);
exit;

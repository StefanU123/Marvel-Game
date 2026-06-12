<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/i18n.php';

requireAdmin();

$pdo = getDatabaseConnection();

$questionStatement = $pdo->prepare("
    SELECT
        questions.id,
        questions.difficulty,
        questions.question_text,
        questions.correct_option,
        heroes.name AS hero_name
    FROM questions
    INNER JOIN heroes ON questions.hero_id = heroes.id
    ORDER BY questions.id
");
$questionStatement->execute();
$questions = $questionStatement->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
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
                <h1 class="admin-title"><?php echo t('admin.manageQuestions'); ?></h1>
                <p class="admin-subtitle"><?php echo t('admin.questionsCount', count($questions)); ?></p>
            </div>
            <a class="button" href="add_question.php"><?php echo t('admin.addQuestion'); ?></a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?php echo t('admin.colId'); ?></th>
                        <th><?php echo t('admin.colHero'); ?></th>
                        <th><?php echo t('admin.colDifficulty'); ?></th>
                        <th><?php echo t('admin.colQuestion'); ?></th>
                        <th><?php echo t('admin.colCorrect'); ?></th>
                        <th><?php echo t('admin.colActions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                        <tr>
                            <td class="admin-id"><?php echo htmlspecialchars($question['id']); ?></td>
                            <td class="admin-hero-name"><?php echo htmlspecialchars($question['hero_name']); ?></td>
                            <td>
                                <span class="diff-badge diff-badge--<?php echo htmlspecialchars($question['difficulty']); ?>">
                                    <?php echo t('diff.' . $question['difficulty']); ?>
                                </span>
                            </td>
                            <td class="admin-q-text"><?php echo htmlspecialchars($question['question_text']); ?></td>
                            <td><span class="correct-badge"><?php echo htmlspecialchars($question['correct_option']); ?></span></td>
                            <td>
                                <div class="admin-actions">
                                    <a class="admin-action" href="edit_question.php?id=<?php echo htmlspecialchars($question['id']); ?>"><?php echo t('admin.edit'); ?></a>
                                    <a class="admin-action admin-action--danger" href="delete_question.php?id=<?php echo htmlspecialchars($question['id']); ?>" onclick="return confirm('<?php echo t('admin.confirmDelete'); ?>');"><?php echo t('admin.delete'); ?></a>
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

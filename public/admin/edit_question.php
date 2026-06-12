<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/i18n.php';

requireAdmin();

$id = $_GET['id'] ?? '';

if (!is_string($id) || !ctype_digit($id) || (int) $id <= 0) {
    echo 'Invalid question id.';
    exit;
}

$pdo = getDatabaseConnection();

$questionStatement = $pdo->prepare('SELECT * FROM questions WHERE id = :id');
$questionStatement->execute([
    'id' => (int) $id,
]);
$question = $questionStatement->fetch();

if (!$question) {
    echo 'Question not found.';
    exit;
}

$heroStatement = $pdo->prepare('SELECT id, name FROM heroes ORDER BY name');
$heroStatement->execute();
$heroes = $heroStatement->fetchAll();

$validDifficulties = ['easy', 'medium', 'hard'];
$validCorrectOptions = ['A', 'B', 'C', 'D'];

$error = '';
$heroId = $question['hero_id'];
$difficulty = $question['difficulty'];
$questionText = $question['question_text'];
$optionA = $question['option_a'];
$optionB = $question['option_b'];
$optionC = $question['option_c'];
$optionD = $question['option_d'];
$correctOption = $question['correct_option'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $heroId = trim($_POST['hero_id'] ?? '');
    $difficulty = trim($_POST['difficulty'] ?? '');
    $questionText = trim($_POST['question_text'] ?? '');
    $optionA = trim($_POST['option_a'] ?? '');
    $optionB = trim($_POST['option_b'] ?? '');
    $optionC = trim($_POST['option_c'] ?? '');
    $optionD = trim($_POST['option_d'] ?? '');
    $correctOption = trim($_POST['correct_option'] ?? '');

    if (
        $heroId === '' ||
        $difficulty === '' ||
        $questionText === '' ||
        $optionA === '' ||
        $optionB === '' ||
        $optionC === '' ||
        $optionD === '' ||
        $correctOption === ''
    ) {
        $error = 'All fields are required.';
    } elseif (filter_var($heroId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
        $error = 'Please choose a valid hero.';
    } elseif (!in_array($difficulty, $validDifficulties, true)) {
        $error = 'Please choose a valid difficulty.';
    } elseif (!in_array($correctOption, $validCorrectOptions, true)) {
        $error = 'Please choose a valid correct option.';
    } else {
        $updateStatement = $pdo->prepare("
            UPDATE questions
            SET
                hero_id = :hero_id,
                question_text = :question_text,
                option_a = :option_a,
                option_b = :option_b,
                option_c = :option_c,
                option_d = :option_d,
                correct_option = :correct_option,
                difficulty = :difficulty
            WHERE id = :id
        ");

        $updateStatement->execute([
            'hero_id' => $heroId,
            'question_text' => $questionText,
            'option_a' => $optionA,
            'option_b' => $optionB,
            'option_c' => $optionC,
            'option_d' => $optionD,
            'correct_option' => $correctOption,
            'difficulty' => $difficulty,
            'id' => (int) $id,
        ]);

        header('Location: questions.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
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
                <a class="admin-back" href="questions.php"><?php echo t('admin.backToQuestions'); ?></a>
                <h1 class="admin-title"><?php echo t('admin.editQuestionTitle'); ?></h1>
                <p class="admin-subtitle">#<?php echo htmlspecialchars($id); ?></p>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="admin-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="admin-form" method="post" action="edit_question.php?id=<?php echo htmlspecialchars($id); ?>">
            <div class="admin-field-row">
                <div class="admin-field">
                    <label for="hero_id"><?php echo t('admin.fieldHero'); ?></label>
                    <select name="hero_id" id="hero_id">
                        <option value=""><?php echo t('admin.chooseHero'); ?></option>
                        <?php foreach ($heroes as $hero): ?>
                            <option value="<?php echo htmlspecialchars($hero['id']); ?>" <?php if ($heroId == $hero['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($hero['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="difficulty"><?php echo t('admin.fieldDifficulty'); ?></label>
                    <select name="difficulty" id="difficulty">
                        <option value=""><?php echo t('admin.chooseDifficulty'); ?></option>
                        <?php foreach ($validDifficulties as $difficultyOption): ?>
                            <option value="<?php echo htmlspecialchars($difficultyOption); ?>" <?php if ($difficulty === $difficultyOption) echo 'selected'; ?>>
                                <?php echo t('diff.' . $difficultyOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="admin-field">
                <label for="question_text"><?php echo t('admin.fieldQuestionText'); ?></label>
                <textarea name="question_text" id="question_text"><?php echo htmlspecialchars($questionText); ?></textarea>
            </div>

            <div class="admin-field-row">
                <div class="admin-field">
                    <label for="option_a">Option A</label>
                    <input type="text" name="option_a" id="option_a" value="<?php echo htmlspecialchars($optionA); ?>">
                </div>
                <div class="admin-field">
                    <label for="option_b">Option B</label>
                    <input type="text" name="option_b" id="option_b" value="<?php echo htmlspecialchars($optionB); ?>">
                </div>
            </div>

            <div class="admin-field-row">
                <div class="admin-field">
                    <label for="option_c">Option C</label>
                    <input type="text" name="option_c" id="option_c" value="<?php echo htmlspecialchars($optionC); ?>">
                </div>
                <div class="admin-field">
                    <label for="option_d">Option D</label>
                    <input type="text" name="option_d" id="option_d" value="<?php echo htmlspecialchars($optionD); ?>">
                </div>
            </div>

            <div class="admin-field">
                <label for="correct_option"><?php echo t('admin.fieldCorrect'); ?></label>
                <select name="correct_option" id="correct_option">
                    <option value=""><?php echo t('admin.chooseCorrect'); ?></option>
                    <?php foreach ($validCorrectOptions as $correctOptionValue): ?>
                        <option value="<?php echo htmlspecialchars($correctOptionValue); ?>" <?php if ($correctOption === $correctOptionValue) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($correctOptionValue); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-actions">
                <button class="button" type="submit"><?php echo t('admin.saveQuestion'); ?></button>
                <a class="button button--ghost" href="questions.php"><?php echo t('admin.cancel'); ?></a>
            </div>
        </form>
    </main>
</body>
</html>

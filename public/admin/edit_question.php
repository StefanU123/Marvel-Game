<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main>
        <h1>Edit Question</h1>

        <p><a href="questions.php">Back to Questions</a></p>

        <?php if ($error !== ''): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post" action="edit_question.php?id=<?php echo htmlspecialchars($id); ?>">
            <p>
                <label for="hero_id">Hero</label><br>
                <select name="hero_id" id="hero_id">
                    <option value="">Choose a hero</option>
                    <?php foreach ($heroes as $hero): ?>
                        <option value="<?php echo htmlspecialchars($hero['id']); ?>" <?php if ($heroId == $hero['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($hero['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="difficulty">Difficulty</label><br>
                <select name="difficulty" id="difficulty">
                    <option value="">Choose difficulty</option>
                    <?php foreach ($validDifficulties as $difficultyOption): ?>
                        <option value="<?php echo htmlspecialchars($difficultyOption); ?>" <?php if ($difficulty === $difficultyOption) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($difficultyOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="question_text">Question text</label><br>
                <textarea name="question_text" id="question_text"><?php echo htmlspecialchars($questionText); ?></textarea>
            </p>

            <p>
                <label for="option_a">Option A</label><br>
                <input type="text" name="option_a" id="option_a" value="<?php echo htmlspecialchars($optionA); ?>">
            </p>

            <p>
                <label for="option_b">Option B</label><br>
                <input type="text" name="option_b" id="option_b" value="<?php echo htmlspecialchars($optionB); ?>">
            </p>

            <p>
                <label for="option_c">Option C</label><br>
                <input type="text" name="option_c" id="option_c" value="<?php echo htmlspecialchars($optionC); ?>">
            </p>

            <p>
                <label for="option_d">Option D</label><br>
                <input type="text" name="option_d" id="option_d" value="<?php echo htmlspecialchars($optionD); ?>">
            </p>

            <p>
                <label for="correct_option">Correct option</label><br>
                <select name="correct_option" id="correct_option">
                    <option value="">Choose correct option</option>
                    <?php foreach ($validCorrectOptions as $correctOptionValue): ?>
                        <option value="<?php echo htmlspecialchars($correctOptionValue); ?>" <?php if ($correctOption === $correctOptionValue) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($correctOptionValue); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <button type="submit">Save Question</button>
            </p>
        </form>
    </main>
</body>
</html>

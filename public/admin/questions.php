<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main>
        <h1>Manage Questions</h1>

        <p>
            <a href="index.php">Back to Admin Panel</a>
            |
            <a href="add_question.php">Add Question</a>
        </p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hero</th>
                    <th>Difficulty</th>
                    <th>Question text</th>
                    <th>Correct option</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($question['id']); ?></td>
                        <td><?php echo htmlspecialchars($question['hero_name']); ?></td>
                        <td><?php echo htmlspecialchars($question['difficulty']); ?></td>
                        <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                        <td><?php echo htmlspecialchars($question['correct_option']); ?></td>
                        <td>
                            <a href="#">Edit</a>
                            |
                            <a href="#">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>

<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

requireAdmin();

$validTypes = ['questions', 'heroes'];
$validFormats = ['csv', 'json'];
$validDifficulties = ['easy', 'medium', 'hard'];
$validCorrectOptions = ['A', 'B', 'C', 'D'];

$error = '';
$success = '';
$importedRows = 0;
$type = $_POST['type'] ?? 'questions';
$format = $_POST['format'] ?? 'csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type'] ?? '');
    $format = trim($_POST['format'] ?? '');
    $rows = [];

    if (!in_array($type, $validTypes, true)) {
        $error = 'Please choose a valid import type.';
    } elseif (!in_array($format, $validFormats, true)) {
        $error = 'Please choose a valid import format.';
    } elseif (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload a file.';
    } else {
        $filePath = $_FILES['import_file']['tmp_name'];
        $requiredFields = [];

        if ($type === 'heroes') {
            $requiredFields = ['name', 'description', 'image_url'];
        } else {
            $requiredFields = [
                'hero_id',
                'difficulty',
                'question_text',
                'option_a',
                'option_b',
                'option_c',
                'option_d',
                'correct_option',
            ];
        }

        if ($format === 'csv') {
            $handle = fopen($filePath, 'r');

            if ($handle === false) {
                $error = 'Could not read the CSV file.';
            } else {
                $headers = fgetcsv($handle);

                if ($headers === false) {
                    $error = 'CSV file is empty.';
                } else {
                    $headers = array_map('trim', $headers);

                    foreach ($requiredFields as $field) {
                        if (!in_array($field, $headers, true)) {
                            $error = 'Missing required column: ' . $field;
                            break;
                        }
                    }

                    while ($error === '' && ($data = fgetcsv($handle)) !== false) {
                        if (count($data) === 1 && trim($data[0]) === '') {
                            continue;
                        }

                        $row = [];

                        foreach ($headers as $index => $header) {
                            $row[$header] = $data[$index] ?? '';
                        }

                        $rows[] = $row;
                    }
                }

                fclose($handle);
            }
        } else {
            $jsonText = file_get_contents($filePath);
            $rows = json_decode($jsonText, true);

            if (!is_array($rows) || json_last_error() !== JSON_ERROR_NONE) {
                $error = 'JSON file must contain an array of objects.';
                $rows = [];
            }
        }

        $cleanRows = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                $error = 'Every row must be an object or CSV row.';
                break;
            }

            $cleanRow = [];

            foreach ($requiredFields as $field) {
                $value = trim((string) ($row[$field] ?? ''));

                if ($value === '') {
                    $error = 'All required fields must be filled.';
                    break 2;
                }

                $cleanRow[$field] = $value;
            }

            if ($type === 'questions') {
                if (filter_var($cleanRow['hero_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    $error = 'Each question must have a valid hero_id.';
                    break;
                }

                if (!in_array($cleanRow['difficulty'], $validDifficulties, true)) {
                    $error = 'Each question difficulty must be easy, medium, or hard.';
                    break;
                }

                if (!in_array($cleanRow['correct_option'], $validCorrectOptions, true)) {
                    $error = 'Each correct_option must be A, B, C, or D.';
                    break;
                }
            }

            $cleanRows[] = $cleanRow;
        }

        if ($error === '') {
            try {
                $pdo = getDatabaseConnection();
                $pdo->beginTransaction();

                if ($type === 'heroes') {
                    $insertStatement = $pdo->prepare("
                        INSERT OR IGNORE INTO heroes (
                            name,
                            description,
                            image_url
                        ) VALUES (
                            :name,
                            :description,
                            :image_url
                        )
                    ");

                    foreach ($cleanRows as $row) {
                        $insertStatement->execute([
                            'name' => $row['name'],
                            'description' => $row['description'],
                            'image_url' => $row['image_url'],
                        ]);

                        $importedRows += $insertStatement->rowCount();
                    }
                } else {
                    $insertStatement = $pdo->prepare("
                        INSERT INTO questions (
                            hero_id,
                            question_text,
                            option_a,
                            option_b,
                            option_c,
                            option_d,
                            correct_option,
                            difficulty
                        ) VALUES (
                            :hero_id,
                            :question_text,
                            :option_a,
                            :option_b,
                            :option_c,
                            :option_d,
                            :correct_option,
                            :difficulty
                        )
                    ");

                    foreach ($cleanRows as $row) {
                        $insertStatement->execute([
                            'hero_id' => $row['hero_id'],
                            'question_text' => $row['question_text'],
                            'option_a' => $row['option_a'],
                            'option_b' => $row['option_b'],
                            'option_c' => $row['option_c'],
                            'option_d' => $row['option_d'],
                            'correct_option' => $row['correct_option'],
                            'difficulty' => $row['difficulty'],
                        ]);

                        $importedRows += $insertStatement->rowCount();
                    }
                }

                $pdo->commit();
                $success = 'Imported ' . $importedRows . ' rows.';
            } catch (Throwable $exception) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = 'Import failed. Please check your file data.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main>
        <h1>Import Data</h1>

        <p><a href="index.php">Back to Admin Panel</a></p>

        <?php if ($error !== ''): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <p><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="post" action="import.php" enctype="multipart/form-data">
            <p>
                <label for="type">Type</label><br>
                <select name="type" id="type">
                    <option value="questions" <?php if ($type === 'questions') echo 'selected'; ?>>questions</option>
                    <option value="heroes" <?php if ($type === 'heroes') echo 'selected'; ?>>heroes</option>
                </select>
            </p>

            <p>
                <label for="format">Format</label><br>
                <select name="format" id="format">
                    <option value="csv" <?php if ($format === 'csv') echo 'selected'; ?>>csv</option>
                    <option value="json" <?php if ($format === 'json') echo 'selected'; ?>>json</option>
                </select>
            </p>

            <p>
                <label for="import_file">Import file</label><br>
                <input type="file" name="import_file" id="import_file">
            </p>

            <p>
                <button type="submit">Import Data</button>
            </p>
        </form>
    </main>
</body>
</html>

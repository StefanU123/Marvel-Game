<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/i18n.php';

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
<html lang="<?php echo lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data</title>
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
                <h1 class="admin-title"><?php echo t('admin.importTitle'); ?></h1>
                <p class="admin-subtitle"><?php echo t('admin.importSubtitle'); ?></p>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="admin-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="admin-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form class="admin-form" method="post" action="import.php" enctype="multipart/form-data">
            <div class="admin-field-row">
                <div class="admin-field">
                    <label for="type"><?php echo t('admin.type'); ?></label>
                    <select name="type" id="type">
                        <option value="questions" <?php if ($type === 'questions') echo 'selected'; ?>><?php echo t('admin.manageQuestions'); ?></option>
                        <option value="heroes" <?php if ($type === 'heroes') echo 'selected'; ?>><?php echo t('admin.manageHeroes'); ?></option>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="format"><?php echo t('admin.format'); ?></label>
                    <select name="format" id="format">
                        <option value="csv" <?php if ($format === 'csv') echo 'selected'; ?>>CSV</option>
                        <option value="json" <?php if ($format === 'json') echo 'selected'; ?>>JSON</option>
                    </select>
                </div>
            </div>

            <div class="admin-field">
                <label for="import_file"><?php echo t('admin.importFile'); ?></label>
                <input type="file" name="import_file" id="import_file">
                <span class="admin-note"><?php echo t('admin.importNote'); ?></span>
            </div>

            <div class="admin-form-actions">
                <button class="button" type="submit"><?php echo t('admin.importData'); ?></button>
                <a class="button button--ghost" href="index.php"><?php echo t('admin.cancel'); ?></a>
            </div>
        </form>
    </main>
</body>
</html>

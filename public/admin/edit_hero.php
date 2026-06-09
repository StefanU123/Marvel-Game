<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hero</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main>
        <h1>Edit Hero</h1>

        <p><a href="heroes.php">Back to Heroes</a></p>

        <?php if ($error !== ''): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post" action="edit_hero.php?id=<?php echo htmlspecialchars($id); ?>">
            <p>
                <label for="name">Name</label><br>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>">
            </p>

            <p>
                <label for="description">Description</label><br>
                <textarea name="description" id="description"><?php echo htmlspecialchars($description); ?></textarea>
            </p>

            <p>
                <label for="image_url">Image URL</label><br>
                <input type="text" name="image_url" id="image_url" value="<?php echo htmlspecialchars($imageUrl); ?>">
            </p>

            <p>
                <button type="submit">Save Hero</button>
            </p>
        </form>
    </main>
</body>
</html>

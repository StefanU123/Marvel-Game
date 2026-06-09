<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

requireAdmin();

$pdo = getDatabaseConnection();

$heroStatement = $pdo->prepare('SELECT id, name, description, image_url FROM heroes ORDER BY id');
$heroStatement->execute();
$heroes = $heroStatement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Heroes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main>
        <h1>Manage Heroes</h1>

        <p><a href="index.php">Back to Admin Panel</a></p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($heroes as $hero): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hero['id']); ?></td>
                        <td>
                            <img
                                src="<?php echo htmlspecialchars('../' . $hero['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($hero['name']); ?>"
                                style="max-width: 100px;"
                            >
                        </td>
                        <td><?php echo htmlspecialchars($hero['name']); ?></td>
                        <td><?php echo htmlspecialchars($hero['description']); ?></td>
                        <td><?php echo htmlspecialchars($hero['image_url']); ?></td>
                        <td>
                            <a href="edit_hero.php?id=<?php echo htmlspecialchars($hero['id']); ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>

<?php
require_once '../../includes/auth.php';

requireAdmin();

$user = currentUser();
$username = $user['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main>
        <h1>Admin Panel</h1>
        <p>Welcome, <?php echo htmlspecialchars($username); ?></p>

        <nav>
            <ul>
                <li><a href="questions.php">Questions</a></li>
                <li><a href="heroes.php">Heroes</a></li>
                <li><a href="import.php">Import</a></li>
                <li><a href="export.php">Export</a></li>
                <li><a href="../index.php">Public Site</a></li>
            </ul>
        </nav>
    </main>
</body>
</html>

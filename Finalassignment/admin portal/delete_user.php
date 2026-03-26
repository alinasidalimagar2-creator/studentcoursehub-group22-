<?php
include 'config.php';
include 'auth.php';

$id = $_GET['id'] ?? null;
$successMessage = '';
$errorMessage = '';

if (!$id || !is_numeric($id)) {
    die("Invalid or missing user ID.");
}

// Fetch user
$stmt = $pdo->prepare("SELECT `id`, `name`, `email` FROM `users` WHERE `id` = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $del = $pdo->prepare("DELETE FROM `users` WHERE `id` = ?");
        $del->execute([$id]);
        $successMessage = "User deleted successfully!";
        header("Location: manage_user.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        // If there are FKs referencing user, you might get an integrity error here
        $errorMessage = "Could not delete user. " . htmlspecialchars($e->getMessage());
        header("Location: manage_user.php?error=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete User</title>
    <!-- Reuse your delete page styling -->
    <link rel="stylesheet" href="delete_programme.css">
    <script>
        function confirmDelete(e) {
            if (!confirm("Are you sure you want to delete this user? This action cannot be undone.")) {
                e.preventDefault(); // if user press cancel it stop form submission
            }
        }
    </script>
</head>
<body>

<h2>Delete User</h2>

<?php if (!empty($errorMessage)): ?>
    <div class="notification error"><?= htmlspecialchars($errorMessage) ?></div>
<?php endif; ?>

<?php if (!empty($successMessage)): ?>
    <div class="notification success"><?= htmlspecialchars($successMessage) ?></div>
<?php endif; ?>

<a href="manage_user.php" class="back-btn">← Back</a>


<!-- tell php to delte user -->
<form method="POST">
    <label>User ID:</label>
    <input type="text" value="<?= htmlspecialchars($user['id']) ?>" readonly>

    <label>Name:</label>
    <input type="text" value="<?= htmlspecialchars($user['name']) ?>" readonly>

    <label>Email:</label>
    <input type="text" value="<?= htmlspecialchars($user['email']) ?>" readonly>

    <button type="submit" onclick="confirmDelete(event)">Delete User</button>
</form>

</body>
</html>

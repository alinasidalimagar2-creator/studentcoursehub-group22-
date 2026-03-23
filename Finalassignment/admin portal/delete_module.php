<?php
include 'config.php';
include 'auth.php';

$moduleID = $_GET['id'] ?? null;

if (!$moduleID) {
    header("Location: module.php");
    exit;
}

// Fetch module details
$stmt = $pdo->prepare("SELECT * FROM Modules WHERE ModuleID=?");
$stmt->execute([$moduleID]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    header("Location: module.php");
    exit;
}

// Fetch staff for Module Leader display
$staff = $pdo->query("SELECT StaffID, Name FROM Staff ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);

$successMessage = '';
if (isset($_POST['confirm'])) {
    $pdo->prepare("DELETE FROM Modules WHERE ModuleID=?")->execute([$moduleID]);
    $successMessage = "Module deleted successfully!";
}

// Cancel deletion
if (isset($_POST['cancel'])) {
    header("Location: module.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Module</title>
    <link rel="stylesheet" href="delete_module.css">
</head>
<body>

<a href="modules.php" class="back-btn">← Back</a>
<h2>Delete Module</h2>


<form method="POST">
    <?php if ($successMessage): ?>
        <p class="success"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>

    <label>Module ID:</label>
    <input type="text" value="<?= htmlspecialchars($module['ModuleID']); ?>" readonly>

    <label>Module Name:</label>
    <input type="text" value="<?= htmlspecialchars($module['ModuleName']); ?>" readonly>

    <label>Description:</label>
    <textarea rows="4" readonly><?= htmlspecialchars($module['Description']); ?></textarea>

    <label>Module Leader ID:</label>
    <input type="text" value="<?= htmlspecialchars($module['ModuleLeaderID']); ?>" readonly>

    <div class="button-group">
        <button type="submit" name="confirm" class="delete-button">Delete</button>
        <button type="submit" name="cancel" class="cancel-button">Cancel</button>
    </div>
</form>

</body>
</html>



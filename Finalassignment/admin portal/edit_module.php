<?php
include 'config.php';
include 'auth.php';

if (!isset($_GET['id'])) {
    header("Location: module.php");
    exit;
}

$id = $_GET['id'];

// Fetch module
$stmt = $pdo->prepare("SELECT * FROM Modules WHERE ModuleID=?");
$stmt->execute([$id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    echo "Module not found!";
    exit;
}

// Fetch staff for dropdown
$staff = $pdo->query("SELECT StaffID, Name FROM Staff ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);

// Update
if (isset($_POST['submit'])) {
    $moduleName = $_POST['ModuleName'];
    $moduleLeader = $_POST['ModuleLeaderID'];
    $description = $_POST['Description'];

    $stmt = $pdo->prepare("UPDATE Modules SET ModuleName=?, ModuleLeaderID=?, Description=? WHERE ModuleID=?");
    $stmt->execute([$moduleName, $moduleLeader, $description, $id]);

    header("Location: moduleS.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Module</title>
    <link rel="stylesheet" href="edit_module.css">
</head>
<body>
<a href="modules.php" class="back-btn">← Back</a>
<h2>Edit Module</h2>

<form method="POST" action="">
    <label for="ModuleID">Module ID</label>
    <input type="text" id="ModuleID" name="ModuleID" value="<?= htmlspecialchars($module['ModuleID']); ?>" readonly>

    <label for="ModuleName">Module Name</label>
    <input type="text" id="ModuleName" name="ModuleName" value="<?= htmlspecialchars($module['ModuleName']); ?>" required>

    <label for="ModuleLeaderID">Module Leader</label>
    <select id="ModuleLeaderID" name="ModuleLeaderID" required>
        <option value="">-- Select Leader --</option>
        <?php foreach ($staff as $s): ?>
            <option value="<?= $s['StaffID']; ?>" <?= $s['StaffID']==$module['ModuleLeaderID']?'selected':'' ?>>
                <?= htmlspecialchars($s['Name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="Description">Module Description</label>
    <textarea id="Description" name="Description" rows="4" required><?= htmlspecialchars($module['Description']); ?></textarea>

    <button type="submit" name="submit">Update Module</button>
</form>

</body>
</html>

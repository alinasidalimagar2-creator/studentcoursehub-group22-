<?php
include 'config.php';
include 'auth.php';

// Delete module if 'delete' parameter is set
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM Modules WHERE ModuleID=?")->execute([$_GET['delete']]);
}

// Fetch all modules with leader names
$modules = $pdo->query("
    SELECT m.*, s.Name AS Leader
    FROM Modules m
    LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
    ORDER BY m.ModuleName
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Modules</title>
    <link rel="stylesheet" href="module.css">
</head>
<body>

<h2>Modules</h2>
<a href="dashboard.php" class="button">← Back</a>
<span style="margin: 0 10px; color: #fff;">|</span>
<a href="add_module.php" class="button">+ Add New Module</a>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Leader</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($modules as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['ModuleName']); ?></td>
                <td><?= htmlspecialchars($m['Leader']); ?></td>
                <td><?= htmlspecialchars($m['Description']); ?></td>
                <td>
                    <a href="edit_module.php?id=<?= $m['ModuleID']; ?>">Edit</a> | 
                    <a href="delete_module.php?id=<?= $m['ModuleID']; ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>

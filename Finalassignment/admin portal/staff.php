<?php
include 'config.php';
include 'auth.php';

// Delete staff if requested
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Staff WHERE StaffID = ?");
    $stmt->execute([$_GET['delete']]);
}

// Fetch all staff
$staffList = $pdo->query("SELECT StaffID, Name FROM Staff ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Staff</title>
    <link rel="stylesheet" href="staff.css">
</head>
<body>
<h2>Staff Managem ent</h2>
<a href="../user/dashboard.php" class="button">← Back</a>
<span style="margin: 0 10px; color: #fff;">|</span>
<a href="add_staff.php" class="button">+ Add New Staff</a>

<table>
    <tr>
        <th>Staff ID</th>
        <th>Name</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($staffList as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['StaffID']); ?></td>
        <td><?= htmlspecialchars($s['Name']); ?></td>
        <td>
        
    <a href="edit_staff.php?id=<?= $s['StaffID']; ?>" >Edit</a> | 
    <a href="delete_staff.php?id=<?= $s['StaffID']; ?>" >Delete</a>
</td>

        
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>

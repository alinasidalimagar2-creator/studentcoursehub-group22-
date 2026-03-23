<?php
include 'config.php';
include 'auth.php';

// (Optional) role guard
// if (!in_array($_SESSION['user']['role'] ?? '', ['Admin','Staff'], true)) {
//     http_response_code(403);
//     exit('Forbidden');
// }

// Handle status messages from redirects (e.g., delete_programme.php)
$status = $_GET['status'] ?? '';
$notice = '';
if ($status === 'deleted') {
    $notice = 'Programme deleted successfully.';
} elseif ($status === 'notfound') {
    $notice = 'Programme not found.';
} elseif ($status === 'csrf') {
    $notice = 'Your session expired. Please try again.';
}

// Fetch all programmes with level and leader names
$sql = "
    SELECT 
        p.ProgrammeID,
        p.ProgrammeName,
        p.Description,
        l.LevelName,
        s.Name AS Leader
    FROM Programmes p
    LEFT JOIN Levels l ON p.LevelID = l.LevelID
    LEFT JOIN Staff  s ON p.ProgrammeLeaderID = s.StaffID
    ORDER BY p.ProgrammeName
";
$programmes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Programmes</title>
    <link rel="stylesheet" href="module.css"> <!-- reuse same CSS as Modules page -->
</head>
<body>

<h2>Programmes</h2>

<!-- Top actions -->
<a href="dashboard.php" class="button">← Back</a>
<span class="header-pipe">|</span>
<a href="add_programme.php" class="button">+ Add New Programme</a>

<!-- Status message (if any) -->
<?php if ($notice): ?>
    <div class="notification <?= $status === 'deleted' ? 'success' : 'error' ?>">
        <?= htmlspecialchars($notice) ?>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Level</th>
            <th>Leader</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($programmes)): ?>
        <?php foreach ($programmes as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['ProgrammeName'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['LevelName'] ?? '—') ?></td>
                <td><?= htmlspecialchars($p['Leader'] ?? '—') ?></td>
                <td><?= htmlspecialchars($p['Description'] ?? '') ?></td>
                <td class="actions">
                    <a href="edit_programme.php?id=<?= (int)$p['ProgrammeID'] ?>">Edit</a> |
                    <a href="delete_programme.php?id=<?= (int)$p['ProgrammeID'] ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="5">No programmes found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>

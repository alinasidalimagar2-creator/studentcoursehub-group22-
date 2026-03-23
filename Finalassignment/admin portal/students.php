<?php
include 'config.php';
include 'auth.php';

// Fetch students including InterestID
$students = $pdo->query("
    SELECT i.InterestID, i.StudentName, i.Email, p.ProgrammeName, i.RegisteredAt
    FROM InterestedStudents i
    JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
    ORDER BY i.RegisteredAt DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Interested Students</title>
    <link rel="stylesheet" href="students.css">
</head>
<body>
<h2>Interested Students</h2>
<a href="dashboard.php">← Back</a>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Programme</th>
    <th>Registered</th>
</tr>
<?php foreach ($students as $s): ?>
<tr>
    <td><?= htmlspecialchars($s['InterestID']); ?></td>
    <td><?= htmlspecialchars($s['StudentName']); ?></td>
    <td><?= htmlspecialchars($s['Email']); ?></td>
    <td><?= htmlspecialchars($s['ProgrammeName']); ?></td>
    <td><?= htmlspecialchars($s['RegisteredAt']); ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>

<?php 
include 'config.php';
include 'auth.php';


$id = $_GET['id'] ?? null;
$name = $description = '';
$level = $leader = '';
$successMessage = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM Programmes WHERE ProgrammeID = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $name = $data['ProgrammeName'];
    $description = $data['Description'];
    $level = $data['LevelID'];
    $leader = $data['ProgrammeLeaderID'];
}

// Fetch dropdowns
$levels = $pdo->query("SELECT * FROM Levels")->fetchAll(PDO::FETCH_ASSOC);
$staff = $pdo->query("SELECT * FROM Staff")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['ProgrammeName'];
    $description = $_POST['Description'];
    $level = $_POST['LevelID'];
    $leader = $_POST['ProgrammeLeaderID'];

    if ($id) {
        $stmt = $pdo->prepare("UPDATE Programmes SET ProgrammeName=?, Description=?, LevelID=?, ProgrammeLeaderID=? WHERE ProgrammeID=?");
        $stmt->execute([$name, $description, $level, $leader, $id]);
        $successMessage = "Programme updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO Programmes (ProgrammeName, Description, LevelID, ProgrammeLeaderID) VALUES (?,?,?,?)");
        $stmt->execute([$name, $description, $level, $leader]);
        $successMessage = "Programme added successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $id ? 'Edit' : 'Add'; ?> Programme</title>
    <link rel="stylesheet" href="edit_programme.css">
</head>
<body>
<h2><?= $id ? 'Edit' : 'Add'; ?> Programme</h2>

<?php if ($successMessage): ?>
    <p class="success"><?= htmlspecialchars($successMessage) ?></p>
<?php endif; ?>
<a href="programmes.php" class="back-btn">← Back</a>


<form method="POST">
    <label>Name:</label><br>
    <input type="text" name="ProgrammeName" value="<?= htmlspecialchars($name); ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="Description" rows="4" cols="50"><?= htmlspecialchars($description); ?></textarea><br><br>

    <label>Level:</label><br>
    <select name="LevelID" required>
        <?php foreach ($levels as $l): ?>
            <option value="<?= $l['LevelID']; ?>" <?= $l['LevelID']==$level?'selected':''; ?>>
                <?= htmlspecialchars($l['LevelName']); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Programme Leader:</label><br>
    <select name="ProgrammeLeaderID" required>
        <?php foreach ($staff as $s): ?>
            <option value="<?= $s['StaffID']; ?>" <?= $s['StaffID']==$leader?'selected':''; ?>>
                <?= htmlspecialchars($s['Name']); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Save</button>
</form>
</body>
</html>

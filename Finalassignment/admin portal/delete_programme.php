<?php

include 'config.php';
include 'auth.php';

/* ------------------ ID VALIDATION ---------------------- */
$id = $_GET['id'] ?? null;
$id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    header('Location: programmes.php');
    exit;
}

/* ------------------ CSRF SETUP ------------------ */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
function csrf_ok($t) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $t ?? '');
}

/* ------------------ FETCH PROGRAMME ------------------ */
$stmt = $pdo->prepare("
    SELECT 
        p.ProgrammeID,
        p.ProgrammeName,
        p.Description,
        p.LevelID,
        p.ProgrammeLeaderID,
        l.LevelName,
        s.Name AS LeaderName
    FROM Programmes p
    LEFT JOIN Levels l ON p.LevelID = l.LevelID
    LEFT JOIN Staff  s ON p.ProgrammeLeaderID = s.StaffID
    WHERE p.ProgrammeID = ?
");
$stmt->execute([$id]);
$programme = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$programme) {
    header('Location: programmes.php?status=notfound');
    exit;
}

/* ------------------ HANDLE POST (DELETE) ------------------ */
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf_token'] ?? '')) {
        header('Location: programmes.php?status=csrf');
        exit;
    }

    // Check dependencies: any ProgrammeModules rows for this Programme?
    $dep = $pdo->prepare("SELECT COUNT(*) FROM ProgrammeModules WHERE ProgrammeID = ?");
    $dep->execute([$id]);
    $dependentCount = (int)$dep->fetchColumn();

    if ($dependentCount > 0) {
        $errorMessage = "Cannot delete this programme because it has {$dependentCount} associated module(s). Please remove them first.";
    } else {
        $del = $pdo->prepare("DELETE FROM Programmes WHERE ProgrammeID = ?");
        $del->execute([$id]);
        header('Location: programmes.php?status=deleted');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Programme</title>
    <link rel="stylesheet" href="delete_programme.css">
    <script>
        function confirmDelete(e){
            if(!confirm("Are you sure you want to delete this programme? This action cannot be undone.")){
                e.preventDefault();
            }
        }
    </script>
</head>
<body>

<h2>Delete Programme</h2>

<?php if (!empty($errorMessage)): ?>
  <div class="notification error"><?= htmlspecialchars($errorMessage) ?></div>
<?php endif; ?>

<a href="programmes.php" class="back-btn">← Back</a>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

    <label>Programme Name:</label>
    <input type="text" value="<?= htmlspecialchars($programme['ProgrammeName'] ?? '') ?>" readonly>

    <label>Description:</label>
    <textarea rows="4" readonly><?= htmlspecialchars($programme['Description'] ?? '') ?></textarea>

    <label>Level:</label>
    <input type="text" value="<?= htmlspecialchars($programme['LevelName'] ?? '—') ?>" readonly>

    <label>Programme Leader:</label>
    <input type="text" value="<?= htmlspecialchars($programme['LeaderName'] ?? '—') ?>" readonly>

    <button type="submit" class="confirm-delete" onclick="confirmDelete(event)">Delete Programme</button>
</form>

</body>
</html>

<?php
include 'config.php';
include 'auth.php';

/* CSRF */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
function csrf_ok($t){ return hash_equals($_SESSION['csrf_token'] ?? '', $t ?? ''); }

/* Validate id */
$id = $_GET['id'] ?? null;
$id = filter_var($id, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if ($id === false) { header('Location: staff.php'); exit; }

/* Fetch staff */
$hasTitle = (bool)$pdo->query("SHOW COLUMNS FROM Staff LIKE 'Title'")->fetch();
$hasPhoto = (bool)$pdo->query("SHOW COLUMNS FROM Staff LIKE 'PhotoUrl'")->fetch();
$sel = "SELECT StaffID, Name" . ($hasTitle?", Title":"") . ($hasPhoto?", PhotoUrl":"") . " FROM Staff WHERE StaffID = ?";
$stmt = $pdo->prepare($sel);
$stmt->execute([$id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$staff) { header('Location: staff.php?status=notfound'); exit; }

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf_token'] ?? '')) {
        header('Location: staff.php?status=csrf'); exit;
    }

    try {
        // Dependency checks
        $progCount = 0; $modCount = 0;
        if ($pdo->query("SHOW TABLES LIKE 'Programmes'")->fetch()) {
            $c1 = $pdo->prepare("SELECT COUNT(*) FROM Programmes WHERE ProgrammeLeaderID = ?");
            $c1->execute([$id]); $progCount = (int)$c1->fetchColumn();
        }
        if ($pdo->query("SHOW TABLES LIKE 'Modules'")->fetch()) {
            $c2 = $pdo->prepare("SELECT COUNT(*) FROM Modules WHERE ModuleLeaderID = ?");
            $c2->execute([$id]); $modCount = (int)$c2->fetchColumn();
        }

        if ($progCount > 0 || $modCount > 0) {
            $parts = [];
            if ($progCount > 0) $parts[] = "$progCount programme(s)";
            if ($modCount > 0)  $parts[] = "$modCount module(s)";
            $errorMessage = "Cannot delete this staff member because they are assigned to " . implode(' and ', $parts) . ". Reassign or remove those first.";
        } else {
            $del = $pdo->prepare("DELETE FROM Staff WHERE StaffID = ?");
            $del->execute([$id]);
            header('Location: staff.php?status=deleted'); exit;
        }
    } catch (PDOException $e) {
        $errorMessage = 'Failed to delete staff due to related records. Please reassign and try again.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Delete Staff</title>
  <link rel="stylesheet" href="delete_programme.css">
  <script>
    function confirmDelete(e){
      if(!confirm("Are you sure you want to delete this staff member? This action cannot be undone.")){
        e.preventDefault();
      }
    }
  </script>
</head>
<body>
<h2>Delete Staff</h2>
<a href="staff.php" class="back-btn">← Back</a>


<?php if (!empty($errorMessage)): ?>
  <div class="notification error"><?= htmlspecialchars($errorMessage) ?></div>
<?php endif; ?>

<form method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

  <label>Name</label>
  <input type="text" value="<?= htmlspecialchars($staff['Name'] ?? '') ?>" readonly>

  <?php if ($hasTitle): ?>
    <label>Title</label>
    <input type="text" value="<?= htmlspecialchars($staff['Title'] ?? '—') ?>" readonly>
  <?php endif; ?>

  <?php if ($hasPhoto && !empty($staff['PhotoUrl'])): ?>
    <label>Photo</label>
    <div><a href="<?= htmlspecialchars($staff['PhotoUrl']) ?>" target="_blank"><?= htmlspecialchars($staff['PhotoUrl']) ?></a></div>
  <?php endif; ?>

  <button type="submit" onclick="confirmDelete(event)">Delete Staff</button>
</form>
</body>
</html>

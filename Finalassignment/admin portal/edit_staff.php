<?php
include 'config.php';
include 'auth.php';

/* CSRF */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
function csrf_ok($t){ return hash_equals($_SESSION['csrf_token'] ?? '', $t ?? ''); }

/* Detect optional columns */
$hasTitle = (bool)$pdo->query("SHOW COLUMNS FROM Staff LIKE 'Title'")->fetch();
$hasPhoto = (bool)$pdo->query("SHOW COLUMNS FROM Staff LIKE 'PhotoUrl'")->fetch();

/* Validate id */
$id = $_GET['id'] ?? null;
$id = filter_var($id, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if ($id === false) { header('Location: staff.php'); exit; }

/* Fetch staff row */
$stmt = $pdo->prepare("SELECT StaffID, Name" . ($hasTitle?", Title":"") . ($hasPhoto?", PhotoUrl":"") . " FROM Staff WHERE StaffID = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) { header('Location: staff.php?status=notfound'); exit; }

$errors = [];
$success = '';
$Name = $row['Name'] ?? '';
$Title = $hasTitle ? ($row['Title'] ?? '') : '';
$PhotoUrl = $hasPhoto ? ($row['PhotoUrl'] ?? null) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $Name = trim($_POST['Name'] ?? '');
        if ($hasTitle) { $Title = trim($_POST['Title'] ?? ''); }

        if ($Name === '') {
            $errors[] = 'Name is required.';
        } elseif (mb_strlen($Name) > 150) {
            $errors[] = 'Name must be at most 150 characters.';
        }
        $newPhoto = $PhotoUrl;

        if ($hasPhoto && !empty($_FILES['Photo']['name'])) {
            if (!is_dir(__DIR__ . '/uploads/staff')) {
                @mkdir(__DIR__ . '/uploads/staff', 0755, true);
            }
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $maxBytes = 3 * 1024 * 1024;
            $fname = $_FILES['Photo']['name'];
            $tmp   = $_FILES['Photo']['tmp_name'];
            $size  = (int)($_FILES['Photo']['size'] ?? 0);
            $ext   = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $errors[] = 'Invalid image type. Allowed: jpg, jpeg, png, webp, gif.';
            } elseif ($size > $maxBytes) {
                $errors[] = 'Image too large (max 3 MB).';
            } elseif (is_uploaded_file($tmp)) {
                $base = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($fname, PATHINFO_FILENAME));
                $new  = $base . '_' . uniqid('', true) . '.' . $ext;
                $rel  = 'uploads/staff/' . $new;
                $abs  = __DIR__ . '/' . $rel;
                if (!move_uploaded_file($tmp, $abs)) {
                    $errors[] = 'Failed to save uploaded image.';
                } else {
                    $newPhoto = $rel;
                }
            } else {
                $errors[] = 'Invalid image upload.';
            }
        }

        if (empty($errors)) {
            try {
                if ($hasTitle && $hasPhoto) {
                    $upd = $pdo->prepare("UPDATE Staff SET Name=:n, Title=:t, PhotoUrl=:p WHERE StaffID=:id");
                    $upd->execute([':n'=>$Name, ':t'=>($Title===''?null:$Title), ':p'=>$newPhoto, ':id'=>$id]);
                } elseif ($hasTitle) {
                    $upd = $pdo->prepare("UPDATE Staff SET Name=:n, Title=:t WHERE StaffID=:id");
                    $upd->execute([':n'=>$Name, ':t'=>($Title===''?null:$Title), ':id'=>$id]);
                } elseif ($hasPhoto) {
                    $upd = $pdo->prepare("UPDATE Staff SET Name=:n, PhotoUrl=:p WHERE StaffID=:id");
                    $upd->execute([':n'=>$Name, ':p'=>$newPhoto, ':id'=>$id]);
                } else {
                    $upd = $pdo->prepare("UPDATE Staff SET Name=:n WHERE StaffID=:id");
                    $upd->execute([':n'=>$Name, ':id'=>$id]);
                }

                $PhotoUrl = $newPhoto;
                $success  = 'Staff member updated successfully.';

            } catch (PDOException $e) {
                $errors[] = 'Failed to update staff. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Staff</title>
  <link rel="stylesheet" href="delete_programme.css">
</head>
<body>
<h2>Edit Staff</h2>
<a href="staff.php" class="back-btn">← Back</a>


<?php if ($errors): ?>
  <div class="notification error">
    <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="notification success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

  <label for="Name">Name</label>
  <input id="Name" name="Name" type="text" value="<?= htmlspecialchars($Name) ?>" required maxlength="150">

  <?php if ($hasTitle): ?>
    <label for="Title">Title (optional)</label>
    <input id="Title" name="Title" type="text" value="<?= htmlspecialchars($Title) ?>" maxlength="150">
  <?php endif; ?>

  <?php if ($hasPhoto): ?>
    <?php if (!empty($PhotoUrl)): ?>
      <label>Current Photo</label>
      <div><a href="<?= htmlspecialchars($PhotoUrl) ?>" target="_blank"><?= htmlspecialchars($PhotoUrl) ?></a></div>
    <?php endif; ?>
    <label for="Photo">New Photo (optional)</label>
    <input id="Photo" name="Photo" type="file" accept=".jpg,.jpeg,.png,.webp,.gif">
  <?php endif; ?>

  <button type="submit">Save Changes</button>
</form>
</body>
</html>

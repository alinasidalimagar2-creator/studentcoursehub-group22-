<?php

include 'config.php';
include 'auth.php';

/* ---------------- CSRF ---------------- */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
function csrf_ok($t) { return hash_equals($_SESSION['csrf_token'] ?? '', $t ?? ''); }

/* ---------------- Detect optional columns ---------------- */
$hasTitle = (bool)$pdo->query("SHOW COLUMNS FROM Staff LIKE 'Title'")->fetch();
$hasPhoto = (bool)$pdo->query("SHOW COLUMNS FROM Staff LIKE 'PhotoUrl'")->fetch();

/* ---------------- State ---------------- */
$errors = [];
$success = '';

$Name  = '';
$Title = '';
$PhotoUrl = null;

/* ---------------- Handle POST ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        // Inputs
        $Name = trim($_POST['Name'] ?? '');
        if ($hasTitle) {
            $Title = trim($_POST['Title'] ?? '');
        }

        // Validate
        if ($Name === '') {
            $errors[] = 'Name is required.';
        } elseif (mb_strlen($Name) > 150) {
            $errors[] = 'Name must be at most 150 characters.';
        }
        if ($hasTitle && $Title !== '' && mb_strlen($Title) > 150) {
            $errors[] = 'Title must be at most 150 characters.';
        }

        // Optional photo upload if column exists
        if ($hasPhoto && !empty($_FILES['Photo']['name'])) {
            if (!is_dir(__DIR__ . '/uploads/staff')) {
                @mkdir(__DIR__ . '/uploads/staff', 0755, true);
            }
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $maxBytes = 3 * 1024 * 1024; // 3MB
            $fname = $_FILES['Photo']['name'] ?? '';
            $tmp   = $_FILES['Photo']['tmp_name'] ?? '';
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
                    $PhotoUrl = $rel;
                }
            } else {
                $errors[] = 'Invalid image upload.';
            }
        }

        // Insert
        if (empty($errors)) {
            try {
                if ($hasTitle && $hasPhoto) {
                    $stmt = $pdo->prepare("INSERT INTO Staff (Name, Title, PhotoUrl) VALUES (:n, :t, :p)");
                    $stmt->execute([
                        ':n' => $Name,
                        ':t' => ($Title === '' ? null : $Title),
                        ':p' => $PhotoUrl
                    ]);
                } elseif ($hasTitle) {
                    $stmt = $pdo->prepare("INSERT INTO Staff (Name, Title) VALUES (:n, :t)");
                    $stmt->execute([
                        ':n' => $Name,
                        ':t' => ($Title === '' ? null : $Title)
                    ]);
                } elseif ($hasPhoto) {
                    $stmt = $pdo->prepare("INSERT INTO Staff (Name, PhotoUrl) VALUES (:n, :p)");
                    $stmt->execute([
                        ':n' => $Name,
                        ':p' => $PhotoUrl
                    ]);
                } else {
                    // Minimal schema: only Name
                    $stmt = $pdo->prepare("INSERT INTO Staff (Name) VALUES (:n)");
                    $stmt->execute([':n' => $Name]);
                }

                $success = 'Staff member <strong>' . htmlspecialchars($Name) . '</strong> added successfully!';
                // Reset form
                $Name = '';
                $Title = '';
                $PhotoUrl = null;

            } catch (PDOException $e) {
                // Show friendlier error
                $errors[] = 'Failed to add staff. Please try again.';
                // Uncomment for debugging:
                // $errors[] = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Add Staff</title>
  <link rel="stylesheet" href="delete_programme.css">
</head>
<body>

<h2>Add Staff</h2>
<a href="staff.php" class="back-btn">← Back</a>

<?php if (!empty($errors)): ?>
  <div class="notification error">
    <?php foreach ($errors as $e): ?>
      <div><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
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
    <label for="Photo">Photo (optional)</label>
    <input id="Photo" name="Photo" type="file" accept=".jpg,.jpeg,.png,.webp,.gif">
  <?php endif; ?>

  <button type="submit">Create Staff</button>
</form>

</body>
</html>

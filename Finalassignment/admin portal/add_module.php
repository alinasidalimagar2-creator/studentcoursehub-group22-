<?php

include 'config.php';
include 'auth.php';

/* ---------------- CSRF ---------------- */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
function csrf_ok($t) { return hash_equals($_SESSION['csrf_token'] ?? '', $t ?? ''); }

/* ---------------- Detect if Modules.ProgrammeID exists (auto-enable Programme select) ---------------- */
$hasProgramme = false;
try {
    $col = $pdo->query("SHOW COLUMNS FROM Modules LIKE 'ProgrammeID'")->fetch(PDO::FETCH_ASSOC);
    $hasProgramme = (bool)$col;
} catch (Throwable $e) {
    $hasProgramme = false;
}

/* ---------------- Dropdown data ---------------- */
$staff = $pdo->query("SELECT StaffID, Name FROM Staff ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);
$programmes = [];
if ($hasProgramme) {
    $programmes = $pdo->query("SELECT ProgrammeID, ProgrammeName FROM Programmes ORDER BY ProgrammeName")->fetchAll(PDO::FETCH_ASSOC);
}

/* ---------------- State ---------------- */
$errors = [];
$success = '';
$ModuleName = '';
$Description = '';
$ModuleLeaderID = '';
$ProgrammeID = '';   // only used if $hasProgramme
$ImagePath = null;

/* ---------------- Handle POST ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        // Read inputs
        $ModuleName = trim($_POST['ModuleName'] ?? '');
        $Description = trim($_POST['Description'] ?? '');
        $ModuleLeaderID = $_POST['ModuleLeaderID'] ?? '';
        if ($hasProgramme) {
            $ProgrammeID = $_POST['ProgrammeID'] ?? '';
        }

        // Validate
        if ($ModuleName === '') {
            $errors[] = 'Module name is required.';
        } elseif (mb_strlen($ModuleName) > 255) {
            // even if DB column is TEXT, keep sensible limit
            $errors[] = 'Module name must be at most 255 characters.';
        }

        if ($ModuleLeaderID !== '' && (!ctype_digit((string)$ModuleLeaderID) || (int)$ModuleLeaderID < 1)) {
            $errors[] = 'Please choose a valid module leader.';
        }

        if ($hasProgramme && ($ProgrammeID === '' || !ctype_digit((string)$ProgrammeID) || (int)$ProgrammeID < 1)) {
            $errors[] = 'Please choose a valid programme.';
        }

        // Optional file upload (Image)
        if (!empty($_FILES['Image']['name'])) {
            if (!is_dir(__DIR__ . '/uploads/modules')) {
                @mkdir(__DIR__ . '/uploads/modules', 0755, true);
            }
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $maxBytes = 3 * 1024 * 1024; // 3MB
            $fname = $_FILES['Image']['name'];
            $tmp   = $_FILES['Image']['tmp_name'];
            $size  = $_FILES['Image']['size'] ?? 0;

            $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $errors[] = 'Invalid image type. Allowed: jpg, jpeg, png, webp, gif.';
            } elseif ($size > $maxBytes) {
                $errors[] = 'Image too large (max 3 MB).';
            } elseif (is_uploaded_file($tmp)) {
                $safeBase = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($fname, PATHINFO_FILENAME));
                $newName  = $safeBase . '_' . uniqid('', true) . '.' . $ext;
                $destRel  = 'uploads/modules/' . $newName;
                $destAbs  = __DIR__ . '/' . $destRel;
                if (!move_uploaded_file($tmp, $destAbs)) {
                    $errors[] = 'Failed to save uploaded image.';
                } else {
                    $ImagePath = $destRel; // store relative path in DB
                }
            } else {
                $errors[] = 'Invalid image upload.';
            }
        }

        // Insert
        if (empty($errors)) {
            try {
                if ($hasProgramme) {
                    $sql = "INSERT INTO Modules (ModuleName, Description, ProgrammeID, ModuleLeaderID, Image)
                            VALUES (:name, :desc, :prog, :leader, :img)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':name'   => $ModuleName,
                        ':desc'   => $Description === '' ? null : $Description,
                        ':prog'   => (int)$ProgrammeID,
                        ':leader' => ($ModuleLeaderID === '' ? null : (int)$ModuleLeaderID),
                        ':img'    => $ImagePath,
                    ]);
                } else {
                    $sql = "INSERT INTO Modules (ModuleName, Description, ModuleLeaderID, Image)
                            VALUES (:name, :desc, :leader, :img)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':name'   => $ModuleName,
                        ':desc'   => $Description === '' ? null : $Description,
                        ':leader' => ($ModuleLeaderID === '' ? null : (int)$ModuleLeaderID),
                        ':img'    => $ImagePath,
                    ]);
                }

                $success = 'Module <strong>' . htmlspecialchars($ModuleName) . '</strong> created successfully!';
                // reset form
                $ModuleName = $Description = $ModuleLeaderID = '';
                if ($hasProgramme) $ProgrammeID = '';
                $ImagePath = null;

            } catch (PDOException $e) {
                // If ModuleID isn't AUTO_INCREMENT, you’ll likely see SQLSTATE 23000 duplicate '0'
                if ($e->getCode() === '23000') {
                    $errors[] = "Insert failed due to a key constraint. If this says duplicate '0' for PRIMARY, set ModuleID to AUTO_INCREMENT:\n<pre>ALTER TABLE Modules\n  MODIFY ModuleID INT UNSIGNED NOT NULL AUTO_INCREMENT;</pre>";
                } else {
                    $errors[] = 'Failed to create module. Please try again.';
                }
                // error_log($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Module</title>
    <link rel="stylesheet" href="delete_programme.css">
</head>
<body>

<h2>Add Module</h2>
<a href="modules.php" class="back-btn">← Back</a>

<?php if (!empty($errors)): ?>
  <div class="notification error">
    <?php foreach ($errors as $e): ?>
      <div><?= nl2br(htmlspecialchars($e)) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="notification success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

    <label for="ModuleName">Module Name</label>
    <input id="ModuleName" name="ModuleName" type="text" value="<?= htmlspecialchars($ModuleName) ?>" required maxlength="255">

    <label for="Description">Description (optional)</label>
    <textarea id="Description" name="Description" rows="4"><?= htmlspecialchars($Description) ?></textarea>

    <?php if ($hasProgramme): ?>
      <label for="ProgrammeID">Programme</label>
      <select id="ProgrammeID" name="ProgrammeID" required>
          <option value="">-- Select programme --</option>
          <?php foreach ($programmes as $p): ?>
              <option value="<?= (int)$p['ProgrammeID'] ?>" <?= ((string)$ProgrammeID === (string)$p['ProgrammeID']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($p['ProgrammeName']) ?>
              </option>
          <?php endforeach; ?>
      </select>
    <?php endif; ?>

    <label for="ModuleLeaderID">Module Leader (optional)</label>
    <select id="ModuleLeaderID" name="ModuleLeaderID">
        <option value="">-- None --</option>
        <?php foreach ($staff as $s): ?>
            <option value="<?= (int)$s['StaffID'] ?>" <?= ((string)$ModuleLeaderID === (string)$s['StaffID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['Name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="Image">Image (optional)</label>
    <input id="Image" name="Image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif">

    <button type="submit">Create Module</button>
</form>

</body>
</html>

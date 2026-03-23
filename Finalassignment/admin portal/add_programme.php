<?php

include 'config.php';
include 'auth.php';

// ---------------- CSRF ----------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
function csrf_ok($t) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $t ?? '');
}

// ---------------- Fetch dropdown data ----------------
function fetchLevels(PDO $pdo): array {
    $stmt = $pdo->query("SELECT LevelID, LevelName FROM Levels ORDER BY LevelName");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function fetchStaff(PDO $pdo): array {
    $stmt = $pdo->query("SELECT StaffID, Name FROM Staff ORDER BY Name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$levels = fetchLevels($pdo);
$staff  = fetchStaff($pdo);

// ---------------- Defaults ----------------
$errors = [];
$successMessage = '';
$ProgrammeName = '';
$Description   = '';
$LevelID       = '';
$ProgrammeLeaderID = '';

// ---------------- Handle POST ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        // Read inputs
        $ProgrammeName = trim($_POST['ProgrammeName'] ?? '');
        $Description   = trim($_POST['Description'] ?? '');
        $LevelID       = $_POST['LevelID'] ?? '';
        $ProgrammeLeaderID = $_POST['ProgrammeLeaderID'] ?? '';

        // Validate inputs
        if ($ProgrammeName === '') {
            $errors[] = 'Programme name is required.';
        } elseif (mb_strlen($ProgrammeName) > 150) {
            $errors[] = 'Programme name must be at most 150 characters.';
        }

        if ($Description !== '' && mb_strlen($Description) > 1000) {
            $errors[] = 'Description must be at most 1000 characters.';
        }

        if ($LevelID === '' || !ctype_digit((string)$LevelID) || (int)$LevelID < 1) {
            $errors[] = 'Please choose a valid level.';
        }

        $leaderIdToUse = null;
        if ($ProgrammeLeaderID !== '') {
            if (!ctype_digit((string)$ProgrammeLeaderID) || (int)$ProgrammeLeaderID < 1) {
                $errors[] = 'Please choose a valid programme leader.';
            } else {
                $leaderIdToUse = (int)$ProgrammeLeaderID;
            }
        }

        // Insert if valid
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO Programmes (ProgrammeName, Description, LevelID, ProgrammeLeaderID)
                    VALUES (:name, :desc, :level, :leader)
                ");
                $stmt->execute([
                    ':name'   => $ProgrammeName,
                    ':desc'   => $Description === '' ? null : $Description,
                    ':level'  => (int)$LevelID,
                    ':leader' => $leaderIdToUse,
                ]);

                // Show success message and reset form values
                $successMessage = "Programme <strong>" . htmlspecialchars($ProgrammeName) . "</strong> added successfully!";
                $ProgrammeName = '';
                $Description = '';
                $LevelID = '';
                $ProgrammeLeaderID = '';

            } catch (PDOException $e) {
                $errors[] = 'Failed to create programme. Please try again.';
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
    <title>Add Programme</title>
    <link rel="stylesheet" href="add_programme.css">
</head>
<body>

<h2>Add Programme</h2>

<!-- Back button (same place and style as before) -->
<a href="programmes.php" class="back-btn">← Back</a>

<?php if (!empty($errors)): ?>
    <div class="notification error">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($successMessage): ?>
    <div class="notification success"><?= $successMessage ?></div>
<?php endif; ?>

<form method="POST" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

    <label for="ProgrammeName">Programme Name:</label>
    <input type="text" id="ProgrammeName" name="ProgrammeName"
           value="<?= htmlspecialchars($ProgrammeName) ?>" maxlength="150" required>

    <label for="Description">Description:</label>
    <textarea id="Description" name="Description" rows="4" maxlength="1000"
              placeholder="Optional"><?= htmlspecialchars($Description) ?></textarea>

    <label for="LevelID">Level:</label>
    <select id="LevelID" name="LevelID" required>
        <option value="">-- Select level --</option>
        <?php foreach ($levels as $l): ?>
            <option value="<?= (int)$l['LevelID'] ?>" <?= ((string)$LevelID === (string)$l['LevelID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['LevelName']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="ProgrammeLeaderID">Programme Leader (optional):</label>
    <select id="ProgrammeLeaderID" name="ProgrammeLeaderID">
        <option value="">-- None --</option>
        <?php foreach ($staff as $s): ?>
            <option value="<?= (int)$s['StaffID'] ?>" <?= ((string)$ProgrammeLeaderID === (string)$s['StaffID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['Name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Create Programme</button>
</form>

</body>
</html>

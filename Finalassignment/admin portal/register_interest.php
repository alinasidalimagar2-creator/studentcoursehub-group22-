<?php
// register_interest.php  (drop-in replacement)
// Show errors during setup (remove later)
ini_set('display_errors', '1'); ini_set('display_startup_errors', '1'); error_reporting(E_ALL);

require __DIR__ . '/includes/db.php';

// Preselect programme from ?programme_id=...
$selectedId = isset($_GET['programme_id']) ? (int)$_GET['programme_id'] : 0;

// Detect schema
$cols = $pdo->query("SHOW COLUMNS FROM InterestedStudents")->fetchAll(PDO::FETCH_COLUMN, 0);
$hasProgrammeID = in_array('ProgrammeID', $cols, true);
$hasCreatedAt   = in_array('CreatedAt',   $cols, true);

// Programmes list (only if ProgrammeID column exists)
$programmes = [];
if ($hasProgrammeID) {
  // Handle presence/absence of IsPublished gracefully
  $hasIsPublished = (bool)$pdo->query("
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='Programmes' AND COLUMN_NAME='IsPublished'
  ")->fetchColumn();

  $sql = "SELECT ProgrammeID, ProgrammeName FROM Programmes";
  if ($hasIsPublished) { $sql .= " WHERE IsPublished = 1"; }
  $sql .= " ORDER BY ProgrammeName";
  $programmes = $pdo->query($sql)->fetchAll();

  // If we came with ?programme_id=..., make sure it's selected
  if ($selectedId > 0) {
    $found = false;
    foreach ($programmes as $p) {
      if ((int)$p['ProgrammeID'] === $selectedId) { $found = true; break; }
    }
    // if not in the published list, fetch it directly and prepend
    if (!$found) {
      $st = $pdo->prepare("SELECT ProgrammeID, ProgrammeName FROM Programmes WHERE ProgrammeID=:id LIMIT 1");
      $st->execute([':id'=>$selectedId]);
      if ($row = $st->fetch()) array_unshift($programmes, $row);
    }
  }
}

$success = false; $error = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pid        = (int)($_POST['programme_id'] ?? 0);
  $first      = trim($_POST['first_name'] ?? '');
  $last       = trim($_POST['last_name'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $studentName= trim($first . ' ' . $last);

  // Prefer explicit posted pid; otherwise keep ?programme_id
  if ($hasProgrammeID) {
    $selectedId = $pid ?: $selectedId;
  }

  // Validate
  if (($hasProgrammeID && $selectedId <= 0)) {
    $error = 'Please select a programme.';
  } elseif ($first === '' || $email === '') {
    $error = 'First name and email are required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
  } else {
    // Build INSERT safely (no duplicate columns)
    $fields = []; $values = []; $params = [];

    if ($hasProgrammeID) { $fields[]='ProgrammeID'; $values[]=':pid'; $params[':pid']=$selectedId; }
    $fields[]='StudentName'; $values[]=':sname'; $params[':sname']=$studentName;
    // if your table also has FirstName / LastName, we can store them too (optional)
    if (in_array('FirstName', $cols, true)) { $fields[]='FirstName'; $values[]=':fn'; $params[':fn']=$first; }
    if (in_array('LastName',  $cols, true)) { $fields[]='LastName';  $values[]=':ln'; $params[':ln']=$last; }
    $fields[]='Email'; $values[]=':email'; $params[':email']=$email;
    if ($hasCreatedAt) { $fields[] = 'CreatedAt'; $values[] = 'NOW()'; }

    $sql = "INSERT INTO InterestedStudents (".implode(', ',$fields).") VALUES (".implode(', ',$values).")";

    try {
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $success = true;
    } catch (PDOException $e) {
      $error = 'Insert error: ' . htmlspecialchars($e->getMessage());
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register Interest</title>
  <link rel="stylesheet" href="/Project/student-course-hub/assests/css/style.css">
  <style>
    /* Small enhancements if your style.css doesn't have them */
    .wrap{max-width:760px;margin:1.5rem auto;padding:0 1rem}
    .panel{background:#fff; color:#111; border-radius:14px; padding:1rem; border:1px solid #e5e7eb}
    .panel h2{margin:.3rem 0 1rem}
    .row{display:grid; gap:.7rem; grid-template-columns:1fr 1fr}
    @media(max-width:720px){.row{grid-template-columns:1fr}}
    label{display:block; margin:.4rem 0 .2rem; font-weight:600}
    input,select{width:100%; padding:.7rem .8rem; border:1px solid #cfd6e4; border-radius:10px}
    .actions{display:flex; gap:.6rem; flex-wrap:wrap; margin-top:1rem}
    .btn{background:#0ea5e9; color:#fff; border:0; padding:.7rem 1rem; border-radius:10px; text-decoration:none; font-weight:800; display:inline-block}
    .btn.secondary{background:#64748b}
    .alert{padding:.7rem 1rem; border-radius:10px; margin:.6rem 0; font-weight:700}
    .alert.success{background:#ecfdf5; color:#065f46; border:1px solid #bbf7d0}
    .alert.error{background:#fff1f2; color:#9f1239; border:1px solid #fecdd3}
  </style>
</head>
<body>
<header class="site-header">
  <div class="container topbar">
    <a class="brand" href="index.php">Student Course Hub</a>
    <nav class="topnav">
      <a class="nav-item" href="programmes.php">Programmes</a>
      <a class="nav-item primary" href="login.php">Login</a>
    </nav>
    <button class="hamburger" onclick="document.querySelector('.topnav').classList.toggle('show')">☰</button>
  </div>
</header>

<main class="wrap">
  <div class="panel">
    <h2>Register your interest</h2>

    <?php if ($success): ?>
      <div class="alert success">✅ Submitted successfully!</div>
      <div class="actions">
        <a class="btn" href="programmes.php">← Back to Programmes</a>
        <a class="btn secondary" href="register_interest.php<?= $selectedId?('?programme_id='.(int)$selectedId):'' ?>">Register another</a>
      </div>

    <?php else: ?>
      <?php if ($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>

      <form method="post" action="">
        <?php if ($hasProgrammeID): ?>
          <?php if ($selectedId>0): ?>
            <input type="hidden" name="programme_id" value="<?= (int)$selectedId ?>">
            <p class="note">Registering interest for Programme ID: <strong><?= (int)$selectedId ?></strong></p>
          <?php else: ?>
            <label for="programme_id">Programme*</label>
            <select id="programme_id" name="programme_id" required>
              <option value="">Select a programme</option>
              <?php foreach ($programmes as $p): ?>
                <option value="<?= (int)$p['ProgrammeID'] ?>" <?= ((int)($_POST['programme_id'] ?? 0)===(int)$p['ProgrammeID']?'selected':'') ?>>
                  <?= htmlspecialchars($p['ProgrammeName']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        <?php endif; ?>

        <div class="row">
          <div>
            <label for="first_name">First name*</label>
            <input id="first_name" name="first_name" type="text" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
          </div>
          <div>
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
          </div>
        </div>

        <label for="email">Email*</label>
        <input id="email" name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <div class="actions">
          <button class="btn" type="submit">Submit</button>
          <a class="btn secondary" href="programmes.php">Back</a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</main>
</body>
</html>

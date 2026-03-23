<?php
// user/view_details.php


/* ---- Load DB config (admin portal) with safe fallbacks ---- */
$root = realpath(dirname(__DIR__)); // e.g., C:\xampp\htdocs\Finalassignment
$tried = [];
$loaded = false;
foreach ([
    $root . '/admin portal/config.php',   // your admin config
    $root . '/config.php',                // optional fallback
    __DIR__ . '/../config/db.php',        // legacy shim if you add it later
] as $path) {
    $tried[] = $path;
    if (is_file($path)) { require_once $path; $loaded = true; break; }
}
if (!$loaded) {
    echo "<pre>Database config not found. Tried:\n" . htmlspecialchars(implode("\n", $tried)) . "</pre>";
    exit;
}
if (!isset($pdo) || !($pdo instanceof PDO)) {
    exit('Database connection ($pdo) was not initialized by the loaded config.');
}

/* ---- Require login ---- */
if (empty($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* ---- CSRF token (same token used in dashboard for interest actions) ---- */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/* ---- Get programme id ---- */
$progId = $_GET['id'] ?? $_GET['programmeID'] ?? null;
if (!$progId || !is_numeric($progId)) {
    http_response_code(400);
    die("Invalid or missing programme ID.");
}
$progId = (int)$progId;

/* ---- Fetch programme ---- */
$progStmt = $pdo->prepare("
    SELECT 
        p.ProgrammeID, p.ProgrammeName, p.Description,
        l.LevelName,
        s.Name AS LeaderName
    FROM Programmes p
    LEFT JOIN Levels l ON p.LevelID = l.LevelID
    LEFT JOIN Staff  s ON p.ProgrammeLeaderID = s.StaffID
    WHERE p.ProgrammeID = ?
    LIMIT 1
");
$progStmt->execute([$progId]);
$programme = $progStmt->fetch(PDO::FETCH_ASSOC);
if (!$programme) {
    http_response_code(404);
    die("Programme not found.");
}

/* ---- Fetch modules ---- */
$modStmt = $pdo->prepare("
    SELECT m.ModuleID, m.ModuleName, m.Description
    FROM ProgrammeModules pm
    JOIN Modules m ON pm.ModuleID = m.ModuleID
    WHERE pm.ProgrammeID = ?
    ORDER BY m.ModuleName
");
$modStmt->execute([$progId]);
$modules = $modStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($programme['ProgrammeName']) ?> — Details</title>
  <link rel="stylesheet" href="view_details.css">
</head>
<body>

  <!-- Fancy Back Button (styled in your CSS) -->
  <a href="dashboard.php" class="back-btn" aria-label="Back to Dashboard">← Back</a>

  <div class="container details-container">
    <h2 class="page-title"><?= htmlspecialchars($programme['ProgrammeName']) ?></h2>

    <div class="card details-card">
      <p class="details-meta">
        <strong>Level:</strong> <?= htmlspecialchars($programme['LevelName'] ?? '—') ?>
        &nbsp; | &nbsp;
        <strong>Programme Leader:</strong> <?= htmlspecialchars($programme['LeaderName'] ?? '—') ?>
      </p>

      <h3 class="section-title">Description</h3>
      <p class="section-body">
        <?= nl2br(htmlspecialchars($programme['Description'] ?? 'No description provided.')) ?>
      </p>

      <h3 class="section-title">Modules</h3>
      <?php if (!empty($modules)): ?>
        <ul class="module-list">
          <?php foreach ($modules as $m): ?>
            <li class="module-item">
              <strong class="module-name"><?= htmlspecialchars($m['ModuleName']) ?></strong>
              <?php if (!empty($m['Description'])): ?>
                <span class="module-desc">— <?= htmlspecialchars($m['Description']) ?></span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="empty-state">No modules are currently listed for this programme.</p>
      <?php endif; ?>

      <div class="actions">
        <!-- Register Interest uses your dashboard's add action with CSRF -->
        <a class="btn btn-primary"
           href="dashboard.php?action=add&programme_id=<?= (int)$programme['ProgrammeID']; ?>&csrf=<?= urlencode($csrf) ?>">
          Register Interest
        </a>
        
      </div>
    </div>
  </div>

</body>
</html>

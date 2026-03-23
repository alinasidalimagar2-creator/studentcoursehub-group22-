<?php
// frontpage/view_details1.php

// --- Direct DB connection (same as your frontpage index.php) ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "student_course_hub";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Accept ?id= or ?programmeID=
$progId = $_GET['id'] ?? $_GET['programmeID'] ?? null;
if (!$progId || !is_numeric($progId)) {
    http_response_code(400);
    die("Invalid or missing programme ID.");
}
$progId = (int)$progId;

// Fetch programme details
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

// Fetch modules for this programme (if any)
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
  <!-- If you want styles, link your frontpage CSS file here -->
  <link rel="stylesheet" href="view_details.css">
 
  </style>
</head>
<body>
 <a href="home.php" class="back-btn" aria-label="Back to Programmes">← Back</a>

<div class="wrap">
  <h1 class="title"><?= htmlspecialchars($programme['ProgrammeName']) ?></h1>
  <div class="meta">
    <strong>Level:</strong> <?= htmlspecialchars($programme['LevelName'] ?? '—') ?>
    &nbsp; | &nbsp;
    <strong>Programme Leader:</strong> <?= htmlspecialchars($programme['LeaderName'] ?? '—') ?>
  </div>

  <div class="card">
    <h3 class="section-title">Description</h3>
    <p class="desc"><?= nl2br(htmlspecialchars($programme['Description'] ?? 'No description provided.')) ?></p>

    <h3 class="section-title" style="margin-top:16px;">Modules</h3>
    <?php if (!empty($modules)): ?>
      <ul class="module-list">
        <?php foreach ($modules as $m): ?>
          <li class="module-item">
            <div class="module-name"><?= htmlspecialchars($m['ModuleName']) ?></div>
            <?php if (!empty($m['Description'])): ?>
              <div class="module-desc"><?= htmlspecialchars($m['Description']) ?></div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">No modules are currently listed for this programme.</p>
    <?php endif; ?>

    <div class="actions">
      <a class="btn btn-primary" href="register_interest.php?programmeID=<?= (int)$programme['ProgrammeID'] ?>">Register Interest</a>
     
    </div>
  </div>
</div>

</body>
</html>

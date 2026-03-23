<?php
session_start();

/* ---------- TEMP DEBUG: set to false in production ---------- */
$DEBUG = true;
if ($DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

/* ---------- Load DB config from admin portal ---------- */
$root = realpath(dirname(__DIR__)); // e.g., C:\xampp\htdocs\Finalassignment
$configPath = $root . '/admin portal/config.php';
if (!is_file($configPath)) {
    echo "<pre style='color:#c00'>DB config not found at: " . htmlspecialchars($configPath) . "</pre>";
    exit;
}
require_once $configPath;
if (!isset($pdo) || !($pdo instanceof PDO)) {
    exit('Database connection ($pdo) was not initialized by the loaded config.');
}

/* ---------- CSRF for interest buttons ---------- */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];
$user = $_SESSION['user'] ?? null;

/* ---------- Validate programme id ---------- */
$id = $_GET['id'] ?? null;
$id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    echo "<p style='padding:1rem'>Invalid programme id. <a href=\"../user/dashboard.php\">Back</a></p>";
    exit;
}

/* ---------- Fetch programme (name, level, leader, description) ---------- */
try {
    $stmt = $pdo->prepare("
        SELECT 
            p.ProgrammeID,
            p.ProgrammeName,
            p.Description,
            l.LevelName,
            s.Name AS LeaderName
        FROM Programmes p
        LEFT JOIN Levels l ON p.LevelID = l.LevelID
        LEFT JOIN Staff  s ON p.ProgrammeLeaderID = s.StaffID
        WHERE p.ProgrammeID = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $programme = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$programme) {
        http_response_code(404);
        echo "<p style='padding:1rem'>Programme not found. <a href=\"../user/dashboard.php\">Back</a></p>";
        exit;
    }
} catch (PDOException $e) {
    if ($DEBUG) {
        echo "<pre style='color:#c00'>Programme query failed: " . htmlspecialchars($e->getMessage()) . "</pre>";
    } else {
        echo "<p style='padding:1rem'>We couldn’t load this programme right now.</p>";
    }
    exit;
}

/* ---------- Fetch modules for this programme (if any) ---------- */
$modules = [];
try {
    $modStmt = $pdo->prepare("
        SELECT 
            m.ModuleID,
            m.ModuleName,
            m.Description,
            st.Name AS ModuleLeader
        FROM ProgrammeModules pm
        JOIN Modules m ON pm.ModuleID = m.ModuleID
        LEFT JOIN Staff  st ON m.ModuleLeaderID = st.StaffID
        WHERE pm.ProgrammeID = ?
        ORDER BY m.ModuleName
    ");
    $modStmt->execute([$id]);
    $modules = $modStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the link table doesn’t exist or query fails, just show no modules
    if ($DEBUG) {
        echo "<pre style='color:#c77'>Modules query skipped/failed: " . htmlspecialchars($e->getMessage()) . "</pre>";
    }
    $modules = [];
}

/* ---------- Interest state (if logged in) ---------- */
$isInterested = false;
if ($user) {
    try {
        $chk = $pdo->prepare("SELECT 1 FROM interestedstudents WHERE ProgrammeID = ? AND Email = ? LIMIT 1");
        $chk->execute([$id, $user['email']]);
        $isInterested = (bool)$chk->fetchColumn();
    } catch (PDOException $e) {
        if ($DEBUG) {
            echo "<pre style='color:#c77'>Interest check failed: " . htmlspecialchars($e->getMessage()) . "</pre>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($programme['ProgrammeName']) ?> — Programme Details</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">

<header class="main-header">
  <div class="header-container">
    <div class="header-left">
      <a href="../user/dashboard.php" class="site-title">Student Course Hub</a>
    </div>
    <div class="header-right">
      <a href="../public/index.php" class="nav-link">Home</a>
      <?php if ($user): ?>
        <a href="../user/logout.php" class="nav-link btn-logout">Logout</a>
      <?php else: ?>
        <a href="../user/login.php" class="nav-link">Login</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="container">
  <div class="dashboard-header">
    <h2><?= htmlspecialchars($programme['ProgrammeName']) ?></h2>
    <p class="muted">
      <strong>Level:</strong> <?= htmlspecialchars($programme['LevelName'] ?? '—') ?>
      &nbsp; • &nbsp;
      <strong>Programme Leader:</strong> <?= htmlspecialchars($programme['LeaderName'] ?? '—') ?>
    </p>
  </div>

  <section class="card shadow-sm" style="padding:1rem; margin-bottom:1.5rem;">
    <h3>Description</h3>
    <p><?= nl2br(htmlspecialchars($programme['Description'] ?? 'No description available.')) ?></p>

    <?php if ($user): ?>
      <div style="margin-top:1rem;">
        <?php if ($isInterested): ?>
          <a class="btn btn-sm btn-danger"
             href="../user/dashboard.php?action=remove&programme_id=<?= (int)$programme['ProgrammeID'] ?>&csrf=<?= urlencode($csrf) ?>">
            Remove Interest
          </a>
        <?php else: ?>
          <a class="btn btn-sm btn-success"
             href="../user/dashboard.php?action=add&programme_id=<?= (int)$programme['ProgrammeID'] ?>&csrf=<?= urlencode($csrf) ?>">
            Register Interest
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>

  <section>
    <h3 class="mb-3">Modules in this Programme</h3>
    <?php if (!empty($modules)): ?>
      <div class="row g-3">
        <?php foreach ($modules as $m): ?>
          <div class="col-md-6">
            <div class="card shadow-sm h-100" style="padding:1rem;">
              <h4 class="card-title" style="margin:0 0 .5rem 0;"><?= htmlspecialchars($m['ModuleName']) ?></h4>
              <p class="muted" style="margin:.25rem 0;">
                <strong>Leader:</strong> <?= htmlspecialchars($m['ModuleLeader'] ?? '—') ?>
              </p>
              <p style="margin-top:.5rem;">
                <?= nl2br(htmlspecialchars($m['Description'] ?? '')) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-info">No modules are linked to this programme yet.</div>
    <?php endif; ?>
  </section>

  <div style="margin-top:2rem;">
    <a href="../user/dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
  </div>
</main>

<footer class="text-center mt-5 p-3 bg-primary text-white">
  &copy; <?= date('Y'); ?> Student Course Hub | All Rights Reserved
</footer>

</body>
</html>
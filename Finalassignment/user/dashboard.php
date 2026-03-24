<?php


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
    echo "<pre style='color:#c00'>Database config not found. Tried:\n" . htmlspecialchars(implode("\n", $tried)) . "</pre>";
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
$user = $_SESSION['user'];
$user_id = $user['id'];

/* ---- CSRF token (for interest actions) ---- */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/* ---- Handle interest actions (add/remove) ---- */
if (isset($_GET['action'], $_GET['programme_id'])) {
    $programme_id = (int)$_GET['programme_id'];
    $token = $_GET['csrf'] ?? '';

    if (!hash_equals($csrf, $token)) {
        // Invalid or missing CSRF token; ignore the action
        header("Location: dashboard.php");
        exit;
    }

    if ($_GET['action'] === 'add') {
        // NOTE: For ON DUPLICATE KEY to work, interestedstudents should have a UNIQUE key on (ProgrammeID, Email)
        $stmt = $pdo->prepare("
            INSERT INTO interestedstudents (ProgrammeID, StudentName, Email, RegisteredAt)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE RegisteredAt = VALUES(RegisteredAt)
        ");
        $stmt->execute([$programme_id, $user['name'], $user['email']]);
    } elseif ($_GET['action'] === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM interestedstudents WHERE ProgrammeID = ? AND Email = ?");
        $stmt->execute([$programme_id, $user['email']]);
    }

    header("Location: dashboard.php");
    exit;
}

/* ---- Filters ---- */
$selectedLevel = isset($_GET['level']) ? (int)$_GET['level'] : 0;

/* ---- Fetch levels ---- */
$levels = $pdo->query("SELECT * FROM levels ORDER BY LevelName")->fetchAll(PDO::FETCH_ASSOC);

/* ---- User's current interests ---- */
$interestStmt = $pdo->prepare("SELECT ProgrammeID FROM interestedstudents WHERE Email = ?");
$interestStmt->execute([$user['email']]);
$userInterests = $interestStmt->fetchAll(PDO::FETCH_COLUMN);

/* ---- Fetch programmes (optionally filtered) ---- */
try {
    if ($selectedLevel > 0) {
        $stmt = $pdo->prepare("
            SELECT p.*, l.LevelName
            FROM programmes p
            JOIN levels l ON p.LevelID = l.LevelID
            WHERE p.LevelID = ?
            ORDER BY p.ProgrammeName
        ");
        $stmt->execute([$selectedLevel]);
    } else {
        $stmt = $pdo->query("
            SELECT p.*, l.LevelName
            FROM programmes p
            JOIN levels l ON p.LevelID = l.LevelID
            ORDER BY p.ProgrammeName
        ");
    }
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="user_dashboard.css">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<header class="main-header">
  <div class="header-container">
    <div class="header-left">
      <a href="dashboard.php" class="site-title">Student Course Hub</a>
    </div>
    <div class="header-right">
      
      <a href="../frontpage/home.php" class="nav-link">Home</a>
<a href="my_interests.php" class="nav-link">My Interests</a>
<a href="logout.php" class="nav-link btn-logout">Logout</a>
  </div>
</header>

<div class="container">
  <div class="dashboard-header">
    <h2>Welcome, <?= htmlspecialchars($user['name']); ?> 👋</h2>
    <p>Explore available programmes and manage your academic interests.</p>
  </div>
</div>

<!-- FILTER SECTION -->
<div class="container my-4">
  <form method="GET" class="d-flex justify-content-end">
    <label for="level" class="me-2">Filter by Level:</label>
    <select name="level" id="level" onchange="this.form.submit()" class="form-select w-auto">
      <option value="0">All Levels</option>
      <?php foreach ($levels as $level): ?>
        <option value="<?= (int)$level['LevelID']; ?>" <?= ($selectedLevel == $level['LevelID']) ? 'selected' : ''; ?>>
          <?= htmlspecialchars($level['LevelName']); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<!-- MAIN CONTENT -->
<div class="container">
  <h2 class="mb-4 text-center">Available Programmes</h2>

  <?php if (!empty($programmes)): ?>
    <div class="row g-4">
      <?php foreach ($programmes as $p): ?>
        <div class="col-md-4">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($p['ProgrammeName']); ?></h5>
              <p><strong>Level:</strong> <?= htmlspecialchars($p['LevelName']); ?></p>
              <p>
                <?php
                  $desc = (string)($p['Description'] ?? '');
                  echo nl2br(htmlspecialchars(mb_substr($desc, 0, 180)));
                  if (mb_strlen($desc) > 180) echo '...';
                ?>
              </p>
<a href="view_details.php?id=<?= (int)$p['ProgrammeID']; ?>" class="btn btn-sm btn-primary">
  View Details
</a>

           

              <?php if (in_array($p['ProgrammeID'], $userInterests, true)): ?>
                <a href="?action=remove&programme_id=<?= (int)$p['ProgrammeID']; ?>&csrf=<?= urlencode($csrf) ?>"
                   class="btn btn-sm btn-danger ms-2">
                  Remove Interest
                </a>
              <?php else: ?>
                <a href="?action=add&programme_id=<?= (int)$p['ProgrammeID']; ?>&csrf=<?= urlencode($csrf) ?>"
                   class="btn btn-sm btn-success ms-2">
                  Register Interest
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">No programmes available for this level.</div>
  <?php endif; ?>
</div>

<!-- FOOTER -->
<footer class="text-center mt-5 p-3 bg-primary text-white">
  &copy; <?= date('Y'); ?> Student Course Hub | All Rights Reserved
</footer>

</body>
</html>

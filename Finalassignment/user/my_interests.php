
<?php
session_start();

/* ---- Load DB config ---- */
$root = realpath(dirname(__DIR__));
$configPath = $root . '/admin portal/config.php';
if (!is_file($configPath)) {
    die("Database config not found at: " . htmlspecialchars($configPath));
}
require_once $configPath;

if (!isset($pdo) || !($pdo instanceof PDO)) {
    exit('Database connection ($pdo) was not initialized.');
}

/* ---- Require login ---- */
if (empty($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];
$user_id = $user['id'];

/* ---- CSRF token ---- */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/* ---- Handle remove interest action ---- */
if (isset($_GET['action'], $_GET['programme_id'])) {
    $programme_id = (int)$_GET['programme_id'];
    $token = $_GET['csrf'] ?? '';

    if (hash_equals($csrf, $token)) {
        if ($_GET['action'] === 'remove') {
            $stmt = $pdo->prepare("DELETE FROM interestedstudents WHERE ProgrammeID = ? AND Email = ?");
            $stmt->execute([$programme_id, $user['email']]);
            header("Location: my_interests.php?removed=1");
            exit;
        }
    }
}

/* ---- Fetch user's interests ---- */
$interestStmt = $pdo->prepare("
    SELECT 
        i.ProgrammeID,
        i.RegisteredAt,
        p.ProgrammeName,
        p.Description,
        l.LevelName
    FROM interestedstudents i
    JOIN programmes p ON i.ProgrammeID = p.ProgrammeID
    LEFT JOIN levels l ON p.LevelID = l.LevelID
    WHERE i.Email = ?
    ORDER BY i.RegisteredAt DESC
");
$interestStmt->execute([$user['email']]);
$interests = $interestStmt->fetchAll(PDO::FETCH_ASSOC);

$success_msg = '';
if (isset($_GET['removed']) && $_GET['removed'] == '1') {
    $success_msg = 'Interest removed successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Interests - Student Course Hub</title>
  <link rel="stylesheet" href="user_dashboard.css">
  <style>
    .interest-card {
      border-left: 4px solid #0d6efd;
      transition: transform 0.2s;
    }
    .interest-card:hover {
      transform: translateX(5px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .registered-date {
      font-size: 0.85rem;
      color: #6c757d;
    }
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: #f8f9fa;
      border-radius: 8px;
      margin-top: 30px;
    }
    .empty-state h3 {
      color: #6c757d;
      margin-bottom: 10px;
    }
    .success-alert {
      background: #d4edda;
      color: #155724;
      padding: 12px 20px;
      border-radius: 5px;
      margin-bottom: 20px;
      border: 1px solid #c3e6cb;
    }
  </style>
</head>
<body class="bg-light">

<!-- NAVBAR -->
<header class="main-header">
  <div class="header-container">
    <div class="header-left">
      <a href="dashboard.php" class="site-title">Student Course Hub</a>
    </div>
    <div class="header-right">
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="my_interests.php" class="nav-link">My Interests</a>
      <a href="../frontpage/home.php" class="nav-link">Home</a>
      <a href="logout.php" class="nav-link btn-logout">Logout</a>
    </div>
  </div>
</header>

<div class="container">
  <div class="dashboard-header">
    <h2>My Registered Interests 👨‍🎓</h2>
    <p>View and manage all programmes you've shown interest in.</p>
  </div>

  <?php if ($success_msg): ?>
    <div class="success-alert"><?= htmlspecialchars($success_msg) ?></div>
  <?php endif; ?>

  <?php if (!empty($interests)): ?>
    <div class="row g-4 mt-3">
      <?php foreach ($interests as $interest): ?>
        <div class="col-md-6">
          <div class="card shadow-sm h-100 interest-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <h5 class="card-title mb-2"><?= htmlspecialchars($interest['ProgrammeName']); ?></h5>
                <span class="badge bg-primary"><?= htmlspecialchars($interest['LevelName'] ?? 'N/A'); ?></span>
              </div>
              
              <p class="card-text">
                <?php
                  $desc = (string)($interest['Description'] ?? '');
                  echo nl2br(htmlspecialchars(mb_substr($desc, 0, 150)));
                  if (mb_strlen($desc) > 150) echo '...';
                ?>
              </p>
              
              <p class="registered-date">
                <strong>📅 Registered:</strong> 
                <?= date('F j, Y \a\t g:i A', strtotime($interest['RegisteredAt'])); ?>
              </p>

              <div class="d-flex gap-2 mt-3">
                <a href="view_details.php?id=<?= (int)$interest['ProgrammeID']; ?>" 
                   class="btn btn-sm btn-primary">
                  View Details
                </a>
                <a href="?action=remove&programme_id=<?= (int)$interest['ProgrammeID']; ?>&csrf=<?= urlencode($csrf) ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Are you sure you want to remove your interest in this programme?');">
                  Remove Interest
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <div class="mt-4">
      <a href="dashboard.php" class="btn btn-success">
        ← Browse More Programmes
      </a>
    </div>
    
  <?php else: ?>
    <div class="empty-state">
      <h3> No Interests Yet</h3>
      <p>You haven't registered interest in any programmes yet.</p>
      <p class="text-muted">Browse available programmes and click "Register Interest" to add them here.</p>
      <a href="dashboard.php" class="btn btn-primary mt-3">
        Browse Programmes
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- FOOTER -->
<footer class="text-center mt-5 p-3 bg-primary text-white">
  &copy; <?= date('Y'); ?> Student Course Hub | All Rights Reserved
</footer>

</body>
</html>

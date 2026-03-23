<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "student_course_hub";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


/* Fetch all programmes with level names */
try {
    $stmt = $pdo->query("
        SELECT p.ProgrammeID,
               p.ProgrammeName,
               p.Description,
               l.LevelName
        FROM Programmes p
        LEFT JOIN Levels l ON p.LevelID = l.LevelID
        ORDER BY p.ProgrammeName
    ");
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Query error: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Course Hub</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="main-nav">
  <div class="logo">
  <a href="home.php">Home</a>
</div>

  <div class="dropdown" id="loginMenu">
    <button class="dropbtn" id="loginToggle">Login ▾</button>
    <div class="dropdown-content" id="loginDropdown">
      <a href="/Finalassignment/admin portal/adminlogin.php">Admin Login</a>
      <a href="/Finalassignment/user/login.php">User Login</a>
      <a href="/Finalassignment/user/signup.php">New User? Signup</a>
    </div>
  </div>
</nav>

<main class="container">
  <h1 class="title">Available Programmes</h1>

  <!-- Filters (client-side) -->
  <div class="filters">
    <select id="levelFilter">
      <option value="all">All Levels</option>
      <option value="Undergraduate">Undergraduate</option>
      <option value="Postgraduate">Postgraduate</option>
    </select>
    <input type="text" id="searchBar" placeholder="Search by keyword...">
  </div>

  <!-- Programme list (server-rendered) -->
  <div id="programmeContainer" class="programme-list">
    <?php if (!empty($programmes)): ?>
      <?php foreach ($programmes as $p): ?>
        <div class="programme-card"
             data-level="<?= htmlspecialchars($p['LevelName'] ?? ''); ?>"
             data-text="<?= htmlspecialchars(($p['ProgrammeName'] ?? '') . ' ' . ($p['Description'] ?? '')); ?>">

          <h3><?= htmlspecialchars($p['ProgrammeName']); ?></h3>
          <?php if (!empty($p['LevelName'])): ?>
            <p class="muted"><strong>Level:</strong> <?= htmlspecialchars($p['LevelName']); ?></p>
          <?php endif; ?>
          <p><?= nl2br(htmlspecialchars($p['Description'] ?? '')); ?></p>

          <div class="actions">
            <a class="btn view-btn" href="view_details.php?id=<?= (int)$p['ProgrammeID']; ?>">
  View Details
</a>
            <!-- Register Interest goes to frontpage/register_interest.php with ProgrammeID -->
            <a class="btn btn-outline" href="register_interest.php?programmeID=<?= (int)$p['ProgrammeID']; ?>">
  Register Interest
</a>

            </a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No programmes found.</p>
    <?php endif; ?>
  </div>
</main>

<script>
/* Login dropdown toggle */
document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("loginToggle");
  const dropdown = document.getElementById("loginDropdown");

  toggle?.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
  });
  document.addEventListener("click", () => { if (dropdown) dropdown.style.display = "none"; });

  /* Client-side filters (works on server-rendered cards) */
  const levelFilter = document.getElementById('levelFilter');
  const searchBar   = document.getElementById('searchBar');
  const cards       = Array.from(document.querySelectorAll('.programme-card'));

  function applyFilters() {
    const level = (levelFilter?.value || 'all').toLowerCase();
    const term  = (searchBar?.value || '').toLowerCase();

    cards.forEach(card => {
      const cardLevel = (card.getAttribute('data-level') || '').toLowerCase();
      const text      = (card.getAttribute('data-text')  || '').toLowerCase();

      const levelOk = (level === 'all') || (cardLevel === level);
      const textOk  = term === '' || text.includes(term);

      card.style.display = (levelOk && textOk) ? '' : 'none';
    });
  }

  levelFilter?.addEventListener('change', applyFilters);
  searchBar?.addEventListener('input', applyFilters);
});
</script>

</body>
</html>
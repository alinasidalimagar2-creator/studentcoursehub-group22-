<?php include 'config.php'; include 'auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body class="dashboard-body">

  <!-- HEADER -->
  <header class="dashboard-header">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></h1>
  </header>

  <!-- SECOND LINE: BACK + LOGOUT -->
  <div class="action-bar">
    <a href="adminlogin.php" class="back-btn">Back to Login</a>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>

  <!-- MAIN DASHBOARD -->
  <div class="dashboard-container">
    <?php if (isset($_SESSION['success'])): ?>
      <p class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <!-- THIRD LINE: NAVIGATION -->
    <div class="dashboard-nav">
      <a href="programmes.php">Manage Programmes</a>
      <a href="modules.php">Manage Modules</a>
      <a href="students.php">Interested Students</a>
       <a href="staff.php">Manage Staffs</a>
         <a href="manage_user.php">Manage Users</a>
      
    </div>

    <p class="dashboard-text">Select an option from above to manage data.</p>
  </div>

</body>
</html>

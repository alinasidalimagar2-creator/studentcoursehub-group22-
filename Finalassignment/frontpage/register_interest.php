<?php
session_start();

/* Direct DB connection (same as index.php) */
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

$programmeID   = $_GET['programmeID'] ?? null;
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

/* Handle form submission (PRG: set flash + redirect) */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $programmeID = $_POST['programme_id'] ?? null;
    $studentName = trim($_POST['student_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');

    if (!$programmeID || $studentName === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = "Please provide a valid name, email, and programme.";
        header("Location: register_interest.php?programmeID=" . urlencode((string)$programmeID));
        exit;
    }

    try {
        // Ensure programme exists
        $chk = $pdo->prepare("SELECT 1 FROM Programmes WHERE ProgrammeID = ?");
        $chk->execute([$programmeID]);
        if (!$chk->fetchColumn()) {
            $_SESSION['flash_error'] = "Selected programme was not found.";
            header("Location: register_interest.php");
            exit;
        }

        // Check if already registered (case-insensitive email)
        $exists = $pdo->prepare("
            SELECT 1
            FROM InterestedStudents
            WHERE ProgrammeID = ? AND LOWER(Email) = LOWER(?)
            LIMIT 1
        ");
        $exists->execute([$programmeID, $email]);

        if ($exists->fetchColumn()) {
            // Update timestamp (and name in case it changed)
            $upd = $pdo->prepare("
                UPDATE InterestedStudents
                SET StudentName = ?, RegisteredAt = NOW()
                WHERE ProgrammeID = ? AND LOWER(Email) = LOWER(?)
            ");
            $upd->execute([$studentName, $programmeID, $email]);
            $_SESSION['flash_success'] = "You're already on the list — we've refreshed your registration time ✅";
        } else {
            // Insert new interest
            $ins = $pdo->prepare("
                INSERT INTO InterestedStudents (ProgrammeID, StudentName, Email, RegisteredAt)
                VALUES (?, ?, ?, NOW())
            ");
            $ins->execute([$programmeID, $studentName, $email]);
            $_SESSION['flash_success'] = "Thank you for registering your interest! We'll email you more details soon ✅";
        }

        header("Location: register_interest.php?programmeID=" . urlencode((string)$programmeID));
        exit;
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Database error: " . htmlspecialchars($e->getMessage());
        header("Location: register_interest.php?programmeID=" . urlencode((string)$programmeID));
        exit;
    }
}

/* Fetch programme name to show in form */
$programmeName = null;
if ($programmeID) {
    $stmt = $pdo->prepare("SELECT ProgrammeName FROM Programmes WHERE ProgrammeID = :id");
    $stmt->execute([':id' => $programmeID]);
    $programmeName = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Interest</title>
   <link rel="stylesheet" href="register_intrest.css">
</head>
<body>
   <a href="index.php" class="back-btn" aria-label="Back to Programmes">← Back</a>
  <div class="login-box">
    <h2>Register Interest</h2>

    <?php if ($programmeName): ?>
      <p class="subtext">Programme: <strong><?= htmlspecialchars($programmeName) ?></strong></p>
    <?php endif; ?>

    <?php if (!empty($flash_success)): ?>
      <div class="success"><?= $flash_success ?></div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
      <div class="error"><?= $flash_error ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="programme_id" value="<?= htmlspecialchars($programmeID ?? '') ?>">

      <label for="student_name">Full Name</label>
      <input id="student_name" type="text" name="student_name" placeholder="Your full name" required>

      <label for="email">Email Address</label>
      <input id="email" type="email" name="email" placeholder="you@example.com" required>

      <button class="btn" type="submit">Submit</button>      
    </form>
    <a href="index.php" >Back to Programmes</a>
  </div>
</body>
</html>

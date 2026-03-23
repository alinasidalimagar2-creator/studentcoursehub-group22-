<?php


/* Load admin portal DB config */
$root = realpath(dirname(__DIR__)); // C:\xampp\htdocs\Finalassignment
$configPath = $root . '/admin portal/config.php';
if (!is_file($configPath)) {
    echo "<pre style='color:#c00'>Database config not found at:\n" . htmlspecialchars($configPath) . "</pre>";
    exit;
}
require_once $configPath;

if (!isset($pdo) || !($pdo instanceof PDO)) {
    exit('Database connection ($pdo) was not initialized by the loaded config.');
}

$error = '';
$success = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if ($name === '') {
        $error = 'Name is required.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'A valid email is required.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    }

    if ($error === '') {
        try {
            // Case-insensitive duplicate check
            $check = $pdo->prepare("SELECT 1 FROM `users` WHERE LOWER(`email`) = LOWER(?) LIMIT 1");
            $check->execute([$email]);

            if ($check->fetchColumn()) {
                $error = "That email is already registered. Please log in instead.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password`) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hash]);

                $success = "Account created successfully! <a href='login.php'>Login here</a>";
                // Reset after success
                $name = '';
                $email = '';
            }
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) == 1062) {
                $error = "This email is already in use.";
            } else {
                $error = "Database error: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Sign-Up</title>
<link rel="stylesheet" href="signup.css">
</head>
<body>
<div class="container login-box">
  <h2>User Sign-Up</h2>

  <?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <p class="success"><?= $success ?></p>
  <?php endif; ?>

  <form method="POST" novalidate>
    <label>Name:</label>
    <input type="text" name="name" required value="<?= htmlspecialchars($name) ?>">

    <label>Email:</label>
    <input type="email" name="email" required placeholder="example@gmail.com" value="<?= htmlspecialchars($email) ?>">

    <label>Password:</label>
    <input type="password" name="password" required minlength="8">

    <input type="submit" value="Sign Up" class="btn">
    <p>
  Already have an account?
  <a href="login.php" class="btn" style="display:inline-block; width:auto; padding:10px 14px; margin-left:6px;">Login</a>
</p>

  </form>
</div>
</body>
</html>

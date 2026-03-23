<?php

/* Load admin portal DB config */
$root = realpath(dirname(__DIR__)); // e.g., C:\xampp\htdocs\Finalassignment
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
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif ($password === '') {
        $error = 'Please enter your password.';
    } else {
        try {
            // Query from singular table `user` (backticked because "user" is reserved)
            $stmt = $pdo->prepare(
                "SELECT `id`, `name`, `email`, `password`
                 FROM `users`
                 WHERE LOWER(`email`) = LOWER(?)
                 LIMIT 1"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email'],
                ];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
    echo "<pre>";
    echo $e->getMessage();
    echo "</pre>";
    exit;
}
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Login</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
 

  <div class="container login-box">
    <h2>User Login</h2>

    <?php if (!empty($error)): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" novalidate>
      <label>Email:</label>
      <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>">

      <label>Password:</label>
      <input type="password" name="password" required>

      <input type="submit" value="Login" class="btn">
      <p>Don’t have an account? <a href="signup.php">Sign up</a></p>
    </form>
  </div>
</body>
</html>
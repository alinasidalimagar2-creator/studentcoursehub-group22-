<?php include 'config.php'; ?>

<?php
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Simple hardcoded credentials for now
    if ($username === 'admin' && $password === 'password123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = 'Administrator';
        $_SESSION['success'] = "Login successful!";
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="adminlogin.css">
</head>
<body class="login-body">
<div class="login-box">
    <div class="logo-section">
        <img src="https://www.nielsbrock.dk/media/nwxhqsmn/nb_logo_dk_centered_cmyk.png" alt="Logo">
    </div>
    <div class="form-section">
        <h2>Admin Login</h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required><br><br>
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required><br><br>
            <button type="submit">Login</button>
        </form>
    </div>
</div>
</body>
</html>

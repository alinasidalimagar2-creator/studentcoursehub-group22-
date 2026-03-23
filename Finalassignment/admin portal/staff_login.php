<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === 'staff@test.com' && $password === 'password123') {
        $_SESSION['staff_logged_in'] = true;
        $_SESSION['staff_name'] = 'Staff Member';
        $_SESSION['staff_email'] = 'staff@test.com';
        
        header("Location: staff_dashboard.php");
        exit;
    } else {
        $error = "Invalid staff credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #ff8c00, #4169e1);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-box {
            background: rgba(139, 69, 19, 0.7);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }
        .login-box h2 {
            text-align: center;
            color: #5d2e2e;
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: 700;
        }
        .error {
            background: #ff6b6b;
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        label {
            display: block;
            color: #d4ed79;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
            font-size: 1rem;
        }
        input::placeholder { color: rgba(255, 255, 255, 0.7); }
        input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 3px rgba(212, 237, 121, 0.3);
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #ff1493;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover { color: #ff69b4; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2> Staff Login</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="staff@test.com" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="back-link">
            <a href="../frontpage/home.php">← Back to Home</a>
        </div>
    </div>
</body>
</html>

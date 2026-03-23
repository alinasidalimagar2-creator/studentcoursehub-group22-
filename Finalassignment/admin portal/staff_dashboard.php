<?php

include 'config.php';

// Check if staff is logged in
if (empty($_SESSION['staff_logged_in'])) {
    header("Location: staff_login.php");
    exit;
}

$message = '';

// ✅ HANDLE: Add new student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if ($name && $email && $password) {
        try {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->fetchColumn()) {
                $message = "⚠️ Email already registered!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
                $stmt->execute([$name, $email, $hash]);
                $message = "✅ Student added successfully!";
            }
        } catch (PDOException $e) {
            $message = "❌ Error: " . $e->getMessage();
        }
    } else {
        $message = "❌ Please fill all fields!";
    }
}

// ✅ FETCH: All students ORDERED BY ID
$students = [];
try {
    $stmt = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'student' ORDER BY id ASC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $students = [];
}

// ✅ FETCH: All modules ORDERED BY ModuleID
$modules = [];
try {
    $stmt = $pdo->query("SELECT ModuleID, ModuleName FROM Modules ORDER BY ModuleID ASC");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $modules = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #ff8c00 0%, #4169e1 100%);
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            background: rgba(139, 69, 19, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dashboard-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            color: #f9f6f6;
            font-size: 2.2rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .dashboard-header p {
            color: #d4ed79;
            margin-top: 10px;
            font-size: 1.1rem;
        }

        .dashboard-header a {
            color: #ff6b6b;
            text-decoration: none;
            font-weight: 700;
            margin-left: 10px;
        }

        .dashboard-header a:hover {
            color: #ff5252;
            text-decoration: underline;
        }

        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .message.success {
            background: #2ecc71;
            color: white;
        }

        .message.error {
            background: #e74c3c;
            color: white;
        }

        .section {
            margin-bottom: 35px;
            padding: 25px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            border-left: 5px solid #d4ed79;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .section h3 {
            color: #f9f6f6;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .form-row {
            margin-bottom: 18px;
        }

        .form-row label {
            display: block;
            font-weight: 600;
            color: #d4ed79;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-row input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-row input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-row input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.4);
            border-color: #d4ed79;
            box-shadow: 0 0 0 4px rgba(212, 237, 121, 0.25);
        }

        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        table th {
            background: rgba(93, 46, 46, 0.95);
            color: #d4ed79;
            padding: 16px 18px;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            border-bottom: 3px solid rgba(255, 255, 255, 0.3);
        }

        table td {
            padding: 14px 18px;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 0.95rem;
        }

        table tbody tr {
            transition: all 0.2s ease;
        }

        table tbody tr:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.01);
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        .note {
            font-size: 0.9rem;
            color: #d4ed79;
            font-style: italic;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .nav-links {
            text-align: center;
            padding-top: 25px;
            border-top: 3px solid rgba(255, 255, 255, 0.3);
            margin-top: 25px;
        }

        .nav-links a {
            display: inline-block;
            margin: 8px 15px;
            padding: 13px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .nav-links a:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .nav-links .logout {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 20px;
                margin: 15px auto;
            }
            
            .dashboard-header h1 {
                font-size: 1.6rem;
            }
            
            .section {
                padding: 15px;
            }
            
            table th, table td {
                padding: 10px 12px;
                font-size: 0.85rem;
            }
            
            .nav-links a {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    
    <!-- Header -->
    <div class="dashboard-header">
        <h1> Staff Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['staff_name']) ?> 
        <a href="staff_logout.php">🚪 Logout</a></p>
    </div>
    
    <!-- Message -->
    <?php if (!empty($message)): ?>
        <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <!-- Add Student Section -->
    <div class="section">
        <h3>➕ Add New Student</h3>
        <p class="note">Fill in details to register a new student</p>
        <form method="POST">
            <div class="form-row">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter student name" required>
            </div>
            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email" placeholder="student@example.com" required>
            </div>
            <div class="form-row">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create password" required>
            </div>
            <button type="submit" name="add_student" class="btn-add">Add Student</button>
        </form>
    </div>
    
    <!-- Students List -->
    <div class="section">
        <h3>🎓 Registered Students (<?= count($students) ?>)</h3>
        <p class="note">🔒 View only - Sorted by ID</p>
        
        <?php if (!empty($students)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td><strong><?= (int)$s['id'] ?></strong></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#d4ed79;text-align:center;padding:20px;font-size:1.1rem;">No students registered yet.</p>
        <?php endif; ?>
    </div>
    
    <!-- Modules List -->
    <div class="section">
        <h3>📚 Available Modules (<?= count($modules) ?>)</h3>
        <p class="note">🔒 View only - Sorted by Module ID</p>
        
        <?php if (!empty($modules)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Module ID</th>
                        <th>Module Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $m): ?>
                    <tr>
                        <td><strong><?= (int)$m['ModuleID'] ?></strong></td>
                        <td><?= htmlspecialchars($m['ModuleName']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#d4ed79;text-align:center;padding:20px;font-size:1.1rem;">No modules found.</p>
        <?php endif; ?>
    </div>
    
    <!-- Navigation -->
    <div class="nav-links">
        <a href="../frontpage/home.php"> Home</a>
        <a href="staff_logout.php" class="logout"> Logout</a>
    </div>
    
</div>

</body>
</html>
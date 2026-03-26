<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "student_course_hub";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verify users table exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $userCount = 0;
}

// Flash messages
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Course Hub — Home</title>
    
    <!-- INTERNAL CSS (All styles in one file) -->
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #182848 0%, #4b6cb7 100%);
            color: #f0f0f0;
            min-height: 100vh;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }

        /* ========================================
           HEADER / NAVIGATION BAR
           ======================================== */
        header {
            background-color: #1e1b7e;
            color: #d2e1ed;
            padding: 0 40px;
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
        }

        .logo:hover {
            color: #7fdfff;
        }

        nav {
            display: flex;
            align-items: center;
        }

        nav ul {
            list-style-type: none;
            display: flex;
            align-items: center;
            gap: 25px;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            color: #cfd6f4;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 6px;
            display: block;
        }

        nav ul li a:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        nav ul li a.active {
            color: #fff;
            background: rgba(52, 152, 219, 0.5);
        }

        /* Admin button style */
        nav ul li a.btn-admin {
            background: #3498db;
            color: #fff;
            padding: 10px 20px;
        }

        nav ul li a.btn-admin:hover {
            background: #2980b9;
        }

        /* ========================================
           MAIN CONTENT
           ======================================== */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Flash Messages */
        .alert {
            max-width: 600px;
            margin: 20px auto;
            padding: 15px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
        }

        .alert-success {
            background: #2ecc71;
            color: #fff;
        }

        .alert-error {
            background: #e74c3c;
            color: #fff;
        }

        /* ========================================
           HERO / WELCOME SECTION
           ======================================== */
        .hero {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            margin: 40px 0;
            backdrop-filter: blur(12px);
        }

        .hero h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            color: #fff;
            margin-bottom: 20px;
            font-weight: 800;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            color: #d7e1ff;
            font-size: 1.15rem;
            line-height: 1.8;
            max-width: 800px;
            margin: 0 auto 35px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #3498db;
            color: #fff;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #fff;
            transform: translateY(-2px);
        }

        /* Stats Section */
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .stat {
            text-align: center;
            padding: 20px 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            min-width: 140px;
        }

        .stat-number {
            display: block;
            font-size: 2.2rem;
            font-weight: 800;
            color: #7fdfff;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #d7e1ff;
            font-size: 0.9rem;
        }

        /* ========================================
           FEATURES SECTION
           ======================================== */
        .features {
            padding: 60px 20px;
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 40px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
        }

        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            color: #fff;
            font-size: 1.3rem;
            margin-bottom: 12px;
        }

        .feature-card p {
            color: #d7e1ff;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* ========================================
           CALL TO ACTION
           ======================================== */
        .cta {
            background: rgba(52, 152, 219, 0.2);
            padding: 70px 20px;
            text-align: center;
            margin: 40px 0;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .cta h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 15px;
        }

        .cta p {
            color: #d7e1ff;
            font-size: 1.1rem;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-success {
            background: #2ecc71;
            color: #fff;
        }

        .btn-success:hover {
            background: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(46, 204, 113, 0.4);
        }

        /* ========================================
           FOOTER
           ======================================== */
        footer {
            background: rgba(0, 0, 0, 0.4);
            padding: 40px 20px;
            text-align: center;
            color: #d7e1ff;
            margin-top: 60px;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #7fdfff;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* ========================================
           RESPONSIVE DESIGN
           ======================================== */
        @media (max-width: 768px) {
            header {
                padding: 0 20px;
                height: 60px;
            }

            nav ul {
                display: none; 
            }

            .hero {
                padding: 40px 20px;
                margin: 20px 0;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }

            .hero-stats {
                gap: 15px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Header with Navigation (Top Right) -->
    <header>
        <a href="home.php" class="logo">🎓 Student Course Hub</a>
        
        <nav>
            <ul>
                <li><a href="home.php" class="active">Home</a></li>
                <li><a href="index.php">Programmes</a></li>
                <li><a href="/Finalassignment/user/login.php">User Login</a></li>
                <li><a href="/Finalassignment/user/signup.php">Sign Up</a></li>
                <li><a href="/Finalassignment/admin portal/adminlogin.php" >Admin Login</a></li>
                <li><a href="/Finalassignment/admin portal/staff_login.php">Staff Login</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Flash Messages -->
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
        <?php endif; ?>
        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($flash_error) ?></div>
        <?php endif; ?>

        <!-- Hero / Welcome Section -->
        <section class="hero">
            <h1>Welcome to the Student Course Hub!</h1>
            <p>
                Discover your future with our world-class undergraduate and postgraduate programmes. 
                From Computer Science to Cyber Security, Artificial Intelligence to Data Science — 
                find the perfect course that matches your ambitions.
            </p>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-number">10+</span>
                    <span class="stat-label">Programmes</span>
                </div>
                <div class="stat">
                    <span class="stat-number">30+</span>
                    <span class="stat-label">Modules</span>
                </div>
                <div class="stat">
                    <span class="stat-number">20+</span>
                    <span class="stat-label">Expert Staff</span>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <h2 class="section-title">Why Choose Us?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📚</div>
                    <h3>Wide Range of Courses</h3>
                    <p>Explore diverse programmes in Computer Science, AI, Cyber Security, Data Science, and Software Engineering.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👨‍🏫</div>
                    <h3>Expert Faculty</h3>
                    <p>Learn from experienced academics and industry professionals dedicated to your success.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💼</div>
                    <h3>Career Focused</h3>
                    <p>Our programmes are designed with industry needs in mind to boost your employability.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌐</div>
                    <h3>Flexible Learning</h3>
                    <p>Study at your own pace with our supportive learning environment and resources.</p>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="cta">
            <h2>Ready to Start Your Journey?</h2>
            <p>Register your interest today and receive updates about open days, application deadlines, and more.</p>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?= date('Y') ?> Student Course Hub. All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Accessibility</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Contact Us</a>
        </div>
    </footer>

</body>
</html>

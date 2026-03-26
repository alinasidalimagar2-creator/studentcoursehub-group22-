🎓 Student Course Hub Management System

A comprehensive, multi-role web application built with PHP and MySQL designed to manage educational academic structures. This system provides specialized portals for Administrators, Staff members, and prospective Students to interact with programmes, modules, and faculty data.

🚀 Key Features

🛡️ Administrative Control (Admin Portal)
Complete Academic Management: Full CRUD (Create, Read, Update, Delete) capabilities for academic Programmes and course Modules.

Faculty Directory: Manage the Staff database, including profile details and photo uploads.

User Governance: Specialized tools to manage system users and maintain access control.

Centralized Dashboard: A secure landing page for admins to navigate all management functions.

👨‍🏫 Staff Specialized Access (Staff Portal)


Dedicated Staff Dashboard: A customized environment for faculty to view current academic data.

Student Enrollment: Tools for staff to manually add students and manage their registration records.

Academic Monitoring: Read-only access to the list of available modules.

👨‍🎓 Student Engagement


Interest Registration: A public-facing portal where prospective students can register their interest in specific programmes.

Record Tracking: Integrated view for admins to monitor all student registrations and their selected programmes.

🔒 Security & Technical Implementation


Role-Based Access Control (RBAC): Separate login systems for Administrators and Staff with independent session management.

Data Integrity: Built using PDO Prepared Statements across all files to protect against SQL Injection.

CSRF Protection: All forms utilize session-based tokens to prevent Cross-Site Request Forgery attacks.

Modular Architecture: Uses a centralized config.php for database connections and auth.php for security gating.

🛠️ Technology Stack


Backend: PHP 

Database: MySQL

Frontend: HTML, CSS (Modular stylesheets for each functional area)

📂 File Structure Highlights


Adminlogin.php / staff_login.php: Dedicated secure entry points.

dashboard.php / staff_dashboard.php: Primary navigation hubs.

register_interest.php: Public student entry form.

config.php: Centralized system configuration and PDO initialization.

auth.php: Authentication guard for administrative routes.

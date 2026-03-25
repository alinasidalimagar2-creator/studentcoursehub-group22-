# Student Course Hub – Final Assignment

## Project Overview

Student Course Hub is a dynamic web application developed using PHP, MySQL, HTML, and CSS.
The system is designed to help students explore academic programmes and manage their interests.

Users can create an account, log in securely, browse available programmes, filter them by level, view detailed information, and register or remove their interest in specific programmes.

This project demonstrates full-stack web development concepts including database integration, authentication, and user interaction.



## Features

* User registration (Sign Up)
* Secure user login and logout
* Session-based authentication
* View all available programmes
* Filter programmes by level
* View detailed programme information
* Register interest in programmes
* Remove registered interest
* View all selected interests in one place
* Responsive and styled user interface



## Technologies Used

* PHP (Server-side scripting)
* MySQL (Database management)
* HTML (Structure)
* CSS (Styling)
* XAMPP (Local server environment)
* Visual Studio Code (Code editor)
* GitHub (Version control)



## Project Folder Structure


Finalassignment
│
├── user
│   ├── dashboard.php              # Main dashboard (view & manage programmes)
│   ├── login.php                  # Login system
│   ├── logout.php                 # Session destroy/logout
│   ├── signup.php                 # User registration
│   ├── my_interests.php           # Displays selected programmes
│   ├── view_details.php           # Programme details page
│   │
│   ├── login.css                  # Login page styling
│   ├── signup.css                 # Signup page styling
│   └── user_dashboard.css         # Dashboard styling
│
├── example-data.sql               # Database structure and sample data
├── README.md                      # Project documentation



## User Folder Explanation

The `user` folder contains all functionality related to student interaction.

1. dashboard.php
  Displays all available programmes and allows users to register or remove interest.

2. login.php
  Handles user authentication using email and password.

3. signup.php
  Allows new users to create an account with validation.

4. logout.php
  Ends the user session securely.

5. my_interests.php
  Displays all programmes the user has selected.

6. view_details.php
  Shows detailed information about a selected programme, including modules.

7. CSS files
  Provide styling and layout for the user interface.



## Database Setup

1. Open phpMyAdmin
2. Create a new database (e.g., `student_course_hub`)
3. Import the file:
   example-data.sql

4. Update database connection in:
   admin portal/config.php


## How to Run the Project

1. Install XAMPP
2. Start Apache and MySQL
3. Move the project folder into:
   C:\xampp\htdocs\
   
4. Open your browser and go to:
   http://localhost/Finalassignment/frontpage/index.php
   
5. Register a new account and login


## User Workflow

1. User signs up for an account
2. User login
3. User accesses the dashboard
4. User browses programmes
5. User views programme details
6. User registers interest
7. User manages interests (add/remove)
8. User logout


## Security Features

* Password hashing using 'password_hash()'
* Password verification using 'password_verify()'
* Session-based authentication
* CSRF protection for secure actions
* Input validation and sanitization


## Conclusion

This project demonstrates the implementation of a complete web application with user authentication, database interaction, and dynamic content management.
It highlights practical skills in backend development, frontend design, and secure programming.


## Author

Student Course Hub Project
Final Assignment


I have created a new `README.md` file that summarizes our progress and includes all the environment details you provided. This document is now ready to be added to your project's GitHub repository.

-----

### README.md

```markdown
# EduCore - A School Management SaaS Platform

## Overview

EduCore is a multi-tenant School Management SaaS designed to streamline administrative, academic, and financial operations for schools. The platform provides a centralized system for managing students, teachers, and and a comprehensive set of school activities, all while enforcing a robust **Role-Based Access Control (RBAC)** model.

## Tech Stack & Environment

* **Backend:** PHP (No Framework)
* **Database:** MySQL (MariaDB 10.4.32)
* **Web Server:** Apache/2.4.58 (Win64) with **PHP/8.2.12**
* **Caching:** Redis
* **Dependency Management:** Composer
* **Routing:** fast-route

## Getting Started

### Project Structure

```

/educore
├── /app
│   ├── /config                    # Application configuration files
│   │   └── database.php           # Database connection settings
│   │
│   ├── /controllers               # Handles request logic and interacts with models/views
│   │   ├── Auth/
│   │   │   ├── AuthController.php   # Handles user authentication (login/logout)
│   │   │   └── UserController.php   # Handles user creation and related actions
│   │   ├── Core/
│   │   │   ├── DashboardController.php  # Handles the dashboard view and related logic
│   │   │   └── HomeController.php
│   │   └── School/
│   │       └── SchoolController.php
│   │
│   ├── /core                      # Core framework components
│   │   ├── AppLogger.php          # Singleton for logging application events
│   │   ├── Database.php           # Singleton for managing the database connection
│   │   ├── Middleware.php         # Abstract base class for all middleware
│   │   ├── Model.php              # Abstract base class for all models
│   │   └── Router.php             # Handles request routing and middleware execution
│   │
│   ├── /middleware                # Middleware for request filtering
│   │   ├── /School
│   │   │   └── SchoolValidationMiddleware.php # Validates school registration forms
│   │   ├── /User
│   │   │   ├── /Admin
│   │   │   │   └── MasterAdminMiddleware.php  # Ensures user has master admin privileges
│   │   │   ├── AuthMiddleware.php           # Protects routes by checking for an active session
│   │   │   ├── AutoLogoutMiddleware.php     # Logs out inactive users
│   │   │   └── LoginValidationMiddleware.php  # Validates user login forms
│   │   │
│   │   └── Middleware.php             # Base middleware file
│   │
│   ├── /models                    # Handles database interactions
│   │   ├── LoginAttemptModel.php    # Manages login attempt tracking
│   │   ├── RoleModel.php            # Manages user roles data
│   │   ├── SchoolModel.php          # Manages schools data
│   │   └── UserModel.php            # Manages user-related data
│   │
│   └── /routes                    # Defines application routes
│       └── routes.php
│
├── /database
│   └── schema.sql                 # Database schema
│
├── /public                        # Web-accessible files
│   ├── /css
│   ├── /js
│   │   ├── /school
│   │   │   └── register.js
│   │   └── /user
│   │       ├── login.js
│   │       └── create-user.js      # Handles user creation form logic and API calls
│   ├── index.php                  # The application's single entry point
│   └── .htaccess                  # Apache rewrite rules
│
├── /resources
│   ├── /views                     # All HTML view files
│   │   ├── auth/
│   │   │   ├── login.html           # User login page
│   │   │   └── register_school.html # School registration page
│   │   └── dashboards/
│   │       └── ...                  # Templates for all user dashboards
│   │
│
├── /vendor                        # Composer dependencies
│   └── ...
│
├── .env.example                   # Example environment variables
├── .gitignore                     # Specifies intentionally untracked files to ignore
├── composer.json                  # Composer dependencies file
├── composer.lock                  # Composer lock file
└── README.md                      # Project documentation

````

### Prerequisites

* **PHP 8.1 or higher**
* **MySQL 8.0 or higher**
* **Redis Server**
* **Composer**

### Installation

1.  Clone the repository:
    `git clone https://github.com/jeffreykwakye/educore.git`
    `cd educore`

2.  Install PHP dependencies:
    `composer install`

3.  Set up your environment file:
    Create a `.env` file in the `/config` directory with your database and other credentials.

    ```
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_NAME=educore
    DB_USER=root
    DB_PASS=
    ```

4.  Run the database setup script to create tables:
    `php app/core/setup.php`

## License

This project is licensed under a proprietary license. All rights are reserved by the copyright holder.

---




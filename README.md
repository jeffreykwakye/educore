# EduCore — Modular School Management SaaS

## Overview

EduCore is a multi-tenant School Management SaaS platform designed to streamline administrative, academic, and operational workflows for schools. It supports granular Role-Based Access Control (RBAC), modular service architecture, and scalable multi-tenant deployment.

## Tech Stack

- **Backend:** PHP 8.2+ (No Framework)
- **Database:** MySQL / MariaDB
- **Web Server:** Apache 2.4+
- **Caching:** Redis
- **Routing:** FastRoute
- **Dependency Management:** Composer

## Project Structure
educore/ ├── app/ │   ├── controllers/ │   │   ├── Api/ │   │   ├── Auth/ │   │   ├── Core/ │   │   └── Schools/ │   ├── services/ │   │   ├── RolePermissionService.php │   │   ├── UserService.php │   │   └── NotificationService.php │   ├── models/ │   ├── middleware/ │   ├── views/ │   └── core/ │       ├── Router.php │       ├── Model.php │       ├── Database.php │       └── AppLogger.php ├── public/ │   ├── index.php │   └── assets/ ├── routes/ │   └── web.php ├── config/ │   └── .env ├── storage/ │   ├── logs/ │   └── uploads/ ├── database/ │   └── schema.sql (modular, evolves per feature) ├── tests/ ├── vendor/ ├── LICENSE.txt

## Getting Started

### Prerequisites

- PHP 8.2+
- MySQL 8.0+ or MariaDB
- Redis Server
- Composer

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/jeffreykwakye/educore.git
   cd educore
   
- Install dependencies:
composer install

- Configure environment: Create a .env file in /config:
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=educore
DB_USER=root
DB_PASS=


- Run setup script:
php app/core/setup.php


Note: The database schema is modular and evolves as features are added.




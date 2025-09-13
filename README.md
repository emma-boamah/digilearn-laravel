# Digilearn Laravel
Digilearn is an oline learning platform built with laravel. It supports user authentication, lesson management,virtual classrooms, quizzes, documents, admin controls, and more.

## Features
- User registration, login, and unlock flow
- Role-based access (admin, student)
- Lesson and video management
- Document and quiz association with videos
- Virtual classroom with real-time participation
- Subscription and access control
- Admin dashboard for content and user management
- Security headers and CSP enforcement

## Requirements
### Server
- **Linux** (Ubuntu 22.04+ recommended)
- **PHP 8.1+**
- **MySQL 8+** or **MariaDB**
- **Composer**
- **Node.js 18+** and **npm** (for asset compilation)
- **Redis**  (for queues, throttling, and caching)
- **Supervisor** (for queue workers)
- **Nginx** or **Apache**
- **Certbot** (for SSL, optional but recommended)

### PHP EXTENSIONS
- `pdo_mysql`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `fileinfo`
- `gd` or `imagick`
- `redis`
- `zip`

---

## Installation

### 1. Clone the Repository

```sh
git clone https://github.com/emma-boamah/digilearn-laravel-git
cd digilearn-laravel/digilearn-laravel
```

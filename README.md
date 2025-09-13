# Digilearn Laravel
Digilearn is an oline learning platform built with laravel. It supports user authentication, lesson management,virtual classrooms, quizzes, documents, admin controls, and more.

---

## Features
- User registration, login, and unlock flow
- Role-based access (admin, student)
- Lesson and video management
- Document and quiz association with videos
- Virtual classroom with real-time participation
- Subscription and access control
- Admin dashboard for content and user management
- Security headers and CSP enforcement

---

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

### Docker
- Docker & Docker Compose (Use the provided docker-compose.yml for local/dev setup)

### Laravel Packages Used
- `laravel/framework` (core)
- `laravel/socialite` (OAuth login)
- `laravel/tinker` (REPL)
- `mongodb/laravel-mongodb` (MongoDB support)
- `pbmedia/laravel-ffmpeg` (video processing)
- `predis/predis` (Redis client)

### Node Packages Used
- vite (asset bunding)
- sass (SCSS compilation)
- alpinejs (frontend interactivity)
- concurrently, stylelint, autoprefixer, postcss (dev tools)

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

### 2. Install PHP Dependencies
```sh
composer install --optimize-autoloader --no-dev
```

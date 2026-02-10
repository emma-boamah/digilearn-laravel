<<<<<<< HEAD
This is an e-learning platform which has structured courses contents and great security features too
=======
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

### Environment Variables
- **Database:** MySql and MongoDB credentials
- **Cache/Queue:** Redis credentials
- **Session:** Secure session settings
- **Mail:** Mailer config (default: log)
- **AWS:** For file storage (optional, not required unless using S3)
- **Security:** CSP, HSTS, rate limiting, etc.

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

### 3. Install Node Dependencies & Buildings Assets

```sh
npm install
npm run build
```

### 4. Configure Environment
Copy `.env.example` to `.env` and set your environment variables:

```sh
cp .env.example .env
php artisan key:generate
```

Set database, Redis, mail, and other credentials in `.env`.

### 5. Run Migrations

```sh
php artisan migrate --force
```

### 6. Set Up Storage
```sh
php artisan storage:link
```
### 7. Set Up Queue Worker (Production)
Install Supervisor and configure a worker for Laravel queues:

```sh
sudo apt install supervisor
# Add a config file for laravel-worker
```

### 8. Set Up Web Server
Configure Nginx or Apache to serve the `public` directory.

### 9. Set Up SSL (Recommended)
Use Certbot to enable HTTPS.

---

## Production Checklist

- [ ] All required PHP extensions installed
- [ ] Database and Redis running
- [ ] Supervisor running for queue workers
- [ ] Storage linked (`php artisan storage:link`) or `sail artisan storage:link` (For Docker Sail Users)
- [ ] Proper file permissions (`storage`, `bootstrap/cache`)
- [ ] Environment variables set
- [ ] Assets built (`npm run build`)
- [ ] Web server configured for `public` directory
- [ ] SSL enabled

---

## Security
- Uses strict Content Security Policy (CSP) headers
- Rate limiting and throttling enabled
- CSRF protection
- Secure session and cookie settings

## Contributing

1. Fork the repo
2. Create your feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes<img width="1920" height="1080" alt="Screenshot from 2026-02-09 22-36-33" src="https://github.com/user-attachments/assets/ba1b3e7b-2257-449b-a88b-cf180493b36c" />
<img width="1920" height="1080" alt="Screenshot from 2026-02-09 22-36-44" src="https://github.com/user-attachments/assets/c823c169-3310-42a7-9a82-cc2f75e0e276" />
<img width="1920" height="1080" alt="Screenshot from 2026-02-09 22-37-33" src="https://github.com/user-attachments/assets/f47e947b-7237-454f-ac1b-d02ff2477fe9" />
<img width="1920" height="1080" alt="Screenshot from 2026-02-09 22-38-05" src="https://github.com/user-attachments/assets/9f7514cd-c256-4a9f-8b0e-e670a99aa6a3" />

4. Push to the branch
5. Create a pull request
>>>>>>> 17ecdc3 (These are fixes to the University logic flow, using the right intended route)

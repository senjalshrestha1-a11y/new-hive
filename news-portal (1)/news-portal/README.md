# News Portal (PHP + MySQL)

A simple, no-framework news portal:

- **Public site** — anyone can browse, search, and read news. No login needed.
- **Admin panel** — a login-protected dashboard where admins can add, edit,
  and delete articles.

## Structure

```
news-portal/
├── admin/              # Admin-only pages (all require login)
│   ├── includes/       # Admin layout partials
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── add_news.php
│   ├── edit_news.php
│   ├── delete_news.php
│   └── change_password.php
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── config/
│   ├── config.php      # Site constants (edit BASE_URL here)
│   └── db.php          # Database credentials (edit these)
├── database/
│   └── schema.sql       # Run this once to create the database + tables
├── includes/
│   ├── functions.php    # Shared helpers (auth, CSRF, uploads, etc.)
│   ├── header.php        # Public site header
│   └── footer.php        # Public site footer
├── setup/
│   └── create_admin.php # Run ONCE to create your first admin login, then delete
├── uploads/              # Uploaded article images end up here
├── index.php             # Public homepage (list, search, filter, pagination)
└── article.php           # Public single-article page
```

## Setup (local dev with XAMPP / WAMP / MAMP)

1. **Copy the folder.** Drop `news-portal/` into your server's web root, e.g.:
   - XAMPP (Windows): `C:\xampp\htdocs\news-portal`
   - XAMPP (Mac/Linux): `/Applications/XAMPP/htdocs/news-portal` or `/opt/lampp/htdocs/news-portal`

2. **Create the database.** Open phpMyAdmin (or the `mysql` CLI) and import
   `database/schema.sql`. This creates the `news_portal` database with the
   `admins`, `categories`, and `news` tables, plus a few starter categories.

3. **Set your DB credentials.** Open `config/db.php` and check `$DB_USER` /
   `$DB_PASS` match your local MySQL setup (XAMPP defaults are usually
   `root` with no password — already set that way here).

4. **Check the base URL.** Open `config/config.php` and confirm `BASE_URL`
   matches your folder name, e.g. `/news-portal` if you access the site at
   `http://localhost/news-portal/`.

5. **Create your admin account.** Visit:
   ```
   http://localhost/news-portal/setup/create_admin.php
   ```
   Fill in a username and password. This uses PHP's own `password_hash()`
   function so the hash is guaranteed to work with your PHP install.

6. **Delete the `/setup` folder** once your admin account is created —
   leaving it in place would let anyone create new admin logins.

7. **You're set!**
   - Public site: `http://localhost/news-portal/index.php`
   - Admin login: `http://localhost/news-portal/admin/login.php`

## Features

- **Public side:** homepage feed, category filter, keyword search,
  pagination, per-article view counter, responsive layout.
- **Admin side:** session-based login (bcrypt password hashing, brute-force
  rate limiting, CSRF protection on every form, session regeneration on
  login), dashboard with stats, full CRUD on articles (title, summary,
  content, category, image upload, draft/published status), change-password
  page.
- **Security basics included:** PDO prepared statements everywhere (no SQL
  injection), output escaping everywhere (no XSS), CSRF tokens on all
  state-changing forms, image upload validated by real MIME type (not just
  extension), uploads folder blocked from executing PHP.

## Things you may want to extend later

- Multiple admin roles (editor vs. super-admin)
- Comments on articles
- Rich text editor (e.g. TinyMCE/Quill) instead of a plain textarea
- Email notifications, RSS feed, tags in addition to categories

These were left out on purpose to keep this version simple to read and run.

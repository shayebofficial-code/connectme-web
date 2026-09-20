# ConnectMe - Mobile-First Online Dating Platform (PHP 8.1+ & MySQL 8+)

**ConnectMe** is a complete, lightweight, production-ready online dating application built using **PHP 8.1+**, **MySQL 8+**, **HTML5**, **CSS3 (vanilla)**, and **vanilla JavaScript**. It is specifically designed for quick and seamless deployment on standard shared hosting providers like Hostinger, cPanel, or any LAMP/LEMP stack.

---

## 🚀 Features Overview

* **Authentication System**:
  * Email & Password registration with strict 18+ age verification.
  * **Google OAuth 2.0 / OpenID Connect** ("Continue with Google") single sign-on.
  * Password hashing via `password_hash()` and PDO prepared statements.
  * Secure sessions & CSRF protection on all forms and API endpoints.

* **Profile Management**:
  * Profile details (Name, DOB/Age, Gender, Looking For, City, Bio, Interests tags).
  * Photo upload with server-side MIME verification (`finfo`), size caps (5MB), and randomized filenames saved outside executable paths.

* **Discover & Matching Engine**:
  * Modern dating card user interface with swipe/reaction actions (❤️ Like, 🚫 Block, Report).
  * Filter feed by City, Gender, Age Range, and Interests.
  * Automatic mutual match creation when two users like each other.

* **Real-Time AJAX Chat**:
  * Exclusive chat access for matched users only.
  * Message history, unread counter badges, and relative timestamps.
  * XSS escaping and AJAX polling engine.

* **Safety & Security Moderation**:
  * Instant User Blocking (excludes blocked users from Discover and Chat).
  * User Reporting system with confidential moderation queue.
  * Prominent dating safety warning notices ("Never send money, OTPs, or passwords").

* **Premium Subscriptions & Payments**:
  * Subscriptions (Monthly ₹199, Quarterly ₹499, Half Year ₹799, Yearly ₹1,299).
  * Features: Unlimited Likes, Direct Messaging, See Who Liked You, VIP Badge.
  * Integrated **Razorpay India** payment flow with server-side HMAC signature verification.
  * Includes a built-in **Mock Payment Simulator** mode for instant sandbox testing without real credentials.

* **Admin Portal (`/admin/`)**:
  * Comprehensive dashboard (Users count, Paid Revenue, Active Subscriptions, Pending Reports).
  * User search, activation toggle, manual VIP premium grant, deletion.
  * Report moderation queue & user ban tool.
  * Profile photo moderation & manual profile verification badge toggle.
  * Pricing plan and payment transaction log.

---

## 🛠️ Installation & Setup Guide

### 1. Database Creation & Schema Import
1. Open phpMyAdmin or your MySQL client.
2. Create a new database named `connectme_db` with character set `utf8mb4` and collation `utf8mb4_unicode_ci`.
3. Import `database.sql` into your new database:
   ```bash
   mysql -u YOUR_DB_USER -p connectme_db < database.sql
   ```

### 2. Configure Database & Environment
1. Copy `config.example.php` to `config/config.php`:
   ```bash
   cp config.example.php config/config.php
   ```
2. Edit `config/config.php` and set your database connection details:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'connectme_db');
   define('DB_USER', 'your_db_username');
   define('DB_PASS', 'your_db_password');
   ```

### 3. Initial Admin Account Creation
Run the one-time admin setup wizard by visiting:
```
http://yourdomain.com/admin/setup.php
```
Enter your desired admin email and password. Once completed, **delete `admin/setup.php`** for security!

---

## 🔑 OAuth & Payment Gateway Configuration

### 4. Google OAuth 2.0 Setup
1. Go to the [Google Cloud Console](https://console.cloud.google.com/).
2. Create a new project and navigate to **APIs & Services > Credentials**.
3. Create an **OAuth 2.0 Client ID** (Web application).
4. Add your **Authorized Redirect URI**:
   `https://yourdomain.com/auth/google-callback.php`
5. Copy your Client ID and Client Secret into `config/config.php`:
   ```php
   define('GOOGLE_CLIENT_ID', 'YOUR_CLIENT_ID.apps.googleusercontent.com');
   define('GOOGLE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET');
   ```

### 5. Payment Gateway Setup (Razorpay & Test Mode)
* **Testing without credentials**: Set `ENABLE_MOCK_PAYMENT` to `true` in `config/config.php`. This activates the sandbox test mode.
* **Live Payments**: Set `ENABLE_MOCK_PAYMENT` to `false` and enter your Razorpay keys:
  ```php
  define('ENABLE_MOCK_PAYMENT', false);
  define('RAZORPAY_KEY_ID', 'rzp_live_XXXXX');
  define('RAZORPAY_KEY_SECRET', 'YOUR_RAZORPAY_SECRET');
  ```
* Webhook URL for asynchronous payment capture:
  `https://yourdomain.com/payment/webhook.php`

---

## 🌐 Shared Hosting Deployment Checklist (Hostinger / cPanel)

1. **Upload Files**: Upload the entire `connectme` directory contents to `public_html/`.
2. **Directory Permissions**: Ensure the `uploads/profiles/` directory has `755` write permissions.
3. **HTTPS / SSL Configuration**: Ensure an SSL certificate (Let's Encrypt / Hostinger SSL) is active on your domain.
4. **Apache `.htaccess`**: Verify that `.htaccess` is present in the document root to block access to sensitive `.sql` and `.php` configuration files.

---

## 🛡️ Security Best Practices

- ✅ **Prepared Statements**: All database operations use PDO prepared statements.
- ✅ **Password Hashing**: Direct passwords are never stored; standard `password_hash()` is used.
- ✅ **XSS Protection**: All user input rendered on pages is escaped using `htmlspecialchars()`.
- ✅ **Upload Hardening**: Uploaded files undergo MIME type verification via `finfo` and are assigned random hex filenames. `.htaccess` prevents execution of scripts inside `/uploads/`.
- ✅ **CSRF Tokens**: Form submissions and AJAX requests validate random session CSRF tokens.

---

## 📄 File Architecture Overview

```text
connectme/
├── index.php                 # Homepage landing page
├── .htaccess                 # Security headers & file protection
├── database.sql              # Complete MySQL 8 database schema
├── config.example.php        # Configuration template
├── README.md                 # Project documentation
│
├── config/
│   ├── config.php            # Primary app settings & API credentials
│   ├── database.php          # PDO singleton connection
│   └── google.php            # Google OAuth helper
│
├── includes/
│   ├── header.php            # Global site header & navigation
│   ├── footer.php            # Global site footer & mobile navigation
│   ├── auth.php              # Session & login state validation
│   ├── functions.php         # Utility helpers & upload processing
│   └── csrf.php              # CSRF protection functions
│
├── assets/
│   ├── css/style.css         # Modern design system & responsive styling
│   └── js/app.js             # Interactive client engine & chat polling
│
├── auth/
│   ├── register.php          # 18+ user registration
│   ├── login.php             # Email & Google login
│   ├── google-login.php      # OAuth URL handler
│   ├── google-callback.php   # OAuth token exchange & account linking
│   ├── logout.php            # Session destruction
│   └── forgot-password.php   # Password reset request
│
├── user/
│   ├── dashboard.php         # Main user hub & stats
│   ├── profile.php           # Profile edit & photo upload
│   ├── discover.php          # Dating card discovery feed
│   ├── matches.php           # Mutual matches grid
│   ├── chat.php              # Real-time conversation screen
│   ├── messages.php         # Conversations inbox
│   ├── subscribe.php         # Premium subscription selection
│   ├── likes.php             # Likes received & sent hub
│   ├── settings.php          # Account security settings
│   ├── block.php             # Block user controller & list
│   └── report.php            # User report form
│
├── payment/
│   ├── create-order.php      # Server-side order creation & mock mode
│   ├── success.php           # Signature verification & premium grant
│   ├── failed.php            # Failure handler
│   └── webhook.php           # Idempotent gateway webhook processor
│
├── admin/
│   ├── setup.php             # Initial admin setup wizard
│   ├── index.php             # Admin overview dashboard
│   ├── users.php             # User management
│   ├── reports.php           # Misconduct reports queue
│   ├── profiles.php          # Photo moderation & verification
│   ├── plans.php             # Subscription plans manager
│   ├── payments.php          # Payment transaction logs
│   └── settings.php          # System configuration overview
│
├── api/
│   ├── like.php              # Like / Pass AJAX API
│   ├── messages.php          # Chat messages AJAX API
│   └── search.php            # Discover feed AJAX API
│
└── uploads/
    └── profiles/             # Secure photo storage directory
```
# connectme-web

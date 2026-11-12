# Guide d'installation (RDC)

This guide covers installing the School Management System on a local (XAMPP / WAMP / MAMP / Laragon) or production Apache server.

## Requirements

- PHP **8.0 or higher** with the following extensions: `pdo_mysql`, `mbstring`, `json`, `openssl`.
- MySQL **5.7+** or MariaDB **10.3+**.
- Apache with `mod_rewrite` (or the PHP built-in web server for quick testing).

## 1. Copy the project

Place the `school-management` folder inside your web server's document root.

- **XAMPP:** `C:\xampp\htdocs\school-management`
- **WAMP:** `C:\wamp64\www\school-management`
- **Laragon:** `C:\laragon\www\school-management`
- **Linux (Apache):** `/var/www/html/school-management`

## 2. Create the database

1. Start MySQL (and Apache) in your control panel.
2. Open **phpMyAdmin** and create a new database, e.g. `school_management` (collation `utf8mb4_general_ci`).
3. Importez le fichier unique `database/school_management.sql` (phpMyAdmin ou `mysql < fichier`) :
   il crée la base `school_management`, toutes les tables et les données de test.
   (L'installateur `public/install.php` fait la même chose.)

## 3. Configure the database connection

Edit `app/config/database.php`:

```php
return [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'dbname'   => 'school_management',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];
```

Update `username` and `password` to match your MySQL account.

## 4. Configure the application URL

Edit `app/config/config.php` and set `BASE_URL` to match your installation path:

```php
define('BASE_URL', 'http://localhost/school-management/public');
```

- Local (XAMPP, in `htdocs`): `http://localhost/school-management/public`
- Production (domain root): `http://yourdomain.com` (when the docroot points at `public/`)
- To test without Apache, run the PHP built-in server from the project root:

  ```bash
  php -S localhost:8000 -t public
  ```

  and open `http://localhost:8000`.

This project ships in **demo mode** (`DEMO_MODE = true`). In demo mode the login
page shows demo credentials and database backup/restore are disabled. Flip
`DEMO_MODE` to `false` once you deploy for real use.

## 5. Point the document root at `public/` (recommended)

For maximum security, configure your virtual host / domain to use the `public` directory as its document root:

```apache
<VirtualHost *:80>
    ServerName school.local
    DocumentRoot "C:/xampp/htdocs/school-management/public"

    <Directory "C:/xampp/htdocs/school-management/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

If you cannot change the docroot, the included root `.htaccess` forwards every request into `public/` automatically.

## 6. Permissions

Ensure the web server user can write to the uploads directory:

- `public/uploads/` (and its subfolders `users`, `settings`, `students`, `teachers`)

On Linux:

```bash
chmod -R 775 public/uploads
chown -R www-data:www-data public/uploads
```

## 7. Login

Open your site URL and sign in with a demo account:

- **Admin:** `admin@school.com` / `Admin123`
- **Teacher:** `teacher@school.com` / `Teacher123`
- **Student:** `student@school.com` / `Student123`

The teacher account is a fully scoped **Teacher** role: it can only view assigned
classes, students, subjects and exams, and only mark attendance / enter results for
those classes and subjects. All admin modules (users & roles, settings, fees,
payroll, etc.) return **403** for this role.

The student account is a **Student** role: it only sees its own class, subjects,
results and attendance. Admin and teacher features are blocked.

Immediately change the passwords via **My Profile → Change Password**, then review the seeded roles and permissions under **System → Users & Roles**.

## 8. Initial configuration

- Go to **Settings** and set your school name, short name, address, contact details, motto, principal name, logo, favicon, currency, timezone and active academic session.
- Add your **Fee Types**, **Transport Vehicles/Routes/Drivers**, and **Inventory Categories/Suppliers** as needed.
- Create users for teachers and parents; link them to student/teacher profiles via the relevant module.

## 9. Going live / demo deployment

Before exposing the site to the public:

1. **Set `BASE_URL`** in `app/config/config.php` to your live URL (see section 4).
2. **Set `DEMO_MODE`** — keep `true` for a public demo (login page shows demo accounts, backup/restore are disabled), set `false` for real use.
3. **Delete `public/install.php`** after installation so the database cannot be reinstalled or reseeded.
4. Point the domain's **document root at `public/`** (see section 5) so `app/`, `database/`, and config files are never web-accessible.
5. Ensure `public/uploads/` is writable by the web server user (section 6).
6. Set real credentials in `app/config/database.php` and use HTTPS.

## Troubleshooting

| Problem                          | Fix |
|----------------------------------|-----|
| Blank page                       | Check that `app/config/database.php` credentials are correct and the DB exists. Set `APP_DEBUG` to `true` temporarily in `app/config/config.php`. |
| "Database connection failed"     | MySQL isn't running, or host/credentials are wrong. |
| 404 on every page                | Ensure `mod_rewrite` is enabled and `AllowOverride All` is set for the `public` directory. |
| Styles / assets missing          | Check `BASE_URL` in `app/config/config.php` matches your install URL exactly (trailing `/public` for local installs). |
| Session / login issues           | Make sure the web server has a writable session directory and PHP session support is enabled. |

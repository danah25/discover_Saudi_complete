# Discover Saudi Arabia — Secure Web Application
 
A simple user-management web application for a Saudi tourism site ("اكتشف السعودية"), built to demonstrate secure
coding practices: SQL-injection prevention, strong password storage, XSS prevention, role-based access control, and
encryption of sensitive data over HTTPS.
 
Stack: PHP 8 (PDO MySQL + OpenSSL), MySQL (InnoDB), Apache. These instructions use **XAMPP on Windows**.
 
---
 
## 1. Overview
 
Features:
- **User registration** — create an account with a username and password (bcrypt-hashed, complexity enforced).
- **Login / logout** — session-based authentication; logout fully destroys the session.
- **User dashboard** — shows the username and a private note encrypted with AES-256-GCM.
- **Admin panel** — role-restricted; an admin can add, edit, and delete tourist destinations.
- **Destinations** — logged-in users can browse the 12 sample destinations.
Roles:
- `user` — can log in, view destinations, and read/write their own private note.
- `admin` — can manage destinations (add / edit / delete). Set manually in the database (see step 6).
---
 
## 2. Requirements
 
- XAMPP (Apache + PHP 8) with the **OpenSSL** and **PDO MySQL** extensions enabled.
- A MySQL server (XAMPP's MySQL, or a standalone MySQL that MySQL Workbench connects to) on port 3306.
- A modern browser.
---
 
## 3. Setup
 
**1. Copy the project into the web root.** Place the `Discover_Saudi 5` folder (including the hidden `.htaccess`
and `.user.ini`) in your XAMPP web root, for example:
 
```
C:\xampp\htdocs\discover_saudi\Discover_Saudi 5\
```
 
Keep this README and the `setup/` folder outside the web root.
 
**2. Create the database and import the schema.** In phpMyAdmin or MySQL Workbench, create a database (this project
uses `discover_saudi_complete`) with InnoDB, then import `Discover_Saudi 5/discover_saudi_complete.sql`. This creates
the `users`, `private_notes`, and `destinations` tables plus 12 sample destinations (no users).
 
**3. Create the private configuration folder (outside the web root).** Create a folder for secrets, e.g.:
 
```
C:\xampp\apache\conf\setup\
```
 
Copy `setup/discover-saudi-db.php` into it and fill in your real database name, user, and password. The application
reads this path from the `DISCOVER_SAUDI_CONFIG_DIR` environment variable, defaulting to
`C:/xampp/apache/conf/setup` (see `db.php` and `dashboard.php`).
 
**4. Create the note encryption key** (32 random bytes) in that same folder. From a PHP shell or by running a
one-off script:
 
```
php -r "file_put_contents('C:/xampp/apache/conf/setup/discover-saudi-private-note.key', random_bytes(32));"
```
 
Keep this key private and back it up. If it is lost, all notes encrypted with it become unreadable (by design).
 
**5. Harden the session cookie in `php.ini`.** XAMPP's mod_php does **not** read the per-folder `.user.ini`, so set
these in `C:\xampp\php\php.ini` under `[Session]`, then restart Apache:
 
```ini
session.name            = DiscoverSaudiLocalSession
session.cookie_httponly = 1
session.cookie_secure   = 1
session.cookie_samesite = "Lax"
session.use_strict_mode = 1
session.use_only_cookies = 1
```
 
Because `cookie_secure = 1`, the session cookie is only sent over HTTPS (see step 7).
 
**6. Run the app and create an admin.** Register a normal account at the registration page. To make an admin,
register a second account, then promote it in the database:
 
```sql
USE discover_saudi_complete;
UPDATE users SET role = 'admin' WHERE username = 'YOUR_ADMIN_USERNAME';
```
 
Log out and log back in as that account to get the admin panel.
 
**7. Use HTTPS (TLS/SSL).** XAMPP serves HTTPS on port 443. Open the site with `https://`:
 
```
https://localhost/discover_saudi/Discover_Saudi%205/
```
 
This project uses a **locally-trusted certificate** created with [mkcert](https://github.com/FiloSottile/mkcert), so the
browser shows a valid secure connection (padlock, no warning):
 
1. Download `mkcert` (the `windows-amd64` build) and place `mkcert.exe` in a folder, e.g. `C:\mkcert`.
2. In an **Administrator** Command Prompt, run:
```
cd C:\mkcert
mkcert -install
mkcert localhost 127.0.0.1 ::1
```
   This creates `localhost+2.pem` and `localhost+2-key.pem`.
3. In `C:\xampp\apache\conf\extra\httpd-ssl.conf`, point Apache at them:
```
SSLCertificateFile    "C:/mkcert/localhost+2.pem"
SSLCertificateKeyFile "C:/mkcert/localhost+2-key.pem"
```
4. Restart Apache.
(If you skip mkcert, XAMPP's default self-signed certificate still provides HTTPS/TLS; the browser just shows a
"Not secure" warning because the certificate is not from a trusted authority. The traffic is encrypted either way.)
 
---
 
## 4. How to test the security features
 
Each test shows what an attacker would try and what the secure application does. Use a logged-in account where noted.
 
### 4.1 SQL injection (login & registration)
- **Where:** the login page username field.
- **Try:** username `' OR '1'='1' #` and any password.
- **Expected (secure):** login is **rejected** ("اسم المستخدم أو كلمة المرور غير صحيحة"). The query uses a
  prepared statement (`WHERE username = :username`), so the input is treated as a literal username, not SQL.
- **Registration check:** entering a single quote `'` as the username and submitting stores it as an ordinary
  username with no SQL error — again because of the parameterized `INSERT`.
### 4.2 Weak password storage
- **Where:** the `users` table in the database.
- **Try:** register a new user, then run `SELECT username, password_hash FROM users;`.
- **Expected (secure):** the `password_hash` column shows a **bcrypt** hash beginning with `$2y$12$` — never the
  plaintext password and never an MD5 hash. Passwords must be at least 8 characters with uppercase, lowercase, a
  number, and a special character (enforced server-side in `register.php`).
### 4.3 Cross-Site Scripting (XSS)
- **Where:** the private note on the user dashboard.
- **Try:** log in as a normal user, save the note `<script>alert('XSS')</script>`, then view it.
- **Expected (secure):** no alert fires. The note is displayed as plain text because output is escaped with
  `htmlspecialchars($note, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
### 4.4 Access control (RBAC)
- **Where:** the admin pages.
- **Try:** while logged in as a normal `user`, open `admin.php` (or `add_destination.php`) directly by typing the
  URL.
- **Expected (secure):** you are **redirected to the dashboard** (or login) and cannot see the admin panel. Each
  admin page checks `$_SESSION['role'] === 'admin'` on the server, so changing the URL alone does not grant access.
  Logging in as an `admin` loads the panel normally.
### 4.5 Encryption of sensitive data
- **At rest:** after saving a note, run `SELECT * FROM private_notes;`. The `ciphertext`, `nonce`, and `auth_tag`
  columns are **binary (BLOB)** — the note text is not readable in the database. Each note is encrypted with
  AES-256-GCM using the key stored outside the web root.
- **In transit:** open the site over `https://` (step 7). Traffic is encrypted with TLS.
- **Session token:** in the browser DevTools → Application → Cookies, the `DiscoverSaudiLocalSession` cookie shows
  **HttpOnly ✓**, **Secure ✓**, and **SameSite = Lax**, so it cannot be read by JavaScript and is only sent over
  HTTPS.
### 4.6 Session / logout
- **Try:** log in, then click Logout. Press the browser Back button, or type the dashboard/admin URL directly.
- **Expected (secure):** you cannot return to the authenticated page — you are sent to the login page. Logout
  clears the session, expires the cookie, and the pages send `no-store` cache headers plus a `pageshow` reload.
---
 
## 5. Project structure (key files)
 
| File | Purpose |
| --- | --- |
| `db.php` | PDO connection; reads DB settings from the private config folder |
| `register.php` | Registration with bcrypt + password-complexity rule |
| `login.php` | Login with prepared statement, `password_verify`, session regeneration |
| `logout.php` | Destroys the session and expires the cookie |
| `dashboard.php` | User dashboard + AES-256-GCM encrypted private note (CSRF-protected) |
| `admin.php` | Admin panel (role-restricted) listing destinations |
| `add_destination.php` / `edit_destination.php` / `delete_destination.php` | Admin CRUD on destinations (role-restricted, prepared statements) |
| `regions.php` / `destination.php` | Browse destinations (login required) |
| `navigation.php` | Shared navigation; role-aware menu |
 
---
 
## 6. Security notes
 
- Database credentials and the note key live **outside** the web root and are excluded from the repository.
- All database access uses **PDO prepared statements**.
- Passwords use **bcrypt** (`PASSWORD_BCRYPT`); never plaintext or MD5.
- All user-supplied output is escaped with **`htmlspecialchars`**.
- Admin actions are gated by **server-side role checks**.
- Sensitive note data is encrypted with **AES-256-GCM**; the site is served over **HTTPS/TLS**; the session cookie
  is **HttpOnly + Secure + SameSite=Lax**.
 
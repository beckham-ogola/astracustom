# AstraCampus — School Management System

A complete, self-contained PHP/MySQL school management system: student admissions,
class promotion/demotion, fee billing (single & batch), payment collection with
M-Pesa STK Push/Cash/Bank/Cheque support, printable receipts with SMS delivery,
financial reports, audit logging, and a public parent fee-lookup portal.

**Design:** a fresh visual identity — Plus Jakarta Sans/Lexend typography, a
custom indigo-violet palette, softer shadows and rounded corners, and gradient
stat tiles on the dashboard.

**Navigation:** a persistent, grouped sidebar (Overview / Academics / Finance /
Administration) on desktop, and a floating pill-style bottom bar on mobile —
both generated from the same role-aware definition, so they never show
different things for the same person. Related screens are still grouped as
tabs on one page instead of separate menu items — Students + New Admission
live together, Bill Types + Fee Structure + Bill a Student + Batch Billing
live together under "Billing", Collect Payment + Recent Receipts live
together under "Payments", and the four finance reports live together under
"Reports".

Reports used to be buried three taps deep inside a catch-all "More" menu —
it's now a first-class Finance section, one tap from anywhere, for anyone who
handles money (developer/admin/accountant see the full four-report hub;
teachers see their own class balances report). The old "More" menu itself is
gone: the setup/config screens it held (Classes, Users, PDF Templates, Audit
Logs, Settings) now live under a dedicated **Administration** hub, reachable
via a gear icon in the header — kept separate from everyday work since only
developer/admin ever touch it. Account and Logout moved to the sidebar
footer / avatar menu, where they're always one tap away regardless of role.

Which items a person sees still depends on their role — accountants see
Billing's everyday tabs (Bill a Student, Batch Billing) but not the setup tabs
(Bill Types, Fee Structure), which stay admin/developer-only, and only
developer/admin ever see the Administration section at all.

Old links (`/more`, `/reports/daily-collection`, etc.) still redirect to their
new homes, so nothing that was bookmarked or linked externally breaks.

**Admission billing:** admitting a student no longer auto-bills every fee type
for their class. The New Admission tab now shows a live "Bills to Create"
checklist (all pre-checked, but every box can be unchecked) once you pick a
class, with a running total that updates as you tick/untick — so staff see and
control exactly what a new student is billed for before submitting, not after.

---

## 1. Requirements

- PHP 8.0+ with the `pdo_mysql` extension enabled (and `curl` if you want M-Pesa STK Push)
- MySQL 5.7+ (or MariaDB 10.3+)
- Apache with `mod_rewrite` enabled (or Nginx — see note below)

---

## 2. Installation

### Step 1 — Copy files
Upload the entire `astracampus/` folder to your server.

### Step 2 — Create the database

**Fresh install:**
```bash
mysql -u root -p -e "CREATE DATABASE astracampus"
mysql -u root -p astracampus < database/schema.sql
```

**Upgrading an existing AstraCampus install?** Don't re-run `schema.sql` (it
would wipe your data). Instead run the migration, which only adds what's new:
```bash
mysql -u root -p astracampus < database/migration_v2.sql
```

The schema file creates all tables, reporting views, and seeds default classes,
bill types, sample fee structure, settings, and login accounts (see below).

### Step 3 — Configure the database connection
Edit `config/database.php` and set your credentials, **or** set these environment
variables on your server (recommended for production):

```
ASTRA_DB_HOST=localhost
ASTRA_DB_NAME=astracampus
ASTRA_DB_USER=your_db_user
ASTRA_DB_PASS=your_db_password
```

### Step 4 — Point your web server at the app

**Recommended:** set your virtual host's document root to the `public/` folder.
This is the most secure option since it keeps `config/`, `models/`, `controllers/`
etc. outside the web root.

```apache
<VirtualHost *:80>
    ServerName astracampus.local
    DocumentRoot /path/to/astracampus/public
    <Directory /path/to/astracampus/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Alternative:** if you must point the document root at the project root (e.g. on
shared hosting where you can't change the vhost), the included root `index.php`
and `.htaccess` will forward requests into `public/` automatically. Just upload
the whole `astracampus/` folder to your web root.

Either way, `mod_rewrite` must be enabled (`sudo a2enmod rewrite && sudo service apache2 restart`).

### Step 5 — Set folder permissions

```bash
chmod -R 755 public/uploads
```

The `public/uploads/admission_forms/` and `public/uploads/templates/` folders
must be writable by the web server user.

### Step 6 — Visit the site
- Public parent portal: `http://your-domain/`
- Staff login: `http://your-domain/login`

---

## 3. Default Login Credentials

| Username | ID Number | Password | Role |
|---|---|---|---|
| developer | 1000 | password123 | Developer (full access) |
| admin | 1001 | password123 | Admin |
| accountant | 1002 | password123 | Accountant |
| teacher | 1003 | password123 | Teacher (assigned to Grade 1) |

**Change these passwords immediately after your first login** via *My Account* →
*Change Password*, or reset them from *Users* (Admin/Developer only).

---

## 4. Security Notes

- Passwords are hashed with **MD5** in this build, per the original specification.
  MD5 is not considered cryptographically secure for password storage. To upgrade:
  1. Replace `hash_password()` / `verify_password()` in `includes/functions.php`
     with `password_hash($password, PASSWORD_BCRYPT)` / `password_verify()`.
  2. Existing users will need their passwords reset once, since MD5 hashes cannot
     be converted to bcrypt hashes.
- All forms are protected with CSRF tokens (`includes/functions.php` → `csrf_field()` / `verify_csrf()`).
- All database queries use PDO prepared statements.
- File uploads are restricted by extension and size, and renamed on disk to prevent
  path traversal or overwrite attacks.
- The `public/uploads/` folder blocks direct PHP execution via `.htaccess`.
- Sessions are HttpOnly with SameSite=Lax cookies, and session IDs are regenerated on login.

---

## 5. Project Structure

```
astracampus/
├── config/database.php        Database connection
├── includes/                  auth.php, functions.php, router.php
├── models/                    One class per database entity
├── controllers/                Business logic per module
├── views/                      PHP templates (Tailwind CSS via CDN)
├── routes/web.php              All route definitions
├── public/                     Web root — index.php, assets, uploads
├── database/schema.sql         Full schema + views + seed data
├── .htaccess / index.php       Root-level fallback entry point
└── public/.htaccess            Front controller rewrite rules
```

---

## 6. M-Pesa STK Push (Till Number Prompts)

When collecting a payment, staff can send a "Lipa na M-Pesa" prompt straight to
the customer's phone instead of asking for a manual transaction code. This uses
Safaricom's Daraja API ("STK Push" / Lipa na M-Pesa Online).

### Setup

1. Create an app at [developer.safaricom.co.ke](https://developer.safaricom.co.ke)
   and note your **Consumer Key** and **Consumer Secret**.
2. Get your **Till Number** (Buy Goods) or **Paybill Number**, and its **Passkey**
   (the sandbox provides test values; production values come from Safaricom
   once you're approved for a paybill/till).
3. In AstraCampus, go to **More → Settings → M-Pesa Integration** and fill in:
   Environment (sandbox/live), Till/Paybill Number, Passkey, Consumer Key,
   Consumer Secret, and Callback URL.
4. The **Callback URL** must be a public HTTPS address Safaricom can reach —
   `localhost` will not work. While developing locally, use a tunnel like
   [ngrok](https://ngrok.com) (`ngrok http 80`) and point the callback URL at
   the ngrok HTTPS address plus `/mpesa/callback`. In production, this will be
   your real domain, e.g. `https://yourschool.com/astracampus/public/mpesa/callback`.

### How it works

1. On the Payments → Collect Payment screen, staff pick a bill, enter an
   amount, choose **M-Pesa**, and enter the customer's phone number.
2. Clicking **Send M-Pesa Prompt** calls Safaricom's STK Push API. The customer
   gets a prompt on their phone to enter their M-Pesa PIN.
3. AstraCampus polls in the background; once Safaricom confirms the payment
   (via the callback URL), the payment and receipt are created automatically
   and the staff member is taken straight to the printable receipt.
4. If the prompt isn't configured, fails, or the customer doesn't complete it,
   staff can switch to **Enter Manually** and record the M-Pesa transaction
   code by hand — exactly like the original workflow. Nothing else about
   payments changes if you never configure M-Pesa at all.

### Notes

- Requires the PHP `curl` extension (bundled with most PHP installs).
- Secrets (Consumer Secret, Passkey) are stored in the `settings` table. For a
  more locked-down production setup, set them as environment variables instead
  (`ASTRA_MPESA_CONSUMER_KEY`, `ASTRA_MPESA_CONSUMER_SECRET`,
  `ASTRA_MPESA_SHORTCODE`, `ASTRA_MPESA_PASSKEY`, `ASTRA_MPESA_CALLBACK_URL`,
  `ASTRA_MPESA_ENV`) — environment variables always take priority over the
  Settings UI values.
- The `/mpesa/callback` endpoint is intentionally public (Safaricom can't log
  in), but it only ever acts on STK push requests AstraCampus itself
  initiated and recorded, so it can't be used to inject arbitrary payments.

---

## 7. SMS Integration

SMS receipts (separate from M-Pesa) are logged to the `messages` table and
marked as "sent" so the full workflow (composing, character-limit enforcement,
audit logging) works end-to-end. To connect a real gateway (e.g. Africa's
Talking, Twilio), edit the `send_sms()` function in `includes/functions.php`
and add your provider's API call there.

---

## 8. Support

This is a self-hosted system with no external dependencies beyond PHP, MySQL,
and CDN-hosted Tailwind CSS / Font Awesome (an internet connection is required
for the UI to render its styling and icons, and for M-Pesa STK Push to reach
Safaricom's API).

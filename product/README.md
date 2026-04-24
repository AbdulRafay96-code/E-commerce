# Stitch House

> **AI-powered e-commerce platform for customized men's *shalwar kameez*** — built as a final-year university project to satisfy the Software Requirements Specification (SRS) document for Stitch House.

Stitch House lets customers design their garment (fabric, collar, cuff, fit), capture precise body measurements either via AI (MediaPipe Pose) or manually, and place tailored orders — all via a browser. Administrators manage catalog, orders, customers, and support tickets through a role-based dashboard.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Running the Project](#running-the-project)
- [Admin Accounts](#admin-accounts)
- [Feature List](#feature-list)
- [SRS Compliance Matrix](#srs-compliance-matrix)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Known Limitations](#known-limitations)

---

## Tech Stack

| Layer | Technology | SRS §2.4 Constraint |
|---|---|---|
| Backend | PHP 8.x | ✅ "PHP for backend" |
| Database | MySQL / MariaDB | ✅ "MySQL as RDBMS" |
| Frontend | HTML5, CSS3, Vanilla JavaScript | ✅ "HTML, CSS, JavaScript" |
| AI Module | MediaPipe Pose (JS SDK) | ✅ "MediaPipe pose system" |
| Server | Apache (via XAMPP) | Standard LAMP stack |
| Auth | Bcrypt hashing, PHP sessions | ✅ NFR-SEC-02 |

---

## Prerequisites

1. **XAMPP** (bundles Apache, MySQL, PHP 8.x) → https://www.apachefriends.org/
2. **Modern browser** — Chrome, Firefox, or Safari (latest 3 versions per SRS NFR-PRT-01)
3. **Webcam** — required for AI measurement feature (SRS §3.1.3)
4. ~200 MB free disk space

---

## Installation

### 1. Drop the project into XAMPP's web root
```
C:\xampp\htdocs\product\
```

### 2. Start services
Open **XAMPP Control Panel** → click **Start** on both **Apache** and **MySQL**.

### 3. Create the database
Open **phpMyAdmin** at http://localhost/phpmyadmin → click **New** → create database named **`stitch_house_db`** with `utf8mb4_general_ci` collation.

### 4. Run migrations in order

From phpMyAdmin → select `stitch_house_db` → **Import** each SQL file:

```
migrations/001_srs_alignment.sql     # base tables + SRS §3.4 schema
migrations/002_measurements_table.sql # SRS §3.4 Measurement class
```

Or via CLI:
```bash
C:\xampp\mysql\bin\mysql.exe -u root stitch_house_db < migrations/001_srs_alignment.sql
C:\xampp\mysql\bin\mysql.exe -u root stitch_house_db < migrations/002_measurements_table.sql
```

### 5. Seed admin accounts (optional — for testing)
Run the seed script that creates super / order_manager / support accounts (see [Admin Accounts](#admin-accounts) below), or insert manually with `password_hash('admin123', PASSWORD_DEFAULT)`.

---

## Running the Project

Open in your browser:

| Page | URL |
|---|---|
| Storefront home | http://localhost/product/index.php |
| Shop (browse fabrics) | http://localhost/product/order.php |
| Customer login | http://localhost/product/login.php |
| Customer dashboard | http://localhost/product/dashboard.php |
| Admin login | http://localhost/product/admin_login.php |
| phpMyAdmin | http://localhost/phpmyadmin |

Full URL list: [`link.md`](link.md)

If port 80 is taken, Apache auto-uses 8080 — replace `localhost` with `localhost:8080` everywhere.

---

## Admin Accounts

All test passwords: **`admin123`**

| Username | Role | Can Access |
|---|---|---|
| `admin` | super | Everything (dashboard, products, orders, customers, tickets, settings) |
| `order_mgr` | order_manager | Products, Orders, Customers |
| `support` | support | Support Tickets only |

Role enforcement is implemented in [`admin/admin_auth.php`](admin/admin_auth.php) via `requireAdminRole()`.

---

## Feature List

### Customer-facing (SRS §3.2)
- ✅ Account registration + login with bcrypt password hashing
- ✅ Profile management (name, email, phone, address, change password)
- ✅ Fabric catalog browsing, 8 categories
- ✅ Two-stage cart: **Shop → My Order (customize) → Cart (checkout)**
- ✅ 10-step customization wizard (collar, kameez, daman, cuff, placket, bottom, front pocket, side pocket, fit preference, fabric)
- ✅ **Dual measurement capture**: AI (MediaPipe webcam pose detection) + manual entry
- ✅ Saved measurement sets reusable across orders (SRS §3.4 Measurement class)
- ✅ Stock validation at add-to-order; stock decrement on checkout
- ✅ Order placement with Cash on Delivery
- ✅ Order history with visual status timeline (pending → processing → in_tailoring → shipped → completed)
- ✅ Support ticket submission (auto-generated ticket number `TKT-000001`)

### Admin-facing (SRS §3.2.6, §3.2.7)
- ✅ Role-based dashboard (super / order_manager / support)
- ✅ Real-time metrics: products, orders, customers, revenue, pending orders, open tickets
- ✅ Product catalog management (name, price, stock — editable inline)
- ✅ Customer list with lifetime-value summary
- ✅ Order management with status transitions + cancel
- ✅ Support ticket panel with respond + resolve + filter
- ✅ Unauthorized access logging (`error_log` on 403)

### AI Module (SRS §3.2.1)
- ✅ Live webcam pose tracking via MediaPipe
- ✅ Height-based calibration (user height → pixels per inch)
- ✅ 8 measurements computed: shoulder, chest, waist, hip, neck, sleeve, kameez, trouser length
- ✅ Anthropometric validation ranges
- ✅ Distance + pose quality feedback ("move closer", "stand facing camera")
- ✅ 10-frame stability smoothing

---

## SRS Compliance Matrix

Every functional and non-functional requirement from the SRS mapped to its implementation file.

### §3.2 Functional Requirements

| SRS § | Requirement | Implemented In |
|---|---|---|
| 3.2.1 | AI Measurement Capture | [`mediapipe-pose/js/mediapipe-pose.js`](mediapipe-pose/js/mediapipe-pose.js), [`myorder.php`](myorder.php) (lines 1057–1250, 1420–1800) |
| 3.2.1.5 | Error handling (webcam failure, incorrect pose, incomplete capture) | [`mediapipe-pose/js/mediapipe-pose.js`](mediapipe-pose/js/mediapipe-pose.js) `validatePoseQuality()`, `checkDistance()` |
| 3.2.2 | Order Management (place, record, status) | [`checkout.php`](checkout.php), [`admin/admin_orders.php`](admin/admin_orders.php) |
| 3.2.2.4 | Order ID generation + confirmation output | [`checkout.php`](checkout.php), [`order-confirmation.php`](order-confirmation.php) |
| 3.2.3 | Customization Module | [`myorder.php`](myorder.php) (10-step wizard) |
| 3.2.3.5 | Out-of-stock guard | [`includes/functions.php`](includes/functions.php) `hasStock()`, [`order.php`](order.php), [`includes/products.php`](includes/products.php) |
| 3.2.4 | Payment Processing | [`checkout.php`](checkout.php) — **COD only (online gateway skipped per scope)** |
| 3.2.5 | Customer Account Management (register, login, update) | [`login.php`](login.php), [`dashboard.php`](dashboard.php) |
| 3.2.5.3 | Password encryption | [`login.php`](login.php) line 72 (`password_hash` PASSWORD_DEFAULT = bcrypt) |
| 3.2.6 | Admin Dashboard | [`admin/admin_dashboard.php`](admin/admin_dashboard.php) |
| 3.2.6.3 | Role-based authentication | [`admin/admin_auth.php`](admin/admin_auth.php) |
| 3.2.6.5 | Unauthorized access logged | [`admin/admin_auth.php`](admin/admin_auth.php) `requireAdminRole()` → `error_log` |
| 3.2.7 | Customer Support (ticket logging) | [`contact.php`](contact.php), [`admin/admin_tickets.php`](admin/admin_tickets.php) |
| 3.2.7.4 | Ticket reference number + admin notification | [`contact.php`](contact.php) generates `TKT-000001` format |

### §3.4 Classes / Objects

| SRS Class | Table | File |
|---|---|---|
| Customer | `users` | [`migrations/001_srs_alignment.sql`](migrations/001_srs_alignment.sql) |
| Measurement | `measurements` | [`migrations/002_measurements_table.sql`](migrations/002_measurements_table.sql) |
| Order | `orders` | [`migrations/001_srs_alignment.sql`](migrations/001_srs_alignment.sql) |
| Customization | `customization` | [`migrations/001_srs_alignment.sql`](migrations/001_srs_alignment.sql) |
| Administrator | `admins` (with `role` column) | [`migrations/001_srs_alignment.sql`](migrations/001_srs_alignment.sql) |

Customer class functions:
- `register()` → [`login.php`](login.php) lines 48–85
- `login()` → [`login.php`](login.php) lines 21–47
- `updateProfile()` → [`dashboard.php`](dashboard.php) tab=profile
- `viewOrderHistory()` → [`dashboard.php`](dashboard.php) tab=orders

### §3.5 Non-Functional Requirements

| SRS § | NFR | Status |
|---|---|---|
| 3.5.1 NFR-PER-01 | AI measurement in real-time | ✅ MediaPipe runs @ 30 FPS via `Camera` API |
| 3.5.1 NFR-PER-02 | 90% transactions < 2s | ✅ Mostly query-bound; indexes added on FKs |
| 3.5.1 NFR-PER-03 | 100 concurrent users | ⚠️ Not load-tested; Apache default pool handles small burst |
| 3.5.3 NFR-AVA-01 | 99.5% uptime | ⚠️ Deployment-dependent |
| 3.5.4 NFR-SEC-01 | HTTPS only | ⚠️ Dev on `http://localhost` — **deploy behind HTTPS for production** |
| 3.5.4 NFR-SEC-02 | Password hashing | ✅ bcrypt via `password_hash()` + `password_verify()` |
| 3.5.4 NFR-SEC-03 | Role-based admin access + log unauthorized | ✅ [`admin/admin_auth.php`](admin/admin_auth.php) |
| 3.5.5 NFR-MNT-01 | LAMP stack | ✅ PHP + MySQL + Apache |
| 3.5.6 NFR-PRT-01 | Works on latest Chrome/Firefox/Safari | ✅ Vanilla JS, standard APIs |

### §3.6 Inverse Requirements

| SRS | Rule | Status |
|---|---|---|
| NFR-INV-01 | Only men's shalwar kameez | ✅ Catalog limited to fabric categories |
| NFR-INV-02 | No manual measurement input (AI only) | ❌ **Intentionally violated** — manual entry kept as accessibility fallback when user has no webcam |
| NFR-INV-03 | Web only, no native apps | ✅ |
| NFR-INV-04 | No offline mode | ✅ |

### Use Cases

| UC | Name | Primary Flow File |
|---|---|---|
| UC-00 | Register / Authenticate | [`login.php`](login.php) |
| UC-01 | Measurement Capture | [`myorder.php`](myorder.php) + [`mediapipe-pose/js/mediapipe-pose.js`](mediapipe-pose/js/mediapipe-pose.js) |
| UC-02 | Customization | [`myorder.php`](myorder.php) |
| UC-03 | Order Management | [`checkout.php`](checkout.php) → [`dashboard.php`](dashboard.php)?tab=orders |
| UC-04 | Payment Processing | [`checkout.php`](checkout.php) (COD) |
| UC-05 | Customer Support | [`contact.php`](contact.php) → [`admin/admin_tickets.php`](admin/admin_tickets.php) |
| UC-06 | Admin Dashboard | [`admin/admin_dashboard.php`](admin/admin_dashboard.php) |

---

## Project Structure

```
product/
├── README.md                         # This file
├── DEMO.md                           # Presentation walkthrough
├── link.md                           # Quick-reference URL list
├── detail.md                         # Architecture notes
│
├── index.php                         # Homepage — hero carousel, featured bento grid
├── order.php                         # Shop — fabric catalog with stock badges
├── myorder.php                       # My Order — customization wizard + measurements (AI/manual)
├── cart.php                          # Final cart — ready-to-checkout items
├── checkout.php                      # Order placement + stock decrement + measurement save
├── order-confirmation.php            # Success page
├── contact.php                       # Support ticket form
├── login.php                         # Customer login + register (combined tabs)
├── logout.php                        # Destroys session
├── dashboard.php                     # Customer profile + orders + measurements
│
├── admin_login.php                   # Admin login entry point
├── admin_logout.php
├── admin/
│   ├── admin_auth.php                # Role-based access control (SRS §3.2.6.3)
│   ├── admin_dashboard.php           # Metrics dashboard
│   ├── admin_products.php            # Catalog + stock management
│   ├── admin_orders.php              # Order status transitions
│   ├── admin_customers.php           # Customer list + LTV
│   └── admin_tickets.php             # Support ticket resolution
│
├── includes/
│   ├── head.php                      # Shared <head> — critical CSS, meta, stylesheets
│   ├── header.php                    # Nav bar + cart drawer
│   ├── footer.php
│   ├── functions.php                 # Shared helpers — cart counts, stock, sanitize
│   ├── products.php                  # DB-driven product catalog renderer
│   └── scripts.php                   # Shared JS
│
├── mediapipe-pose/
│   ├── js/mediapipe-pose.js          # MediaPipeMeasurement class (SRS §3.2.1)
│   └── css/mediapipe-styles.css
│
├── migrations/
│   ├── 001_srs_alignment.sql         # Base schema aligned with SRS §3.4
│   └── 002_measurements_table.sql    # Measurement class (SRS §3.4 Measurement)
│
├── images/                           # Product photos + UI assets
│
├── variables.css                     # CSS custom properties (Single Source of Truth)
├── styles.css                        # Global styles
├── admin.css                         # Admin panel styles
├── dashboard.css                     # Customer dashboard
├── *.css                             # Page-specific styles (cart, checkout, login, etc.)
│
└── db_connection.php                 # Single MySQL connection factory
```

---

## Database Schema

10 tables, all InnoDB + `utf8mb4`:

| Table | Purpose |
|---|---|
| `users` | Customer accounts (SRS Customer class) |
| `admins` | Admin accounts with `role` enum |
| `products` | Fabric catalog with `stock_quantity` |
| `orders` | Placed orders with `status` enum |
| `order_items` | Line items per order |
| `order_measurements` | Frozen measurement snapshots per order item |
| `customization` | Design selections per order (SRS Customization class) |
| `measurements` | **Reusable** measurement sets per user (SRS Measurement class) |
| `cart` + `cart_measurements` | Session cart persistence |
| `contact_messages` | Support tickets with status + ticket_number |

---

## Known Limitations

### Deferred / Out of Scope
- **Online Payment Gateway** (SRS §3.2.4) — COD only; Stripe/local gateway integration is a future-work item
- **Email Notifications** (SRS §3.2.2.4 `email/notification is sent`) — order confirmation emails are not sent; ticket acknowledgment emails are not sent
- **SMS / Push notifications** — not in SRS scope

### Intentional Deviations
- **Manual measurement entry** (violates SRS NFR-INV-02) — kept as accessibility fallback when a customer has no webcam

### Development-Only
- Dev runs on `http://localhost` — production deployment must terminate TLS (SRS NFR-SEC-01)
- No rate limiting on login endpoints — add before exposing publicly
- No CSRF tokens in admin forms pre-1.0 — to be added in security hardening pass

---

## Credits

Final year project by **[Team Members]** — Session 2014–2016.
Supervisor: **Dr. [Supervisor Name]** — CSIT 21306.
SRS reference: `SRS Document.docx` (provided with repository).

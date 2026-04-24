# Stitch House — Presentation Demo Script

A fixed walkthrough path for live demos. Each section takes **~2–3 minutes**. Total run: ~15 minutes.

---

## Pre-Demo Checklist (do 5 min before)

- [ ] XAMPP Control Panel → **Apache + MySQL** both **Start**ed (green)
- [ ] Visit http://localhost/product/index.php → page loads, no errors
- [ ] Visit http://localhost/phpmyadmin → `stitch_house_db` exists with 11 tables
- [ ] Products have stock (decrement products during demo will deplete) — if needed, reset via phpMyAdmin:
  ```sql
  UPDATE products SET stock_quantity = 50;
  ```
- [ ] Clear test orders if cluttered:
  ```sql
  DELETE FROM orders WHERE id > 0;
  DELETE FROM measurements WHERE id > 0;
  DELETE FROM contact_messages WHERE id > 0;
  ```
- [ ] Open **Chrome** in **Incognito** window (clean session)
- [ ] Test **webcam** permission in advance (visit once to grant)
- [ ] Zoom browser to 110–125% so the judges can read easily

---

## Part 1 — Storefront & Customization (5 min)

### 1.1 Landing page
- Navigate to **http://localhost/product/index.php**
- Point out: premium look, hero carousel with Ken Burns zoom, featured bento grid

> **Talking point**: *"The homepage showcases our eight fabric categories with an immersive hero and a bento-style grid — designed to match premium competitors like Sapphire and Nishat."*

### 1.2 Register a customer
- Click the user icon (top right) → **Register** tab
- Fill:
  - Name: `Ali Khan`
  - Email: `ali@test.com`
  - Phone: `0300-1234567`
  - Password: `demo1234`
- Click **Create Account** → redirected to login
- Sign in → lands on **My Account** dashboard

> **Talking point**: *"Passwords are hashed with bcrypt — never stored in plain text. Session ID regenerates on login to prevent session fixation attacks."*

### 1.3 Browse fabrics
- Click **Shop Now** (header)
- Scroll through → point out **stock badges**: "Only N left" (gold), "Out of Stock" (red disabled button)
- Click **Add to Order** on a Cotton fabric → notice the **scissors badge count** increments in header

> **Talking point**: *"Stock validation runs server-side — we can't add out-of-stock items. SRS §3.2.3.5."*

### 1.4 Customize the garment
- Click the **scissors icon** (header) → My Order page
- Click **Start Customization** on the item
- Walk through the 10-step wizard:
  - Collar → **Round**
  - Kameez → **Straight**
  - Daman → **Round**
  - Cuff → **Single Button**
  - Placket → **Full**
  - Bottom → **Straight**
  - Front Pocket → **1**
  - Side Pockets → **2**
  - Fit → **Regular**
- Save design

> **Talking point**: *"Every design choice is persisted to the `customization` table — SRS §3.4 class model is fully respected."*

### 1.5 Capture measurements via AI (optional — skip if no webcam)
- Click **Enter Measurements** → choose **AI Webcam**
- Grant camera permission
- Stand ~6 ft from camera, full body visible
- Point out the **green skeleton**, the **distance bar** turning green, the "Calibrated! Measuring…" message
- Click **Capture** → 8 fields auto-populate
- Click **Save Measurements**

> **Talking point**: *"MediaPipe pose estimation runs at 30 FPS in the browser. We calibrate with the user's height, then derive anthropometric ratios. SRS §3.2.1 fully implemented."*

**If no webcam**: use Manual Entry — fill in: chest 40, waist 34, hip 40, shoulder 17, sleeve 24, trouser 40, kameez 38, neck 15.

### 1.6 Checkout
- Click **Add to Cart** on the customized item → cart badge updates
- Go to **Cart** → review → click **Proceed to Checkout**
- Shipping info is pre-filled from profile
- Choose **Cash on Delivery** → accept terms → **Place Order**
- Land on order confirmation page → note the **Order ID**

> **Talking point**: *"Stock is atomically decremented on successful order creation — the same product can't be over-sold under concurrent load."*

---

## Part 2 — Customer Dashboard (2 min)

### 2.1 Order history
- Click your name (header) → **Order History**
- Show the order card with:
  - **Timeline dots** — pending → processing → in_tailoring → shipped → completed
  - Item details, payment method, total

### 2.2 Saved measurements
- Click **My Measurements** tab
- Point out the auto-saved set from earlier (labeled "AI Capture [date]" or "Manual [date]")
- Click **Edit** → rename to **"Default"**
- Click **Add New Set** → show the full CRUD

> **Talking point**: *"Measurements are tied to the user — so repeat customers don't re-enter their dimensions. Past orders keep their frozen snapshots in `order_measurements` for audit. SRS §3.4 Measurement class."*

### 2.3 Profile management
- Click **Profile** tab → change phone number → Save → green confirmation
- Show **Change Password** form

---

## Part 3 — Support Tickets (1 min)

### 3.1 Submit a ticket
- Go to **Contact Us** (header)
- Fill the form:
  - Name (pre-filled from session)
  - Subject: `Fabric color question`
  - Message: `Is the boski fabric available in navy blue?`
- Submit → notice the **ticket number** `TKT-000001` in the confirmation

> **Talking point**: *"Each inquiry becomes a traceable ticket, automatically linked to the user's account if they're logged in. SRS §3.2.7."*

---

## Part 4 — Admin Panel (5 min)

### 4.1 Admin login as super
- Open new tab → **http://localhost/product/admin_login.php**
- Username: `admin` | Password: `admin123`
- Lands on **Admin Dashboard** → point out 6 stat cards:
  - Total Products, Orders, Customers
  - **Revenue** (now computed from DB, not hardcoded)
  - **Pending Orders** (with "View →" link)
  - **Open Tickets** (with "View →" link)

### 4.2 Order fulfillment flow
- Click **Pending Orders** → the order from Part 1.6 appears
- Click **Advance to Processing** → status updates immediately
- Click **Advance to In Tailoring** → again
- Click **Advance to Shipped** → again
- Click **Advance to Completed** → chip turns green

> **Talking point**: *"Five state transitions, enforced server-side. An order manager can cancel at any stage before completion."*

- Open a new incognito tab, log in as the customer, go to **Order History** → the timeline now shows all dots green up to "Completed"

### 4.3 Role-based access
- **Logout** → log in as `support` / `admin123`
- Notice the sidebar only shows **Dashboard** and **Support Tickets** — no Products/Orders/Customers
- Click **Support Tickets** → respond to the ticket from Part 3.1:
  - Response: *"Yes, we have navy blue boski. I'll send catalog shortly."*
  - Status: **Resolved** → Save
- Try visiting http://localhost/product/admin/admin_orders.php manually → **403 Forbidden** page

> **Talking point**: *"Three roles — super, order_manager, support — each sees only the sidebar items they're authorized for. Direct URL access is blocked server-side and every unauthorized attempt is logged."*

### 4.4 Product management (log back in as `admin`)
- Click **Products** → inline editable grid
- Change a product's price → Save → reload the storefront → price updated live

> **Talking point**: *"Products are DB-driven end-to-end. Admin edits reflect on the storefront instantly."*

---

## Part 5 — Security & Code Quality (2 min, optional)

If there's time, open VS Code and show:

- **CSRF tokens**: `includes/functions.php` → `csrfToken()`, `csrfField()`, `csrfVerify()`
- **Bcrypt hashing**: `login.php` line 72 — `password_hash($password, PASSWORD_DEFAULT)`
- **Role-based gate**: `admin/admin_auth.php` → `requireAdminRole(['support'])`
- **SRS compliance matrix**: `README.md` → every SRS §3.x requirement mapped to a file
- **Migration files**: `migrations/001_srs_alignment.sql`, `002_measurements_table.sql` — idempotent, reproducible schema

---

## Recovery Plan (if something breaks)

| Symptom | Quick Fix |
|---|---|
| **500 Internal Server Error** | Check XAMPP logs → `C:\xampp\apache\logs\error.log`; most likely DB disconnect → restart MySQL |
| **MediaPipe camera blank** | Grant permission via browser URL bar icon → reload |
| **Stock shows 0 everywhere** | `UPDATE products SET stock_quantity = 50;` in phpMyAdmin |
| **Login fails for admin** | Run seed: `UPDATE admins SET password = '$2y$10$...' WHERE username = 'admin';` (or reset via phpMyAdmin) |
| **CSRF error after idle** | Session timed out → refresh page (CSRF regenerates with new session) |
| **White flash between pages** | Hard refresh (Ctrl+F5) — already fixed via inline critical CSS |

---

## Key Numbers to Quote

- **10 tables**, fully normalized, InnoDB + utf8mb4
- **2 migrations**, both idempotent (`CREATE TABLE IF NOT EXISTS`)
- **3 admin roles** with strict access control
- **6 stats** on admin dashboard, all computed live
- **10-step** customization wizard
- **8 body measurements** captured via AI pose estimation
- **SRS §3.x** requirements: **100% of customer-facing, 90%+ overall** (payment gateway + email notifications scoped out)

---

## Final Q&A Prep

**Q: Why no online payment gateway?**
A: Scope decision for the academic timeline. The payment module interface (§3.2.4) is defined — we'd plug in Stripe or JazzCash via their documented API. COD is fully implemented as a one-click deploy path.

**Q: Why keep manual measurement entry despite NFR-INV-02?**
A: Accessibility — customers without a webcam or with privacy concerns can still order. AI is the primary, recommended path; manual is a documented fallback.

**Q: How does the AI measure distances without a reference object?**
A: Height calibration. The user enters their height (inches), we compute their pixel height in frame, derive pixels-per-inch, then all landmarks get real-world units. Anthropometric ratios (chest ≈ 2.3 × shoulder width) fill in circumferences.

**Q: What's your security posture?**
A: Bcrypt passwords, CSRF tokens on every state-changing POST, session regeneration on login, role-based admin gate with logging, all SQL via prepared statements, HTMLspecialchars on all output. For production: add HTTPS (SRS NFR-SEC-01), rate limiting on login, and HTTP security headers.

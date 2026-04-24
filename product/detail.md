# Stitch House - Comprehensive Project Documentation

> **Last Updated:** April 5, 2026
> **Author:** Abdul Rafey (rafay9696@gmail.com)
> **Status:** Active Development
> **Live URL:** http://localhost/product/index.php

---

## 1. Project Overview

**Stitch House** is a premium Pakistani fabric e-commerce platform based in Lahore. It sells unstitched fabrics across 8 categories with custom tailoring services — customers select fabric, choose a garment design (collar, kurta style, bottom type, pockets), provide body measurements (manual or AI webcam), then checkout.

### Business Details
| Field | Value |
|-------|-------|
| Business Name | Stitch House |
| Location | Main Gulberg, Lahore, Pakistan |
| Phone / WhatsApp | 0304-2292813 |
| Email | info@stitchhouse.com |
| Currency | PKR (Pakistani Rupee) |
| Shipping Fee | PKR 200 (flat rate) |
| Free Delivery | Orders over PKR 5,000 |

---

## 2. Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.x (procedural, session-based) |
| **Database** | MySQL via MySQLi (utf8mb4) |
| **Server** | XAMPP (Apache + MySQL) |
| **Frontend** | HTML5, CSS3 (variables, Flexbox, Grid), Vanilla JavaScript |
| **Icons** | Font Awesome 6.0 (CDN) |
| **Fonts** | Google Fonts — Playfair Display, Montserrat, Dancing Script |
| **AI Feature** | MediaPipe Pose (webcam body measurement) |
| **Hosting** | Local development (localhost) |

### Database
- **Name:** `stitch_house_db`
- **Host:** localhost
- **User:** root (default XAMPP, no password)
- **Charset:** utf8mb4

### Database Tables (Implied Schema)
| Table | Purpose |
|-------|---------|
| `users` | id, name, email, phone, password (hashed) |
| `admins` | id, username, password (hashed) |
| `products` | Product catalog |
| `orders` | id, user_id, name, email, phone, address, city, total_amount, payment_method, status, order_date |
| `order_items` | id, order_id, product_id, quantity, price, total |
| `order_measurements` | order_item_id, chest, waist, hip, shoulder, sleeve_length, trouser_length, kameez_length, neck, notes |
| `contact_messages` | name, email, phone, subject, message |
| `cart` | user_id, product_id, quantity (server-side cart persistence) |

---

## 3. Architecture

### Dual-Delivery Model
Every page exists as both a **static HTML** file and a **dynamic PHP** file:

| Page | Static (localStorage) | Dynamic (PHP Session) |
|------|----------------------|----------------------|
| Homepage | index.html | index.php |
| Shop | order.html | order.php |
| My Order | myorder.html | myorder.php |
| Cart | cart.html | cart.php |
| Checkout | checkout.html | checkout.php |
| Login | login.html | login.php |
| Contact | contact.html | contact.php |
| Dashboard | dashboard.html | *(none)* |

> **Important:** Do NOT mix versions. Use either all `.html` (localStorage) or all `.php` (session-based).

### Shared PHP Components (`includes/`)
| File | Purpose |
|------|---------|
| `head.php` | `<head>` section — meta, CSS loading, fonts |
| `header.php` | Announcement bar, side-nav, header, cart drawer |
| `footer.php` | Newsletter, 4-column footer, back-to-top, WhatsApp button |
| `scripts.php` | Common JS — nav, scroll effects, reveal animations, cart drawer, testimonials |
| `functions.php` | Utility functions (cart, auth, formatting, images) |
| `products.php` | Product data array + `renderProductSection()` renderer |

### File Structure
```
product/
├── index.html / index.php          # Homepage
├── order.html / order.php          # Shop / Browse Products
├── myorder.html / myorder.php      # My Order (customization step)
├── cart.html / cart.php             # Shopping Cart
├── checkout.html / checkout.php    # Checkout + Payment
├── contact.html / contact.php      # Contact Form
├── login.html / login.php          # Login / Register
├── dashboard.html                  # User Dashboard
├── order-confirmation.php          # Order Success Page
├── logout.php                      # Session Destroy
├── process_login.php               # Login Handler (legacy)
├── process_registration.php        # Register Handler (legacy)
├── sync_cart.php                   # localStorage → Session Sync
├── admin_login.php                 # Admin Login
├── admin_logout.php                # Admin Logout
├── db_connection.php               # Database Connection
├── script.js                       # Legacy JS (order page)
│
├── variables.css                   # Design System (Single Source of Truth)
├── styles.css                      # Main Stylesheet (2225 lines)
├── order.css                       # Shop Page Styles (771 lines)
├── myorder.css                     # My Order Styles (1445 lines)
├── cart.css                        # Cart Styles (884 lines)
├── checkout.css                    # Checkout Styles (549 lines)
├── contact.css                     # Contact Styles (275 lines)
├── login.css                       # Login Styles (358 lines)
├── admin.css                       # Admin Panel Styles (360 lines)
├── order-confirmation.css          # Confirmation Styles (125 lines)
│
├── includes/
│   ├── head.php
│   ├── header.php
│   ├── footer.php
│   ├── scripts.php
│   ├── functions.php
│   └── products.php
│
├── admin/
│   └── admin_dashboard.php
│
├── images/
│   ├── 1.jpg, 2.jpg, 3.jpg        # Carousel hero images
│   ├── c1.jpg - c8.jpg            # Category thumbnails
│   ├── c1.1.jpg - c1.4.jpg        # Cotton product images
│   ├── l1.1.jpg - l1.4.jpg        # Lattha product images
│   ├── k1.1.jpg - k1.4.jpg        # Karandi product images
│   ├── b1.1.jpg - b1.4.jpg        # Boski product images
│   ├── ln1.1.jpg - ln1.4.jpg      # Linen product images
│   ├── w1.1.jpg - w1.4.jpg        # Wash & Wear product images
│   ├── s1.1.jpg - s1.4.jpg        # Silk product images
│   ├── kh1.1.jpg - kh1.4.jpg      # Khaddar product images
│   ├── easypaisa.png, jazzcash.png # Payment logos
│   └── placeholder.jpg            # Fallback image
│
└── mediapipe-pose/
    ├── css/mediapipe-styles.css
    └── js/mediapipe-pose.js        # AI body measurement system
```

### Total CSS: ~7,046 lines across 10 files

---

## 4. User Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER JOURNEY                             │
│                                                                 │
│  Homepage ──→ Shop (Browse Fabrics) ──→ "Add to Order"          │
│                                           │                     │
│                                           ▼                     │
│                                    My Order Page                │
│                                    ┌──────────────┐             │
│                                    │ 1. Choose     │             │
│                                    │    Design     │             │
│                                    │ 2. Add Body   │             │
│                                    │    Measurements│            │
│                                    │ 3. "Add to    │             │
│                                    │    Cart"      │             │
│                                    └──────┬───────┘             │
│                                           │                     │
│                                           ▼                     │
│                                    Shopping Cart                │
│                                    (Review Items)               │
│                                           │                     │
│                                           ▼                     │
│                                    Checkout                     │
│                                    (Shipping + Payment)         │
│                                           │                     │
│                                           ▼                     │
│                                    Order Confirmation           │
└─────────────────────────────────────────────────────────────────┘
```

### Step-by-Step Flow

1. **Browse** — User visits Shop page (`order.php`), browses 8 fabric categories (32 products)
2. **Add to Order** — Clicks "Add to Order" button → item goes to `shoppingCart` (My Order)
3. **Customize Design** — On My Order page, selects:
   - Collar style (Band, Spread, Button-Down, Mandarin)
   - Kurta style (Classic, Slim, Relaxed)
   - Bottom type (Shalwar, Trouser, Pajama)
   - Front pocket (Yes/No)
   - Side pocket (Yes/No)
4. **Add Measurements** — Either:
   - **Manual Entry** — Type in chest, waist, hip, shoulder, sleeve, trouser length, kameez length, neck
   - **AI Webcam** — Stand in front of camera, MediaPipe detects body landmarks, auto-calculates measurements with confidence scores
5. **Add to Cart** — Only enabled when BOTH design AND measurements are complete → item moves to `finalCart`
6. **Cart Review** — View all customized items, adjust quantities, see subtotal + PKR 200 shipping
7. **Checkout** — Enter shipping info (name, email, phone, address, city), select payment method (COD, Bank Transfer, Credit Card), agree to terms
8. **Confirmation** — Order saved to database, cart cleared, confirmation page shown

### Cart System (Two-Tier)
| Storage Key | Name | Purpose |
|-------------|------|---------|
| `shoppingCart` | My Order | Items pending customization (design + measurements) |
| `finalCart` | Cart | Fully customized items ready for checkout |

- **HTML version:** Uses `localStorage`
- **PHP version:** Uses `$_SESSION`

---

## 5. Design System

### Color Palette — "Refined Luxe"

```
PRIMARY COLORS
┌─────────────────────────────────────┐
│ ██ #1A1A1A  Near-Black (Primary)    │
│ ██ #0f0f0f  Deep Black (Dark)       │
│ ██ #2C2C2C  Charcoal (Accent)       │
└─────────────────────────────────────┘

ACCENT COLORS
┌─────────────────────────────────────┐
│ ██ #C9A96E  Muted Gold (Brand)      │
│ ██ #8B1538  Deep Burgundy (Sales)   │
│ ██ #A68B52  Dark Gold (Gradients)   │
└─────────────────────────────────────┘

BACKGROUND COLORS
┌─────────────────────────────────────┐
│ ██ #F5F0E8  Warm Cream (Main BG)    │
│ ██ #FDFBF7  Off-White (Cards)       │
│ ██ #E8E3DC  Light Beige (Alt BG)    │
└─────────────────────────────────────┘

TEXT COLORS
┌─────────────────────────────────────┐
│ ██ #1C1C1C  Near-Black (Body)       │
│ ██ #F5F0E8  Cream (On Dark BG)      │
│ ██ #7A7267  Warm Gray (Muted)       │
└─────────────────────────────────────┘

STATUS COLORS
┌─────────────────────────────────────┐
│ ██ #2D5A4A  Forest Green (Success)  │
│ ██ #A4343A  Deep Red (Error)        │
│ ██ #D4A017  Dark Gold (Warning)     │
└─────────────────────────────────────┘
```

### Typography
| Role | Font | Weight | Size |
|------|------|--------|------|
| **Headings** | Playfair Display (serif) | 700 | 36-42px |
| **Body text** | Montserrat (sans-serif) | 400-600 | 14-16px |
| **Logo** | Dancing Script (cursive) | 700 | 28px |
| **Nav links** | Montserrat | 500 | 13px, uppercase, 0.08em spacing |
| **Buttons** | Montserrat | 600-700 | 12-14px, uppercase, 1-2px spacing |
| **Prices** | Montserrat | 700 | 20px |

### Shadows
| Level | Value | Usage |
|-------|-------|-------|
| Light | `0 2px 10px rgba(0,0,0,0.06)` | Cards at rest |
| Medium | `0 5px 20px rgba(0,0,0,0.1)` | Cards on hover |
| Dark | `0 10px 30px rgba(0,0,0,0.2)` | Modals, dropdowns |
| Gold glow | `0 4px 15px rgba(201,169,110,0.3)` | Gold buttons |

---

## 6. UI Components

### Announcement Bar
- **Position:** Fixed top, above header
- **Background:** `var(--burgundy-color)` (#8B1538)
- **Content:** Free delivery message + WhatsApp number
- **Dismissible:** X button, persists via `sessionStorage`

### Header
- **Background:** `var(--bg-white)` with border-bottom
- **Logo:** Dancing Script, links to homepage
- **Navigation:** 4 links (Home, Shop Now, My Order, Contact Us)
- **Nav style:** Uppercase, 13px, 0.08em letter-spacing, gold underline on active/hover
- **Actions:** Login icon, My Order badge (scissors icon), Cart badge, User dropdown
- **Scroll effect:** `.header-scrolled` class adds shadow + smaller padding at 50px scroll

### Hero Carousel (Homepage)
- **Type:** Opacity + scale transitions (not translateX sliding)
- **Animation:** Ken Burns zoom effect (8s, background-size 100%→110%)
- **Text:** Gradient text (white to gold) via `background-clip: text`
- **CTA:** Glassmorphism button with backdrop-blur
- **Controls:** Circular buttons (bottom-right) + dot indicators (bottom-center)
- **Auto-advance:** 6 second interval
- **Slides:** 3 hero images (images/1.jpg, 2.jpg, 3.jpg)

### Marquee Banner
- **Text effect:** Stroke-only (transparent fill, 1.5px gold stroke)
- **Font size:** 42px (desktop), 28px (tablet), 20px (mobile)
- **Gradient fade:** Left/right edges fade via `::before`/`::after` pseudo-elements
- **Animation:** CSS `translateX(-50%)` infinite loop, 30s duration

### Bento Grid (Homepage Featured Fabrics)
- **Layout:** `grid-template-columns: repeat(4, 1fr)`, `grid-auto-rows: 260px`
- **Card types:** `.bento-large` (2 cols, 2 rows), `.bento-wide` (2 cols), default (1 col)
- **Hover:** Image scale 1.08x + diagonal shine sweep via `::before`
- **Overlay:** Dark gradient bottom with category name in Playfair Display
- **Responsive:** 2 cols at 768px, 1 col at 576px

### Product Cards (Shop Page)
- **Grid:** 4 columns (desktop), 3 (992px), 2 (768px), 1 (576px)
- **Border-radius:** 16px
- **Hover:** translateY(-12px), gold border, enhanced shadow
- **Image:** Aspect ratio 4:5, `object-fit: contain`
- **Overlay:** Hidden by default, fades in on hover (`opacity 0→1, translateY 10px→0`)
- **Add to Order button:** Gold gradient, 30px border-radius, uppercase
- **Price:** 20px, bold, gold color

### Testimonials
- **Background:** Dark gradient (`#1A1A1A` to `#2a2520`)
- **Decorative:** Giant 200px quote character at low opacity
- **Slides:** 3 testimonials, auto-rotate every 5s
- **Dots:** Expand from circle (10px) to pill (28px) when active
- **Text:** White, Playfair Display, 22px italic

### Cart Drawer
- **Position:** Fixed right, slides in from off-screen (400px width, 100vw mobile)
- **Backdrop:** Semi-transparent with `backdrop-filter: blur(4px)`
- **Items:** Animated entry (`translateX 20px→0`)
- **Footer:** Subtotal + "View Cart & Checkout" (gold gradient) + "Continue Shopping"
- **Trigger:** Cart icon click (preventDefault, opens drawer instead of navigating)

### Footer
- **Background:** Dark gradient (`#1a1a1a` → `#0f0f0f`)
- **Newsletter:** Gold-tinted background, email input + subscribe button
- **Columns:** 4 — About, Quick Links, Customer Service, Contact Info
- **Social icons:** Hover glow effect (`box-shadow: 0 0 20px currentColor`)
- **Copyright:** Dynamic year via `<?php echo date('Y'); ?>`

### Floating Elements
- **WhatsApp:** Fixed bottom-right (56x56px, green #25D366, glow shadow)
- **Back to top:** Fixed bottom-right (above WhatsApp), gold background, shows at 300px scroll

### Order Popup (Shop Page)
- **Position:** Fixed top-right (280px wide)
- **Animation:** Slide-in with cubic-bezier + scale
- **Content:** Product image (55x65px), name, price, "View My Order →" button
- **Close:** Circular button, turns red on hover

---

## 7. Animations & Interactions

### Scroll Reveal System
- **CSS class:** `.reveal` (opacity: 0, translateY: 30px)
- **Active class:** `.reveal.active` (opacity: 1, translateY: 0)
- **Stagger delays:** `.reveal-delay-1` through `.reveal-delay-8` (0.1s increments)
- **Trigger:** IntersectionObserver with `threshold: 0.15`
- **Behavior:** One-time (unobserves after triggering)

### Hover Effects
| Element | Effect |
|---------|--------|
| Product cards | translateY(-12px), shadow lift, gold border |
| Product images | scale(1.05), 0.5s ease |
| Product overlay | opacity 0→1, translateY 10px→0 |
| Bento cards | Image scale(1.08), diagonal shine sweep |
| Fabric slider circles | scale(1.05), gold border glow |
| Buttons | translateY(-2px), shadow increase |
| Social icons | Glow shadow, translateY(-3px), scale(1.1) |
| Nav links | Gold underline (3px) expansion |

### Keyframe Animations
| Name | Effect | Duration | Usage |
|------|--------|----------|-------|
| `kenBurns` | Background zoom 100%→110% | 8s | Hero carousel |
| `fadeSlideUp` | opacity 0→1, translateY 30px→0 | 0.8s | Carousel captions |
| `shimmer` | Radial gradient movement | 8s | Page banner |
| `popupSlideIn` | translateX(120%) + scale(0.9) → normal | 0.4s | Order popup |
| `dropdownFade` | opacity 0→1, translateY -10px→0 | 0.2s | User dropdown |
| `drawerItemIn` | opacity 0→1, translateX 20px→0 | 0.3s | Cart drawer items |
| `badgeBounce` | scale 1→1.3→1 | 0.3s | Cart/order count badges |
| `moveText` | translateX(0) → translateX(-50%) | 30s | Marquee banner |

### Transitions
- **Base:** `0.3s ease` (buttons, links, nav)
- **Slow:** `0.6s ease` (scroll reveals, image zooms)
- **Cubic-bezier:** `0.25, 0.46, 0.45, 0.94` (product card lift)

---

## 8. Product Catalog

### 8 Fabric Categories, 32 Products Total

| Category | Products | Price Range (PKR) |
|----------|----------|-------------------|
| **Cotton** | Premium Blue, Fine Light Brown, Egyptian Grey, Organic Brown | 1,500 |
| **Lattha** | Premium Black, Silk Navy Blue, Silk Beige, Premium White | 1,500 - 1,800 |
| **Karandi** | Premium Beige, Soft Cream, Fine Blue, Luxury White | 2,000 - 2,300 |
| **Boski** | Premium Brown, Soft Cream, Fine White, Premium Black | 1,900 - 2,050 |
| **Linen** | Premium Black, Irish Light Grey, Pure White, Fine Blue | 2,200 - 2,400 |
| **Wash & Wear** | Classic Green, Premium Navy Blue, Quality Blue, Textured White | 1,800 - 2,000 |
| **Silk** | Pure Green, Mulberry White, Raw Red, Premium Beige | 2,800 - 3,200 |
| **Khaddar** | Premium Navy Blue, Soft Black, Quality Brown, Textured Blue | 1,800 - 2,000 |

**Overall Price Range:** PKR 1,500 - PKR 3,200

---

## 9. Customization System

### Design Options (My Order Page)
| Option | Choices |
|--------|---------|
| Collar | Band, Spread, Button-Down, Mandarin |
| Kurta Style | Classic, Slim Fit, Relaxed |
| Bottom | Shalwar, Trouser, Pajama |
| Front Pocket | Yes, No |
| Side Pocket | Yes, No |

### Measurement Fields
| Measurement | Unit | Valid Range |
|------------|------|-------------|
| Chest | inches | 28 - 58 |
| Waist | inches | 22 - 52 |
| Hip | inches | 30 - 56 |
| Shoulder | inches | 13 - 22 |
| Sleeve Length | inches | 18 - 38 |
| Trouser Length | inches | 32 - 50 |
| Kameez Length | inches | 22 - 44 |
| Neck | inches | 12 - 20 |
| Notes | text | Free-form |

**Minimum required:** 3 valid measurements to save

### AI Webcam Measurement (MediaPipe Pose)
- **Technology:** MediaPipe Pose (model complexity 2, highest accuracy)
- **Camera:** 640x480, 30fps, front-facing
- **Calibration:** User enters height → 10 frames calibrate pixels-per-inch ratio
- **Distance guide:** Optimal body height = 75% of frame
- **Measurements derived:**
  - Shoulder width (direct landmark distance)
  - Chest circumference (2.3x shoulder width)
  - Hip circumference (2.4x hip width)
  - Waist circumference (0.8x hip)
  - Neck circumference (0.85x shoulder)
  - Sleeve length (shoulder to wrist, direct)
  - Kameez length (shoulder to knee, direct)
  - Trouser length (hip to ankle, direct)
- **Smoothing:** Weighted moving average (buffer size 10)
- **Confidence scoring:** Based on landmark visibility (green ≥85%, yellow ≥75%, red <75%)

---

## 10. Authentication

### User Registration
- Fields: name, email, phone, password, confirm_password
- Password: min 8 characters, hashed with `password_hash(PASSWORD_DEFAULT)`
- Email validation: `filter_var(FILTER_VALIDATE_EMAIL)`
- Duplicate check: email uniqueness enforced

### User Login
- Fields: email, password
- Verification: `password_verify()` against stored hash
- Session: Sets `user_id`, `user_name`, `user_email`

### Admin Login
- Separate `admin_login.php` → `admin/admin_dashboard.php`
- Session: `admin_id`, `admin_username`
- Protected: All admin pages check `$_SESSION['admin_id']`

### HTML Version (localStorage)
- Stores: `isLoggedIn`, `userName`, `userEmail`, `userPhone`
- No real authentication — demo/client-side only

---

## 11. Payment & Checkout

### Payment Methods
| Method | Type |
|--------|------|
| Cash on Delivery | Default |
| Bank Transfer | Manual |
| Credit Card | Manual |

### Checkout Process (PHP)
1. Validate all fields (name, email, phone, address, city)
2. Begin database transaction
3. Insert into `orders` table
4. Insert each item into `order_items` table
5. Insert measurements into `order_measurements` table
6. Auto-create guest user account (if not logged in) with temp password
7. Commit transaction
8. Clear cart sessions
9. Redirect to `order-confirmation.php`

### Order Statuses
| Status | Color |
|--------|-------|
| Pending | Blue |
| Processing | Yellow |
| Completed | Green |
| Cancelled | Red |

---

## 12. Admin Panel

### Admin Dashboard (`admin/admin_dashboard.php`)
- **Stats:** Total Products, Total Orders, Total Customers, Revenue
- **Recent Orders:** Last 5 orders with customer name, date, amount, status
- **Sidebar:** Dashboard, Products, Orders, Customers, Settings, Logout

### Admin Features (referenced but not fully implemented)
- `admin_products.php` — Product management
- `admin_orders.php` — Order management
- `admin_customers.php` — Customer management
- `admin_settings.php` — Site settings

---

## 13. Responsive Breakpoints

| Breakpoint | Target | Key Changes |
|-----------|--------|-------------|
| **1200px** | Large desktop | Featured grid 2 cols |
| **992px** | Desktop | Product grid 3 cols, bento grid 2 cols, carousel 500px |
| **768px** | Tablet | Navigation collapses, product grid 2 cols, cart drawer 100vw, marquee 28px |
| **576px** | Mobile | Product grid 1 col, bento 1 col, carousel 350px, marquee 20px, Ken Burns disabled |
| **480px** | Small mobile | Compact spacing |
| **400px** | Tiny | Minimal padding |

---

## 14. Security Features

| Feature | Implementation |
|---------|---------------|
| Password hashing | `password_hash(PASSWORD_DEFAULT)` — bcrypt |
| SQL injection prevention | Prepared statements (`$conn->prepare()`, `bind_param()`) |
| XSS prevention | `htmlspecialchars()` via `e()` helper function |
| CSRF protection | `bin2hex(random_bytes(32))` token on myorder.php |
| Input sanitization | `filter_input()` with appropriate filters |
| Session management | Server-side sessions for auth and cart |
| Admin protection | Session-based admin authentication |

---

## 15. Utility Functions (`includes/functions.php`)

| Function | Purpose | Returns |
|----------|---------|---------|
| `getCartCount()` | Count items in finalCart session | int |
| `getCartSubtotal()` | Sum of price * quantity | float |
| `isLoggedIn()` | Check if user_id exists in session | bool |
| `getUserName()` | Get user name from session | string |
| `getUserId()` | Get user ID from session | int\|null |
| `e($string)` | HTML escape (XSS prevention) | string |
| `getImagePath($path)` | Resolve image path with fallbacks (jpg/webp/png) | string |
| `formatPrice($amount)` | Format as "PKR X,XXX" | string |
| `initializeCart()` | Initialize session cart arrays | void |
| `getMeasurementValue($m)` | Extract value + confidence from measurement | array |
| `getConfidenceColor($c)` | Get CSS color for confidence level | string |

---

## 16. Key Design Decisions

1. **Two-tier cart** — Separates browsing intent (shoppingCart) from purchase intent (finalCart) because custom tailoring requires a design+measurement step before checkout
2. **Dual HTML/PHP** — HTML files work without a server (demo/static hosting), PHP files provide full functionality with database
3. **CSS Variables** — Single source of truth in `variables.css` for easy theme changes
4. **Component includes** — DRY principle via shared PHP includes for header, footer, scripts
5. **AI Measurements** — MediaPipe Pose integration reduces friction in the measurement step
6. **Guest checkout** — Auto-creates user accounts for guest orders with temporary passwords
7. **Bento grid** — Visual hierarchy on homepage using varied card sizes instead of uniform grid
8. **Cart drawer** — Slide-in panel instead of page navigation for faster cart review
9. **Scroll reveals** — IntersectionObserver-based animations for progressive content display
10. **Gold gradient buttons** — Premium feel using `linear-gradient(135deg, #C9A96E, #A68B52)` with glow shadows

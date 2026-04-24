# Stitch House — Project Links

Start **Apache** and **MySQL** from the XAMPP Control Panel, then use the URLs below.

## Storefront (Customer)

| Page | URL |
|---|---|
| Home | http://localhost/product/index.php |
| Shop / Order | http://localhost/product/order.php |
| My Order (customize) | http://localhost/product/myorder.php |
| Cart | http://localhost/product/cart.php |
| Checkout | http://localhost/product/checkout.php |
| Order Confirmation | http://localhost/product/order-confirmation.php |
| Contact (Support Tickets) | http://localhost/product/contact.php |
| Customer Login / Register | http://localhost/product/login.php |
| Customer Dashboard | http://localhost/product/dashboard.php |
| &nbsp; → Order History | http://localhost/product/dashboard.php?tab=orders |
| &nbsp; → My Measurements | http://localhost/product/dashboard.php?tab=measurements |
| Customer Logout | http://localhost/product/logout.php |

## Info Pages (footer)

| Page | URL |
|---|---|
| Shipping Policy | http://localhost/product/shipping-policy.php |
| Returns & Exchange | http://localhost/product/returns.php |
| Size Guide | http://localhost/product/size-guide.php |
| FAQs | http://localhost/product/faqs.php |

## Admin Panel

| Page | URL |
|---|---|
| Admin Login | http://localhost/product/admin_login.php |
| Dashboard | http://localhost/product/admin/admin_dashboard.php |
| Products (manage catalog + stock) | http://localhost/product/admin/admin_products.php |
| Orders (status transitions) | http://localhost/product/admin/admin_orders.php |
| Customers | http://localhost/product/admin/admin_customers.php |
| Support Tickets | http://localhost/product/admin/admin_tickets.php |
| Admin Logout | http://localhost/product/admin_logout.php |

## Admin Accounts

All passwords: `admin123`

| Username | Role | Access |
|---|---|---|
| `admin` | super | Full access to everything |
| `order_mgr` | order_manager | Products, Orders, Customers |
| `support` | support | Support Tickets only |

## Database

| Tool | URL |
|---|---|
| phpMyAdmin | http://localhost/phpmyadmin |
| Database name | `stitch_house_db` |

## Notes

- If port 80 is taken, Apache runs on `8080` — replace `localhost` with `localhost:8080` in all URLs.
- All customer flows start at the Home or Shop page. Shop → My Order (customize) → Cart → Checkout.
- Stock is validated at add-to-order and decremented on successful checkout.

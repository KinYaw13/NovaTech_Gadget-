# NovaTech Gadgets

NovaTech Gadgets is a PHP + MySQL e-commerce coursework system for XAMPP. It keeps the approved minimalist premium gadget-store UI and adds database-backed products, users, cart, checkout, orders, and admin management.

## Technologies

- PHP
- MySQL
- PDO prepared statements
- HTML, CSS, JavaScript
- XAMPP Control Panel v3.3.0

## Install XAMPP

1. Download and install XAMPP from Apache Friends.
2. Open XAMPP Control Panel.
3. Start `Apache` and `MySQL`.

## Project Location

Copy the whole `novatech_gadgets` folder into:

```text
C:\xampp\htdocs\novatech_gadgets\
```

## Database Setup

1. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

2. Import this SQL file:

```text
database/novatech_gadgets.sql
```

The SQL file creates the `novatech_gadgets` database, all required tables, sample categories, sample products, and default users.

If you already imported the older demo database and only want to replace the sample products with real market products, import:

```text
database/update_real_products.sql
```

This resets cart/order demo data and inserts products such as iPhone, Samsung Galaxy, Huawei, vivo, OPPO, iPad, Dell, ASUS, Sony, and Logitech.

## Run The Website

Open:

```text
http://localhost/novatech_gadgets/
```

## Default Login

Admin:

```text
Email: admin@novatech.com
Password: admin123
```

Customer:

```text
Email: customer@novatech.com
Password: customer123
```

## Main Features

- Customer register, login, and logout
- Hashed passwords with `password_hash()` and `password_verify()`
- Product listing from MySQL
- Product search, category filter, and sorting
- Product detail page
- Database cart add/update/remove
- Checkout with order, order items, and payment records
- Customer order history
- Protected admin dashboard
- Product add/edit/delete
- Order status management
- Customer list

## Folder Structure

```text
novatech_gadgets/
- index.php
- products.php
- product-detail.php
- cart.php
- checkout.php
- login.php
- register.php
- logout.php
- profile.php
- admin/
  - dashboard.php
  - products.php
  - add-product.php
  - edit-product.php
  - orders.php
  - customers.php
- includes/
  - db.php
  - header.php
  - footer.php
  - auth.php
  - product-card.php
- css/
  - style.css
- js/
  - app.js
- images/
- database/
  - novatech_gadgets.sql
```

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 05, 2026 at 04:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `novatech_gadgets`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `selected_color` varchar(80) DEFAULT NULL,
  `selected_spec` varchar(80) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `created_at`, `selected_color`, `selected_spec`, `unit_price`) VALUES
(14, 2, 1, 1, '2026-07-02 07:10:20', 'Cosmic Orange', '256GB', 5999.00);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Smartphones', 'Real flagship phones sold through official and mall sellers'),
(2, 'Laptops', 'Portable computers for work and study'),
(3, 'Tablets', 'Touch devices for notes and entertainment'),
(4, 'Smartwatches', 'Wearable health and notification devices'),
(5, 'Earbuds', 'Wireless personal audio devices'),
(6, 'Keyboards', 'Productivity keyboards and desk tools'),
(7, 'Mice', 'Precision mice for productivity'),
(8, 'Chargers', 'Official and premium charging accessories'),
(9, 'Phone Cases', 'Official and premium cases for iPhone and flagship phones'),
(10, 'Accessories', 'Official and premium add-ons for a complete setup');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `discount_name` varchar(140) NOT NULL,
  `discount_type` varchar(20) NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `applies_to` varchar(20) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `discount_name`, `discount_type`, `discount_value`, `applies_to`, `category`, `product_id`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Mid Term Year', 'percentage', 20.00, 'category', 'Mice', NULL, '2026-07-01 17:42:00', '2026-07-03 17:42:00', 'active', '2026-07-01 09:42:53', '2026-07-01 09:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` varchar(80) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_role` varchar(20) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `receiver_role` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `subject` varchar(160) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `sender_role`, `receiver_id`, `receiver_role`, `customer_id`, `admin_id`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 'customer_2_admin', 2, 'customer', 1, 'admin', 2, 1, 'Customer Reply', 'Test message from customer', 1, '2026-06-27 10:14:35'),
(2, 'customer_2_admin', 1, 'admin', 2, 'customer', 2, 1, 'NovaTech Admin Reply', 'Test reply from admin', 1, '2026-06-27 10:14:52'),
(3, 'customer_2_admin', 1, 'admin', 2, 'customer', 2, 1, 'You Received RM15 Voucher', 'Thank you for completing your NovaTech Gadgets profile. You have received a RM15 voucher. Use code WELCOME15-60A205 at checkout.', 0, '2026-06-27 10:15:31'),
(4, 'customer_3_admin', 3, 'customer', 1, 'admin', 3, 1, 'Customer Reply', 'hi', 1, '2026-06-27 10:18:42'),
(5, 'customer_3_admin', 1, 'admin', 3, 'customer', 3, 1, 'NovaTech Admin Reply', 'hi', 1, '2026-06-27 10:20:40'),
(6, 'customer_3_admin', 3, 'system', 1, 'admin', 3, 1, 'Customer Profile Completed', 'Customer Q has completed their profile information.', 1, '2026-06-27 11:11:39'),
(7, 'customer_3_admin', 1, 'admin', 3, 'customer', 3, 1, 'You Received RM15 Voucher', 'Thank you for completing your NovaTech Gadgets profile. You have received a RM15 voucher. Use code WELCOME15-2BB82A at checkout.', 1, '2026-06-29 01:43:28'),
(8, 'customer_5_admin', 1, 'system', 5, 'customer', 5, 1, 'Complete Your NovaTech Profile', 'Welcome to NovaTech Gadgets! Please complete your profile information, including your phone number and address, so we can process your orders faster and provide better support.', 1, '2026-06-29 01:47:11'),
(9, 'customer_5_admin', 5, 'system', 1, 'admin', 5, 1, 'Customer Profile Completed', 'Customer CHEN HONG JUN has completed their profile information.', 1, '2026-06-29 01:49:20'),
(10, 'customer_5_admin', 1, 'admin', 5, 'customer', 5, 1, 'NovaTech Admin Reply', 'why u did finish ur information', 1, '2026-07-01 16:11:06'),
(11, 'customer_5_admin', 1, 'admin', 5, 'customer', 5, 1, 'You Received RM15 Voucher', 'Thank you for completing your NovaTech Gadgets profile. You have received a RM15 voucher. Use code WELCOME15-708AF5 at checkout.', 1, '2026-07-01 16:11:16');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `invoice_no` varchar(40) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('Paid','Processing','Packed','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Paid',
  `shipping_name` varchar(100) NOT NULL,
  `customer_email` varchar(160) DEFAULT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `bank_name` varchar(80) DEFAULT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `voucher_code` varchar(40) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_method` varchar(50) DEFAULT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_estimate` varchar(100) DEFAULT NULL,
  `pickup_location` text DEFAULT NULL,
  `account_holder_name` varchar(150) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `invoice_no`, `user_id`, `total_amount`, `order_status`, `shipping_name`, `customer_email`, `shipping_phone`, `shipping_address`, `payment_method`, `bank_name`, `card_last4`, `subtotal`, `tax`, `created_at`, `voucher_code`, `discount_amount`, `delivery_method`, `delivery_fee`, `delivery_estimate`, `pickup_location`, `account_holder_name`, `payment_reference`) VALUES
(1, NULL, 2, 10613.94, 'Delivered', 'Demo Customer', NULL, '0108888888', 'No.1', 'Credit / Debit Card', NULL, NULL, NULL, NULL, '2026-06-18 13:57:06', NULL, 0.00, NULL, 0.00, NULL, NULL, NULL, NULL),
(2, 'NVG-20260625-133537-808', 1, 6373.94, 'Paid', 'NovaTech Admin', 'kinyawkiew@gmail.com', '+60108888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 5999.00, 359.94, '2026-06-25 11:35:37', NULL, 0.00, NULL, 0.00, NULL, NULL, NULL, NULL),
(3, 'NVG-20260626-053554-964', 2, 6373.94, 'Paid', 'Demo Customer', 'kinyawkiew@gmail.com', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 5999.00, 359.94, '2026-06-26 03:35:54', NULL, 0.00, NULL, 0.00, NULL, NULL, NULL, NULL),
(4, 'NVG-20260627-130950-604', 3, 1179.94, 'Processing', 'Q', 'kinyawkiew@gmail.com', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 1099.00, 65.94, '2026-06-27 11:09:50', NULL, 0.00, NULL, 0.00, NULL, NULL, NULL, NULL),
(5, 'NVG-20260628-131448-842', 3, 8492.88, 'Paid', 'Q', 'tp083601@mail.apu.edu.my', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 7998.00, 479.88, '2026-06-28 11:14:48', NULL, 0.00, NULL, 0.00, NULL, NULL, NULL, NULL),
(6, 'NVG-20260629-033640-309', 3, 318.80, 'Paid', 'Q', 'tp083601@mail.apu.edu.my', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 286.60, 17.20, '2026-06-29 01:36:40', NULL, 0.00, NULL, 0.00, NULL, NULL, NULL, NULL),
(7, 'NVG-20260629-043513-689', 3, 490.94, 'Paid', 'Q', 'tp083601@mail.apu.edu.my', '+60126577643', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 449.00, 26.94, '2026-06-29 02:35:13', NULL, 0.00, NULL, 0.00, NULL, NULL, NULL, NULL),
(8, 'NVG-20260701-181016-401', 3, 8476.98, 'Paid', 'Q', 'kinyawkiew@gmail.com', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 7998.00, 478.98, '2026-07-01 16:10:16', 'WELCOME15-2BB82A', 15.00, 'express', 15.00, '1-2 working days', NULL, NULL, NULL),
(9, 'NVG-20260701-184759-581', 3, 8379.88, 'Paid', 'Q', 'kinyawkiew@gmail.com', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 7898.00, 473.88, '2026-07-01 16:47:59', NULL, 0.00, 'standard', 8.00, '3-5 working days', NULL, NULL, NULL),
(10, 'NVG-20260701-185301-894', 3, 6048.94, 'Paid', 'Q', 'TP083601@mail.apu.edu.my', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 5699.00, 341.94, '2026-07-01 16:53:01', NULL, 0.00, 'standard', 8.00, '3-5 working days', NULL, NULL, NULL),
(11, 'NVG-20260702-094241-308', 3, 7426.94, 'Paid', 'Q', 'tp083601@mail.apu.edu.my', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Credit / Debit Card', 'Maybank', '8888', 6999.00, 419.94, '2026-07-02 07:42:41', NULL, 0.00, 'standard', 8.00, '3-5 working days', NULL, NULL, NULL),
(12, 'NVG-20260703-085249-963', 3, 6366.94, 'Processing', 'Q', 'tp083601@mail.apu.edu.my', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'Cash on Delivery', NULL, NULL, 5999.00, 359.94, '2026-07-03 06:52:49', NULL, 0.00, 'standard', 8.00, '3-5 working days', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_category` varchar(100) DEFAULT NULL,
  `product_name` varchar(180) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `selected_options` varchar(255) DEFAULT NULL,
  `warranty_period` varchar(80) DEFAULT NULL,
  `warranty_type` varchar(120) DEFAULT NULL,
  `warranty_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `product_category`, `product_name`, `quantity`, `price`, `subtotal`, `selected_options`, `warranty_period`, `warranty_type`, `warranty_description`) VALUES
(1, 1, 1, NULL, NULL, 1, 9999.00, 9999.00, '2TB Deep Blue', NULL, NULL, NULL),
(2, 2, 1, 'Smartphones', 'Apple iPhone 17 Pro Max', 1, 5999.00, 5999.00, '256GB Cosmic Orange', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(3, 3, 7, 'Smartphones', 'Samsung Galaxy S25 Ultra', 1, 5999.00, 5999.00, '', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(4, 4, 42, 'Earbuds', 'Apple AirPods Pro 3', 1, 1099.00, 1099.00, '', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(5, 5, 73, 'Tablets', 'Huawei MatePad Pro 13.2', 2, 3999.00, 7998.00, '', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(6, 6, 76, 'Mice', 'Attack Shark X8 SE', 1, 286.60, 286.60, '', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(7, 7, 57, 'Mice', 'Logitech MX Master 3S', 1, 449.00, 449.00, '', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(8, 8, 73, 'Tablets', 'Huawei MatePad Pro 13.2', 2, 3999.00, 7998.00, '', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(9, 9, 72, 'Tablets', 'Xiaomi Pad 7 Pro', 1, 1899.00, 1899.00, '', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(10, 9, 7, 'Smartphones', 'Samsung Galaxy S25 Ultra', 1, 5999.00, 5999.00, '', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(11, 10, 71, 'Tablets', 'Samsung Galaxy Tab S10 Ultra', 1, 5699.00, 5699.00, '', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(12, 11, 54, 'Laptops', 'Apple MacBook Pro 14 M4', 1, 6999.00, 6999.00, '', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(13, 12, 1, 'Smartphones', 'Apple iPhone 17 Pro Max', 1, 5999.00, 5999.00, '256GB Cosmic Orange', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `bank_name` varchar(80) DEFAULT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `payment_status` enum('Pending','Paid','Failed') NOT NULL DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_holder_name` varchar(150) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `bank_name`, `card_last4`, `payment_status`, `payment_date`, `account_holder_name`, `payment_reference`) VALUES
(1, 1, 'Credit / Debit Card', NULL, NULL, 'Pending', '2026-06-18 13:57:06', NULL, NULL),
(2, 2, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-06-25 11:35:37', NULL, NULL),
(3, 3, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-06-26 03:35:54', NULL, NULL),
(4, 4, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-06-27 11:09:50', NULL, NULL),
(5, 5, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-06-28 11:14:48', NULL, NULL),
(6, 6, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-06-29 01:36:40', NULL, NULL),
(7, 7, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-06-29 02:35:13', NULL, NULL),
(8, 8, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-07-01 16:10:16', NULL, NULL),
(9, 9, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-07-01 16:47:59', NULL, NULL),
(10, 10, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-07-01 16:53:01', NULL, NULL),
(11, 11, 'Credit / Debit Card', 'Maybank', '8888', 'Paid', '2026-07-02 07:42:41', NULL, NULL),
(12, 12, 'Cash on Delivery', NULL, NULL, 'Pending', '2026-07-03 06:52:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(150) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `image` varchar(700) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 4.5,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `warranty_period` varchar(80) NOT NULL DEFAULT '1 Year',
  `warranty_type` varchar(120) NOT NULL DEFAULT 'Manufacturer Warranty',
  `warranty_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `brand`, `description`, `price`, `stock_quantity`, `image`, `rating`, `status`, `created_at`, `warranty_period`, `warranty_type`, `warranty_description`) VALUES
(1, 1, 'Apple iPhone 17 Pro Max', 'Apple', 'Config: 256GB / 512GB / 1TB / 2TB. Colors: Cosmic Orange, Deep Blue, Silver.', 5999.00, 7, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-9inch-cosmicorange?wid=900&hei=900&fmt=png-alpha', 4.9, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(2, 1, 'Apple iPhone 17 Pro', 'Apple', 'Config: 256GB / 512GB / 1TB. Colors: Cosmic Orange, Deep Blue, Silver.', 5499.00, 12, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-3inch-cosmicorange?wid=900&hei=900&fmt=png-alpha', 4.9, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(3, 1, 'Apple iPhone Air', 'Apple', 'Config: 256GB / 512GB / 1TB. Colors: Sky Blue, Light Gold, Cloud White, Space Black.', 4999.00, 12, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-air-finish-select-202509-skyblue?wid=900&hei=900&fmt=png-alpha', 4.8, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(4, 1, 'Apple iPhone 17', 'Apple', 'Config: 256GB / 512GB. Colors: Lavender, Sage, Mist Blue, White, Black.', 3999.00, 18, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-finish-select-202509-lavender?wid=900&hei=900&fmt=png-alpha', 4.8, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(5, 1, 'Apple iPhone 16', 'Apple', 'Config: 128GB / 256GB / 512GB. Colors: Ultramarine, Teal, Pink, White, Black.', 3499.00, 20, 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-16-1.jpg', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(6, 1, 'Apple iPhone 15 Pro Max', 'Apple', 'Config: 256GB / 512GB / 1TB. Colors: Natural Titanium, Blue Titanium, White Titanium, Black Titanium.', 4999.00, 18, 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-15-pro-max-1.jpg', 4.8, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(7, 1, 'Samsung Galaxy S25 Ultra', 'Samsung', 'Config: 12GB+256GB / 12GB+512GB / 12GB+1TB. Colors: Titanium Silverblue, Titanium Black, Titanium Gray, Titanium Whitesilver, Titanium Jadegreen, Titanium Pinkgold.', 5999.00, 14, 'https://images.samsung.com/rs/smartphones/galaxy-s25-ultra/buy/02_Gallery/02-1_KV_No-Exclusive-Color/01_Color_Group_KV_image_PC.jpg', 4.9, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(8, 1, 'Samsung Galaxy S24 Ultra', 'Samsung', 'Config: 12GB+256GB / 12GB+512GB / 12GB+1TB. Colors: Titanium Black, Titanium Gray, Titanium Violet, Titanium Yellow.', 4699.00, 16, 'https://images.samsung.com/co/smartphones/galaxy-s24-ultra/images/galaxy-s24-ultra-highlights-kv.jpg?imbypass=true', 4.8, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(9, 1, 'Samsung Galaxy Z Fold7', 'Samsung', 'Config: 12GB+256GB / 12GB+512GB / 16GB+1TB. Colors: Blue Shadow, Jetblack, Silver Shadow, Mint.', 7799.00, 8, 'https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-fold7/buy/Q7_Global_Color_Group_KV_Jetblack_Blue_Shadow_Silver_Shadow_No-text_PC_1600x864.png', 4.8, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(10, 1, 'Samsung Galaxy Z Flip7', 'Samsung', 'Config: 12GB+256GB / 12GB+512GB. Colors: Blue Shadow, Jetblack, Coralred, Mint.', 4999.00, 10, 'https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-flip7/buy/B7_Global_Color_Group_KV_Blue_Shadow_No-text_PC_1600x864.png', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(11, 1, 'OPPO Find X8 Pro', 'OPPO', 'Config: 16GB+512GB. Colors: Pearl White, Space Black.', 4999.00, 12, 'https://www.oppo.com/content/dam/oppo/common/mkt/v2-2/find-x8-series-en/find-x8-pro/listpage/432-600-white.png', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(12, 1, 'OPPO Find X8', 'OPPO', 'Config: 12GB+256GB / 16GB+512GB. Colors: Star Grey, Space Black, Shell Pink.', 3699.00, 14, 'https://www.oppo.com/content/dam/oppo/common/mkt/v2-2/find-x8-series-en/find-x8/listpage/436-600-white-v2.png', 4.6, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(13, 1, 'vivo X300 Pro', 'vivo', 'Config: 12GB+256GB / 16GB+512GB / 16GB+1TB. Colors: Phantom Black, Mist Blue, Dune Brown, Cloud White.', 4599.00, 10, 'https://fdn2.gsmarena.com/vv/pics/vivo/vivo-x300-pro-1.jpg', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(14, 1, 'vivo X200 Pro', 'vivo', 'Config: 16GB+512GB. Colors: Titanium Grey, Carbon Black, Blue.', 4699.00, 13, 'https://fdn2.gsmarena.com/vv/pics/vivo/vivo-x200-pro-1.jpg', 4.6, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(15, 1, 'Xiaomi 15 Ultra', 'Xiaomi', 'Config: 16GB+512GB / 16GB+1TB. Colors: Black, White, Silver Chrome.', 5199.00, 9, 'https://i02.appmifile.com/492_operatorx_operatorx_opx/02/03/2025/5667c36c15d47b90d0faa7ac23c9f276.png', 4.8, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(16, 1, 'HONOR Magic7 Pro', 'HONOR', 'Config: 12GB+512GB. Colors: Lunar Shadow Grey, Breeze Blue, Black.', 4599.00, 11, 'https://fdn2.gsmarena.com/vv/pics/honor/honor-magic7-pro-1.jpg', 4.6, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(17, 1, 'Huawei Pura 80 Ultra', 'Huawei', 'Config: 16GB+512GB / 16GB+1TB. Colors: Gold, Black, White.', 5999.00, 8, 'https://consumer.huawei.com/dam/content/dam/huawei-cbg-site/common/mkt/pdp/phones/pura80-ultra/list-gold.png', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(18, 9, 'Apple Silicone Case with MagSafe - iPhone 17 Series', 'Apple', 'Fits: iPhone 17 Pro Max, iPhone 17 Pro, iPhone 17, iPhone Air, iPhone 17e. Colors: Black, Orange, Blue, Green, Pink.', 249.00, 35, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MYYV3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(19, 9, 'Apple Clear Case with MagSafe - iPhone 17 Series', 'Apple', 'Fits: iPhone 17 Pro Max, iPhone 17 Pro, iPhone 17, iPhone Air. Color: Clear.', 249.00, 32, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MA7E4?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.6, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(20, 9, 'Apple TechWoven Case with MagSafe - iPhone 17 Pro', 'Apple', 'Fits: iPhone 17 Pro Max and iPhone 17 Pro. Colors: Orange, Blue, Black, Green.', 299.00, 24, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MYYX3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(21, 9, 'Apple Silicone Case with MagSafe - iPhone 16 Series', 'Apple', 'Fits: iPhone 16 Pro Max, iPhone 16 Pro, iPhone 16 Plus, iPhone 16. Colors: Black, Denim, Lake Green, Fuchsia.', 249.00, 28, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MYYV3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.6, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(22, 9, 'Apple Clear Case with MagSafe - iPhone 16 Series', 'Apple', 'Fits: iPhone 16 Pro Max, iPhone 16 Pro, iPhone 16 Plus, iPhone 16. Color: Clear.', 249.00, 28, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MA7E4?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.6, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(23, 9, 'Apple Silicone Case with MagSafe - iPhone 15 Series', 'Apple', 'Fits: iPhone 15 Pro Max, iPhone 15 Pro, iPhone 15 Plus, iPhone 15. Colors: Black, Blue, Pink, Green.', 249.00, 26, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MT0Y3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.5, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(30, 8, 'Apple 20W USB-C Power Adapter', 'Apple', 'Official Apple USB-C charger for iPhone and iPad. Color: White.', 99.00, 50, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/MWVV3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(31, 8, 'Apple 30W USB-C Power Adapter', 'Apple', 'Official Apple USB-C charger for iPhone, iPad, and light MacBook Air charging. Color: White.', 169.00, 35, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/HSJT2?wid=890&hei=890&fmt=jpeg&qlt=90', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(32, 8, 'Apple 240W USB-C Charge Cable 2m', 'Apple', 'Official Apple USB-C charge cable for iPhone, iPad, and MacBook. Color: White.', 149.00, 45, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MU2G3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(33, 8, 'Apple MagSafe Charger', 'Apple', 'Official magnetic wireless charger for MagSafe iPhone models. Color: White.', 239.00, 40, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MHXH3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.8, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(34, 10, 'Apple AirPods Pro 2 USB-C', 'Apple', 'Official Apple premium earbuds with active noise cancellation and USB-C charging case.', 1099.00, 28, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MTJV3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.8, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(35, 10, 'Apple AirTag 4 Pack', 'Apple', 'Official Apple tracker pack for keys, bags, and daily items.', 499.00, 30, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MX542?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active', '2026-06-17 06:46:02', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(42, 5, 'Apple AirPods Pro 3', 'Apple', 'Config: USB-C MagSafe Charging Case. Colors: White.', 1099.00, 21, 'https://www.apple.com/v/airpods-pro/r/images/meta/og__c0ceegchesom_overview.png?202604261906', 4.9, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(43, 5, 'Apple AirPods 4 with ANC', 'Apple', 'Config: USB-C Charging Case. Colors: White.', 829.00, 24, 'https://www.apple.com/v/airpods-4/g/images/meta/airpods-4__gnjh1t3yjxm6_og.png?202604230007', 4.7, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(44, 5, 'Samsung Galaxy Buds3 Pro', 'Samsung', 'Config: Standard. Colors: Silver, White.', 999.00, 18, 'https://i5.walmartimages.com/asr/a0a7118b-71be-4fa3-98c4-ef8f98f32dbd.b5530453763509be966b3012c81d1fcd.jpeg?odnHeight=612&odnWidth=612&odnBg=FFFFFF', 4.7, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(45, 5, 'Xiaomi Buds 5 Pro', 'Xiaomi', 'Config: Standard. Colors: Black, White, Gold.', 699.00, 20, 'https://i02.appmifile.com/455_item_my/25/02/2025/2e013f5e02abbb4487af8fc7aa5868b3.png', 4.6, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(46, 5, 'Beats Studio Buds +', 'Beats', 'Config: Standard. Colors: Transparent, Black, Ivory.', 699.00, 16, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/MQLK3?wid=1200&hei=630&fmt=jpeg&qlt=95', 4.6, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(47, 6, 'Razer BlackWidow V4 Pro 75%', 'Razer', 'Config: Orange tactile switches / Green clicky switches. Color: Black.', 1399.00, 10, 'https://assets2.razerzone.com/images/pnx.assets/3b09f11f56c96cab9def3c2825f00567/razer-blackwidow-v4-pro-75-og-image-1200x630-v2.webp', 4.8, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(48, 6, 'Razer Huntsman V3 Pro TKL', 'Razer', 'Config: Analog optical switches. Color: Black.', 799.00, 12, 'https://assets2.razerzone.com/images/pnx.assets/ce8efb94452a0b8f9d2e8dcebc9bab5c/razer-huntsman-v3-pro-tkl-ogimage-1200x630-v2.webp', 4.8, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(49, 6, 'Logitech MX Mechanical', 'Logitech', 'Config: Tactile Quiet / Linear / Clicky. Colors: Graphite, Pale Grey.', 699.00, 14, 'https://resource.logitech.com/w_544%2Ch_466%2Car_7%3A6%2Cc_pad%2Cq_auto%2Cf_auto%2Cdpr_1.0/d_transparent.gif/content/dam/logitech/en/products/keyboards/mx-mechanical/migration-assets-for-delorean-2025/gallery/mx-mechanical-top-view-graphite-us.png', 4.7, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(50, 6, 'Keychron Q1 HE', 'Keychron', 'Config: Magnetic switches. Colors: Black, White.', 999.00, 9, 'https://www.keychron.com/cdn/shop/files/Q1-HE-Iconic-Features.jpg?crop=center&height=1200&v=1754623218&width=1200', 4.7, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(51, 6, 'ASUS ROG Azoth', 'ASUS', 'Config: ROG NX switches. Colors: Black, White.', 1199.00, 9, 'https://dlcdnwebimgs.asus.com/gain/145896AC-B462-4466-A1FE-935F085741F3', 4.8, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(52, 2, 'Apple MacBook Air 13 M4', 'Apple', 'Config: 16GB+256GB / 16GB+512GB / 24GB+512GB. Colors: Sky Blue, Silver, Starlight, Midnight.', 4499.00, 11, 'https://www.apple.com/v/macbook-air/z/images/meta/macbook_air_mx__ez5y0k5yy7au_og.png?202605071756', 4.8, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(53, 2, 'Apple MacBook Air 15 M4', 'Apple', 'Config: 16GB+256GB / 16GB+512GB / 24GB+512GB. Colors: Sky Blue, Silver, Starlight, Midnight.', 5499.00, 10, 'https://www.apple.com/v/macbook-air/z/images/meta/macbook_air_mx__ez5y0k5yy7au_og.png?202605071756', 4.8, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(54, 2, 'Apple MacBook Pro 14 M4', 'Apple', 'Config: 16GB+512GB / 24GB+1TB. Colors: Space Black, Silver.', 6999.00, 7, 'https://www.apple.com/v/macbook-pro/ax/images/meta/macbook-pro__difvbgz1plsi_og.png?202605071756', 4.9, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(55, 2, 'Apple MacBook Pro 16 M4 Pro', 'Apple', 'Config: 24GB+512GB / 48GB+1TB. Colors: Space Black, Silver.', 10499.00, 7, 'https://www.apple.com/v/macbook-pro/ax/images/meta/macbook-pro__difvbgz1plsi_og.png?202605071756', 4.9, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(56, 2, 'ASUS Zenbook 14 OLED', 'ASUS', 'Config: 16GB+1TB / 32GB+1TB. Colors: Ponder Blue, Foggy Silver.', 4299.00, 10, 'https://dlcdnwebimgs.asus.com/gain/282fa6b1-5d9e-4950-ab46-1da2defbe6a3/', 4.7, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(57, 7, 'Logitech MX Master 3S', 'Logitech', 'Config: Standard. Colors: Graphite, Pale Grey.', 449.00, 27, 'https://resource.logitech.com/w_800,c_limit,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/mx-master-3s/gallery/mx-master-3s-mouse-top-view-graphite.png', 4.8, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(58, 7, 'Logitech MX Anywhere 3S', 'Logitech', 'Config: Standard. Colors: Graphite, Pale Grey, Rose.', 399.00, 24, 'https://resource.logitech.com/w_544%2Ch_466%2Car_7%3A6%2Cc_pad%2Cq_auto%2Cf_auto%2Cdpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/mx-anywhere-3s/product-gallery/graphite/mx-anywhere-3s-mouse-top-view-graphite.png', 4.7, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(59, 7, 'Razer Basilisk V3 Pro', 'Razer', 'Config: Standard. Colors: Black, White.', 699.00, 15, 'https://assets2.razerzone.com/images/og-image/razer-basilisk-v3-pro-og-image.jpg', 4.8, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(60, 7, 'Razer Viper V3 Pro', 'Razer', 'Config: Standard. Colors: Black, White.', 799.00, 14, 'https://assets2.razerzone.com/images/pnx.assets/24970f67be4ba9644e28720377d91cfb/razer-viper-v3-pro-1200x630.webp', 4.8, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(61, 7, 'Apple Magic Mouse USB-C', 'Apple', 'Config: Standard. Colors: White, Black.', 399.00, 18, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/MXK53?wid=1200&hei=630&fmt=jpeg&qlt=95', 4.5, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(62, 4, 'Apple Watch Series 11', 'Apple', 'Config: 42mm GPS / 46mm GPS / 46mm GPS + Cellular. Colors: Rose Gold, Silver, Jet Black.', 1799.00, 18, 'https://www.apple.com/my/apple-watch-series-11/images/meta/apple-watch-series-11__cim89z1i9spe_og.png?202605220009', 4.8, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(63, 4, 'Apple Watch SE 3', 'Apple', 'Config: 40mm GPS / 44mm GPS / 44mm GPS + Cellular. Colors: Midnight, Starlight.', 1049.00, 20, 'https://www.apple.com/my/apple-watch-se-3/images/meta/apple-watch-se-3__d0wwc67lzg02_og.png?202605220009', 4.6, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(64, 4, 'Apple Watch Ultra 3', 'Apple', 'Config: 49mm GPS + Cellular. Colors: Natural Titanium, Black Titanium.', 3699.00, 10, 'https://www.apple.com/my/apple-watch-ultra-3/images/meta/apple-watch-ultra-3__y7lxayrwmlem_og.png?202605220009', 4.9, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(65, 4, 'Samsung Galaxy Watch8', 'Samsung', 'Config: 40mm Bluetooth / 44mm Bluetooth / 44mm LTE. Colors: Graphite, Silver.', 1299.00, 16, 'https://image-us.samsung.com/us/watches/galaxy-watch8/images/kv/11_Watch8-Graphite-44mm-Thumbnail-800x600.jpg', 4.7, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(66, 4, 'Samsung Galaxy Watch Ultra', 'Samsung', 'Config: 47mm LTE. Colors: Titanium Gray, Titanium Silver, Titanium White.', 2799.00, 12, 'https://images.samsung.com/is/image/samsung/p6pim/us/f2507/gallery/us-galaxy-watch-ultra-2025-l705-sm-l705uza1xaa-547907800?$PD_GALLERY_PNG$', 4.8, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(67, 4, 'Xiaomi Watch S4', 'Xiaomi', 'Config: Standard / Leather Strap Edition. Colors: Black, Silver.', 699.00, 22, 'https://i02.appmifile.com/15_item_my/25/02/2025/c2e7d63c48093702b15d65fcfe324af2.png', 4.6, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(68, 4, 'Fitbit Charge 6', 'Fitbit', 'Config: Standard. Colors: Black, Champagne Gold, Silver.', 799.00, 18, 'https://lh3.googleusercontent.com/4fHe7o06nMhfBJhr2NPnn49GnXZV4m60iqWNfmNl5H5MjD1nuMhEGTz1hNR_Du9rlsnA5HyEF5NKcfNeE22YZAghCtEZl0BX_Q=rj-sc0xffffffff', 4.5, 'active', '2026-06-17 10:52:50', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
(69, 3, 'Apple iPad Air M3', 'Apple', 'Config: 11-inch 128GB / 11-inch 256GB / 13-inch 128GB / 13-inch 256GB. Colors: Space Grey, Blue, Purple, Starlight.', 2799.00, 14, 'https://www.apple.com/v/ipad-air/ah/images/meta/ipad-air_overview__bc2fd15uec0y_og.png?202606081814', 4.8, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(70, 3, 'Apple iPad Pro M4', 'Apple', 'Config: 11-inch 256GB / 13-inch 256GB / 13-inch 512GB. Colors: Silver, Space Black.', 4499.00, 12, 'https://www.apple.com/v/ipad-pro/aw/images/meta/ipad-pro_overview__bu4cql27diaa_og.png?202605071756', 4.9, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(71, 3, 'Samsung Galaxy Tab S10 Ultra', 'Samsung', 'Config: 12GB+256GB / 12GB+512GB / 16GB+1TB. Colors: Moonstone Grey, Platinum Silver.', 5699.00, 9, 'https://images.samsung.com/is/image/samsung/assets/hk_en/tablets/galaxy-tab-s10/S10_Size_KV_PC_1600x864.jpg?imbypass=true', 4.8, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(72, 3, 'Xiaomi Pad 7 Pro', 'Xiaomi', 'Config: 8GB+256GB / 12GB+512GB. Colors: Blue, Grey, Green.', 1899.00, 17, 'https://i02.appmifile.com/mi-com-product/fly-birds/xiaomi-pad-7-pro/pc/5484effe86e2b64e1bf9d0235e7bd126.jpg', 4.6, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(73, 3, 'Huawei MatePad Pro 13.2', 'Huawei', 'Config: 12GB+256GB / 12GB+512GB. Colors: Black, White, Green.', 3999.00, 8, 'https://consumer.huawei.com/content/dam/huawei-cbg-site/cn/mkt/pdp/tablets/matepad-pro-13-2-v1/list/list-black.png', 4.6, 'active', '2026-06-17 10:52:50', '2 Years', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'),
(76, 7, 'Attack Shark X8 SE', 'Attack Shark', '', 286.60, 9, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQkDd5lgEvshh_SssN9lHvFdj_4INzppIi_oy_AiscaQw&s=10', 4.5, 'active', '2026-06-19 09:16:25', '1 Year', 'Manufacturer Warranty', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.');

-- --------------------------------------------------------

--
-- Table structure for table `product_color_images`
--

CREATE TABLE `product_color_images` (
  `color_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_name` varchar(80) NOT NULL,
  `color_hex` varchar(20) NOT NULL DEFAULT '#d8d8d8',
  `image` varchar(700) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_color_images`
--

INSERT INTO `product_color_images` (`color_id`, `product_id`, `color_name`, `color_hex`, `image`, `created_at`) VALUES
(8, 76, 'Black', '#0a0a0a', 'https://attackshark.com/cdn/shop/files/X8_1.png?v=1777367414&width=800', '2026-06-25 13:29:17'),
(9, 76, 'White', '#f8f7f7', 'https://attackshark.com/cdn/shop/files/X8_2_fc2a39d6-279f-48ca-aa6f-19e08fb4aecd.png?v=1777367414&width=800', '2026-06-25 13:29:17'),
(10, 76, 'Berry Red', '#f36da3', 'https://attackshark.com/cdn/shop/files/X8_3_4470a318-eded-4870-a277-d3e2a4929c96.png?v=1777367414&width=800', '2026-06-25 13:29:17');

-- --------------------------------------------------------

--
-- Table structure for table `product_requests`
--

CREATE TABLE `product_requests` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `requested_by_email` varchar(160) DEFAULT NULL,
  `customer_message` text NOT NULL,
  `normalized_query` varchar(255) DEFAULT NULL,
  `product_name` varchar(180) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `estimated_price` decimal(10,2) NOT NULL,
  `product_image_url` varchar(700) DEFAULT NULL,
  `source_url` varchar(700) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Added') NOT NULL DEFAULT 'Pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_requests`
--

INSERT INTO `product_requests` (`id`, `customer_id`, `requested_by_email`, `customer_message`, `normalized_query`, `product_name`, `brand`, `category`, `estimated_price`, `product_image_url`, `source_url`, `description`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(10, 3, 'tp083601@mail.apu.edu.my', 'PS5', 'ps5', 'Ps5', 'Unknown', 'Gaming Console', 3200.00, NULL, NULL, NULL, 'Approved', '', '2026-07-01 06:41:07', '2026-07-01 06:41:37');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text NOT NULL,
  `status` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_completed` tinyint(1) NOT NULL DEFAULT 0,
  `profile_completed_notified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `phone`, `address`, `age`, `gender`, `created_at`, `profile_completed`, `profile_completed_notified`) VALUES
(1, 'NovaTech Admin', 'admin@novatech.com', '$2y$10$umcMcpCcUXKvqZgar9RbJ.5yhizINkMPglFT5eVaL8ydb6Nm5Hbo6', 'admin', '0123456789', 'NovaTech Office', NULL, NULL, '2026-06-16 05:49:55', 0, 0),
(2, 'Demo Customer', 'customer@novatech.com', '$2y$10$MAvps0kMwF5w2lD8CGk3ieP620wNjKzr/KHRkNdHHXEJbgHzg6PSu', 'customer', '0198765432', 'Kuala Lumpur, Malaysia', NULL, NULL, '2026-06-16 05:49:55', 0, 0),
(3, 'Q', 'tp083601@mail.apu.edu.my', '$2y$10$h12jVj5vUpL6QyRfaL6lP.AjS22Fd6eGSAW3CnQmSaKb287rVt7bi', 'customer', '+60188888888', 'alan Teknologi 5, Taman Teknologi Malaysia, 57000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', NULL, 'Male', '2026-06-16 05:52:51', 1, 1),
(5, 'CHEN HONG JUN', 'steamhj0610@gmail.com', '$2y$10$4wxE2dSaJ70ytqnzNt75HesZ2aHIBxDMaS3QYmTW0Rzu6Upe/CoGK', 'customer', '+601212345678', 'taanjung rambutan', 20, 'Male', '2026-06-29 01:47:11', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(40) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `voucher_type` varchar(80) NOT NULL,
  `discount_type` varchar(20) NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 15.00,
  `min_order_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_by_admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `customer_id`, `voucher_type`, `discount_type`, `discount_value`, `min_order_amount`, `status`, `created_by_admin_id`, `created_at`, `expires_at`, `used_at`) VALUES
(1, 'WELCOME15-60A205', 2, 'welcome_profile_completion', 'fixed', 15.00, 0.00, 'active', 1, '2026-06-27 10:15:31', '2026-07-27 12:15:31', NULL),
(2, 'WELCOME15-2BB82A', 3, 'welcome_profile_completion', 'fixed', 15.00, 0.00, 'used', 1, '2026-06-29 01:43:28', '2026-07-29 03:43:28', '2026-07-02 00:10:16'),
(3, 'WELCOME15-708AF5', 5, 'welcome_profile_completion', 'fixed', 15.00, 0.00, 'active', 1, '2026-07-01 16:11:16', '2026-07-31 18:11:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `warranty_claims`
--

CREATE TABLE `warranty_claims` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `product_name` varchar(180) NOT NULL,
  `reason` varchar(120) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warranty_claims`
--

INSERT INTO `warranty_claims` (`id`, `customer_id`, `order_id`, `order_item_id`, `product_name`, `reason`, `description`, `image_path`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 3, 'Samsung Galaxy S25 Ultra', 'Warranty claim', 'Test warranty claim from automated check', NULL, 'Completed', 'Approved for inspection', '2026-06-27 16:35:31', '2026-07-01 06:42:25'),
(2, 3, 4, 4, 'Apple AirPods Pro 3', 'Return request', 'its too slow', NULL, 'Completed', '', '2026-07-01 06:40:07', '2026-07-01 06:42:20');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_discount_status` (`status`,`applies_to`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_messages` (`customer_id`,`created_at`),
  ADD KEY `idx_conversation` (`conversation_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_color_images`
--
ALTER TABLE `product_color_images`
  ADD PRIMARY KEY (`color_id`),
  ADD KEY `fk_product_color_images_product` (`product_id`);

--
-- Indexes for table `product_requests`
--
ALTER TABLE `product_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_review_order_item` (`order_item_id`),
  ADD KEY `idx_product_reviews` (`product_id`,`status`,`created_at`),
  ADD KEY `idx_customer_reviews` (`customer_id`,`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_customer_vouchers` (`customer_id`,`status`);

--
-- Indexes for table `warranty_claims`
--
ALTER TABLE `warranty_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_claims` (`customer_id`,`created_at`),
  ADD KEY `idx_order_claims` (`order_id`),
  ADD KEY `idx_claim_status` (`status`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_customer_product` (`customer_id`,`product_id`),
  ADD KEY `idx_wishlist_customer` (`customer_id`,`created_at`),
  ADD KEY `idx_wishlist_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `product_color_images`
--
ALTER TABLE `product_color_images`
  MODIFY `color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_requests`
--
ALTER TABLE `product_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `warranty_claims`
--
ALTER TABLE `warranty_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `product_color_images`
--
ALTER TABLE `product_color_images`
  ADD CONSTRAINT `fk_product_color_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

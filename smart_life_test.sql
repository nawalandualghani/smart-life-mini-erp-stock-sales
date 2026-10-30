-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 30, 2026 at 03:25 PM
-- Server version: 5.7.40
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_life_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'إلكترونيات'),
(2, 'قرطاسية');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`) VALUES
(1, 'عميل نقدي', NULL),
(2, 'شركة تجريبية', '770000000');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `warehouse_id` int(10) UNSIGNED NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total` decimal(14,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoices_warehouse_date` (`warehouse_id`,`created_at`),
  KEY `idx_invoices_customer` (`customer_id`),
  KEY `idx_invoices_creator` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `customer_id`, `warehouse_id`, `created_by`, `subtotal`, `discount_percent`, `discount_amount`, `total`, `created_at`) VALUES
(5, 2, 2, 1, '25.00', '0.00', '0.00', '25.00', '2026-10-30 11:13:45'),
(6, 1, 1, 1, '20.00', '0.00', '0.00', '20.00', '2026-10-30 11:18:36');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `line_total` decimal(14,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_product` (`invoice_id`,`product_id`),
  KEY `idx_invoice_items_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `product_code`, `product_name`, `quantity`, `unit_price`, `line_total`) VALUES
(5, 5, 1, 'EL-001', 'لوحة مفاتيح', 1, '25.00', '25.00'),
(6, 6, 44, 'DEMO-001', 'منتج تجربة البيع', 2, '10.00', '20.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `selling_price` decimal(12,2) NOT NULL,
  `is_active` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_code` (`code`),
  KEY `idx_products_category_active` (`category_id`,`is_active`),
  KEY `idx_products_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `code`, `name`, `selling_price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'EL-001', 'لوحة مفاتيح', '25.00', 1, '2026-10-30 10:08:56', '2026-10-30 10:08:56'),
(2, 1, 'EL-002', 'فأرة حاسوب', '12.50', 1, '2026-10-30 10:08:56', '2026-10-30 10:08:56'),
(3, 2, 'ST-001', 'دفتر', '3.75', 1, '2026-10-30 10:08:56', '2026-10-30 10:08:56'),
(27, 2, '057365', 'طبشور', '33.00', 1, '2026-10-30 10:42:16', '2026-10-30 11:39:57'),
(44, 2, 'DEMO-001', 'منتج تجربة البيع', '10.00', 1, '2026-10-30 11:16:18', '2026-10-30 11:47:11'),
(88, 1, 'UI-DEMO-001', 'سماعة سلكية', '18.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(89, 1, 'UI-DEMO-002', 'سماعة بلوتوث', '35.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(90, 1, 'UI-DEMO-003', 'شاحن هاتف', '15.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(91, 1, 'UI-DEMO-004', 'كابل USB', '5.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(92, 1, 'UI-DEMO-005', 'ذاكرة فلاش 32 جيجا', '12.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(93, 1, 'UI-DEMO-006', 'حامل هاتف', '8.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(94, 1, 'UI-DEMO-007', 'موزع USB', '20.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(95, 1, 'UI-DEMO-008', 'كاميرا ويب', '45.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(96, 1, 'UI-DEMO-009', 'لوحة ماوس', '6.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(97, 1, 'UI-DEMO-010', 'مكبر صوت', '30.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(98, 2, 'UI-DEMO-011', 'قلم أزرق', '1.50', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(99, 2, 'UI-DEMO-012', 'قلم رصاص', '1.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(100, 2, 'UI-DEMO-013', 'ممحاة', '0.50', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(101, 2, 'UI-DEMO-014', 'مسطرة', '2.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(102, 2, 'UI-DEMO-015', 'دفتر ملاحظات', '4.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(103, 2, 'UI-DEMO-016', 'ملف مستندات', '3.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(104, 2, 'UI-DEMO-017', 'دباسة', '7.50', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(105, 2, 'UI-DEMO-018', 'علبة دبابيس', '2.50', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(106, 2, 'UI-DEMO-019', 'ورق طباعة A4', '10.00', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05'),
(107, 2, 'UI-DEMO-020', 'أقلام تلوين', '6.50', 1, '2026-10-30 12:18:05', '2026-10-30 12:18:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','warehouse_user') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `fk_users_warehouse` (`warehouse_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `warehouse_id`, `name`, `username`, `password_hash`, `role`, `is_active`) VALUES
(1, NULL, 'admin', 'admin', '$2y$10$SXypEhAFZPwXnz4CoPh31.J3PS9cxoTT59AjYkfcwezv1v0XY.//S', 'admin', 1),
(2, 1, 'warehouse1', 'warehouse1', '$2y$10$j6.0sDwz2Nb2lZtwm7DTXuAjFmkCAE5cgGrhTl11uk2.iZVKrdLIy', 'warehouse_user', 1);

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE IF NOT EXISTS `warehouses` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_warehouses_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`) VALUES
(1, 'المستودع الرئيسي'),
(2, 'مستودع الفرع');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_stock`
--

DROP TABLE IF EXISTS `warehouse_stock`;
CREATE TABLE IF NOT EXISTS `warehouse_stock` (
  `warehouse_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `alert_quantity` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`warehouse_id`,`product_id`),
  KEY `idx_stock_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouse_stock`
--

INSERT INTO `warehouse_stock` (`warehouse_id`, `product_id`, `quantity`, `alert_quantity`, `updated_at`) VALUES
(1, 1, 10, 3, '2026-10-30 10:08:56'),
(1, 2, 2, 5, '2026-10-30 10:08:56'),
(1, 3, 20, 5, '2026-10-30 14:42:17'),
(1, 44, 8, 3, '2026-10-30 11:18:37'),
(2, 1, 0, 2, '2026-10-30 11:13:45'),
(2, 2, 0, 5, '2026-10-30 10:08:56'),
(2, 3, 15, 5, '2026-10-30 10:08:56'),
(2, 44, 20, 2, '2026-10-30 11:46:41');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_invoices_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_invoices_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  ADD CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_stock_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES=0 */;

-- Use the correct database
USE `u735263260_pos`;

-- Dumping structure for table categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Category` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

INSERT INTO `categories` (`id`, `Category`, `Date`) VALUES
	(8, 'MOUSE', '2025-06-30 06:13:17'),
	(9, 'KEYBOARD', '2025-06-30 12:11:25'),
	(10, 'MONITOR', '2025-06-30 12:11:30'),
	(11, 'CPU', '2025-06-30 06:13:58'),
	(12, 'GPU', '2025-06-30 06:14:03'),
	(13, 'MOTHERBOARD', '2025-06-30 12:11:40'),
	(14, 'HEADSET', '2025-06-30 12:10:58');

-- Table: customers
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `idDocument` int NOT NULL,
  `email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `phone` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `birthdate` date NOT NULL,
  `purchases` int NOT NULL,
  `lastPurchase` datetime NOT NULL,
  `registerDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

INSERT INTO `customers` VALUES
(12, 'Dexter', 43, 'dexter@gmail.com', '09559332133', 'Bunao', '1998-02-22', 14, '2025-07-02 09:11:19', '2025-07-02 14:11:19'),
(13, 'Kei', 102, 'kei@gmail.com', '09559332133', 'Bunao', '1998-02-22', 6, '2025-07-01 09:13:06', '2025-07-01 14:13:07');

-- Table: products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idCategory` int NOT NULL,
  `code` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `image` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `stock` int NOT NULL,
  `buyingPrice` float NOT NULL,
  `sellingPrice` float NOT NULL,
  `sales` int NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

INSERT INTO `products` VALUES
(68, 4, '525', 'Product Sample Eleven', 'views/img/products/default/anonymous.png', 26, 120, 168, 0, '2025-06-30 06:54:01'),
(69, 8, '5446', 'a4tech mouse', 'views/img/products/5446/651.jpg', 20, 199, 278.6, 0, '2025-06-30 06:20:19'),
(70, 9, '5446', 'a4tech keyboard', 'views/img/products/5446/381.jpg', 20, 299, 418.6, 0, '2025-06-30 13:22:07'),
(71, 13, '5446', 'monitor', 'views/img/products/5446/833.jpg', 17, 4000, 5600, 3, '2025-06-30 06:53:13'),
(72, 9, 'Aula-f75', 'Aulaf75', 'views/img/products/Aula-f75/322.jpg', 6, 999, 1398.6, 15, '2025-07-03 06:03:40'),
(73, 0, 'asdasd', 'adasdas', 'views/img/products/default/anonymous.png', 12, 1223, 1712.2, 0, '2025-06-30 13:22:15'),
(74, 0, '12312', 'dasd', 'views/img/products/default/anonymous.png', 10, 123, 172.2, 2, '2025-06-30 13:53:14'),
(75, 9, '123123-', 'asdasd', 'views/img/products/default/anonymous.png', 12, 123, 172.2, 0, '2025-06-30 13:22:13');

-- Table: sales
CREATE TABLE IF NOT EXISTS `sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` int NOT NULL,
  `idCustomer` int NOT NULL,
  `idSeller` int NOT NULL,
  `products` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `tax` int NOT NULL,
  `netPrice` float NOT NULL,
  `totalPrice` float NOT NULL,
  `customerCash` float DEFAULT NULL,
  `paymentMethod` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `saledate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

INSERT INTO `sales` VALUES
(23, 10014, 12, 1, '[{"id":"71","description":"monitor","quantity":"1","stock":"17","price":"5600","totalPrice":"5600"}]', 0, 0, 5600, NULL, 'cash', '2025-06-30 06:53:13'),
(29, 10015, 12, 1, '[{"id":"74","description":"dasd","quantity":"1","stock":"11","price":"172.2","totalPrice":"172.2"}]', 0, 0, 0, NULL, 'cash', '2025-06-30 13:26:15'),
(30, 10016, 12, 1, '[{"id":"74","description":"dasd","quantity":"1","stock":"10","price":"172.2","totalPrice":172.2}]', 0, 172.2, 172.2, NULL, 'cash', '2025-06-30 13:53:14'),
(31, 10017, 12, 4, '[{"id":"72","description":"Aulaf75","quantity":"1","stock":"19","price":"1398.6","totalPrice":1398.6}]', 0, 0, 1398.6, NULL, 'cash', '2025-07-01 13:33:42'),
(32, 10018, 13, 4, '[{"id":"72","description":"Aulaf75","quantity":"1","stock":"18","price":"1398.6","totalPrice":1398.6}]', 0, 1398.6, 1398.6, NULL, 'cash', '2025-07-01 13:38:29'),
(33, 10019, 12, 4, '[{"id":"72","description":"Aulaf75","quantity":"1","stock":"5","price":"1398.6","totalPrice":1398.6}]', 0, 1398.6, 1398.6, NULL, 'cash', '2025-07-02 14:11:19');

-- Table: users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `user` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `password` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `profile` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `photo` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `status` int NOT NULL,
  `lastLogin` datetime NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

INSERT INTO `users` VALUES
(1, 'Administrator', 'admin', '$2a$07$asxx54ahjppf45sd87a5auXBm1Vr2M1NV5t/zNQtGHGpS5fFirrbG', 'Administrator', 'views/img/users/admin/admin-icn.png', 1, '2025-07-03 22:07:20', '2025-07-04 03:07:20'),
(4, 'admin1', 'admin1', '$2a$07$asxx54ahjppf45sd87a5auq7Jv7frVPvHwXetZz5rg8WwwwDkB0L2', 'Administrator', 'views/img/users/default/prfplaceholder.png', 1, '2025-07-03 00:48:57', '2025-07-03 05:48:57'),
(5, 'Queenie', 'cashier', '$2a$07$asxx54ahjppf45sd87a5auq7Jv7frVPvHwXetZz5rg8WwwwDkB0L2', 'Seller', 'views/img/users/default/prfplaceholder.png', 1, '2025-07-01 00:38:30', '2025-07-01 05:38:30'),
(6, 'Kei', 'Kei', '$2a$07$asxx54ahjppf45sd87a5auq7Jv7frVPvHwXetZz5rg8WwwwDkB0L2', 'Special', 'views/img/users/default/prfplaceholder.png', 1, '2025-07-01 05:29:59', '2025-07-01 10:29:59');

-- Reset session variables
/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

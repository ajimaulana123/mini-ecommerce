-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versi server:                 5.6.23 - MySQL Community Server (GPL)
-- OS Server:                    Win64
-- HeidiSQL Versi:               12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Membuang struktur basisdata untuk ecommerce
CREATE DATABASE IF NOT EXISTS `ecommerce` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `ecommerce`;

-- membuang struktur untuk table ecommerce.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(36) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `total_price` int(11) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;

-- Membuang data untuk tabel ecommerce.orders: ~2 rows (lebih kurang)
INSERT INTO `orders` (`id`, `user_id`, `product_id`, `product_name`, `qty`, `total_price`, `status`, `payment_method`, `created_at`, `updated_at`) VALUES
	(35, 60852, '550e8400-e29b-41d4-a716-446655440001', 'Batik Solo Eksklusif', 1, 250000, 'success', 'COD', '2026-01-14 17:25:44', '2026-01-14 17:25:44'),
	(36, 60852, '550e8400-e29b-41d4-a716-446655440000', 'Kopi Luwak Asli', 4, 1400000, 'failed', 'Mandiri', '2026-01-14 17:26:15', '2026-01-14 17:26:15');

-- membuang struktur untuk table ecommerce.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_id` varchar(100) NOT NULL,
  `amount` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_id` (`payment_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- Membuang data untuk tabel ecommerce.payments: ~2 rows (lebih kurang)
INSERT INTO `payments` (`id`, `order_id`, `payment_id`, `amount`, `payment_method`, `status`, `created_at`, `updated_at`) VALUES
	(20, 35, 'PAY-7C3764B3', 250000, 'COD', 'success', '2026-01-15 00:25:44', '2026-01-14 17:25:44'),
	(21, 36, 'PAY-174A0F12', 1400000, 'Mandiri', 'failed', '2026-01-15 00:26:15', '2026-01-14 17:26:15');

-- membuang struktur untuk table ecommerce.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `description` text,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Membuang data untuk tabel ecommerce.products: ~5 rows (lebih kurang)
INSERT INTO `products` (`id`, `name`, `price`, `qty`, `description`, `image_url`, `created_at`) VALUES
	('550e8400-e29b-41d4-a716-446655440000', 'Kopi Luwak Asli', 350000, 65, 'Kopi Luwak premium dari Indonesia, cita rasa kaya & aromatik', 'https://images.pexels.com/photos/414645/pexels-photo-414645.jpeg', '2026-01-14 10:48:07'),
	('550e8400-e29b-41d4-a716-446655440001', 'Batik Solo Eksklusif', 250000, 22, 'Batik asli Solo bermotif tradisional untuk fashion & dekorasi', 'https://images.pexels.com/photos/4602437/pexels-photo-4602437.jpeg', '2026-01-14 10:48:07'),
	('550e8400-e29b-41d4-a716-446655440002', 'Kerajinan Anyaman Rotan', 150000, 69, 'Kerajinan tangan rotan Indonesia, cocok untuk rumah & hadiah', 'https://images.pexels.com/photos/459918/pexels-photo-459918.jpeg', '2026-01-14 10:48:07'),
	('550e8400-e29b-41d4-a716-446655440003', 'Teh Hijau Nusantara', 120000, 50, 'Teh hijau khas Indonesia, segar & menyehatkan', 'https://images.pexels.com/photos/461428/pexels-photo-461428.jpeg', '2026-01-14 10:48:07'),
	('550e8400-e29b-41d4-a716-446655440004', 'Coklat Bali Premium', 95000, 33, 'Coklat artisan dari Bali, rasa manis & tekstur lembut', 'https://images.pexels.com/photos/302899/pexels-photo-302899.jpeg', '2026-01-14 10:48:07');

-- membuang struktur untuk table ecommerce.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2147483647 DEFAULT CHARSET=latin1;

-- Membuang data untuk tabel ecommerce.users: ~1 rows (lebih kurang)
INSERT INTO `users` (`id`, `email`, `password`, `created_at`, `updated_at`) VALUES
	(60852, 'user@gmail.com', '$2y$10$5nha6q7EDOpdoHN7PAB.u.jQgZVLJuaE6xlxjKyL1Ig7NLrBczPsi', NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

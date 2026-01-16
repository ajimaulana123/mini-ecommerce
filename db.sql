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
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=latin1;

-- Membuang data untuk tabel ecommerce.orders: ~2 rows (lebih kurang)
INSERT INTO `orders` (`id`, `user_id`, `product_id`, `product_name`, `qty`, `total_price`, `status`, `payment_method`, `created_at`, `updated_at`) VALUES
	(155, 60852, '550e8400-e29b-41d4-a716-446655440000', 'Kopi Luwak Asli', 5, 1750000, 'success', 'bank_transfer', '2026-01-16 06:28:21', '2026-01-16 06:28:49'),
	(156, 60852, '550e8400-e29b-41d4-a716-446655440004', 'Coklat Bali Premium', 1, 95000, 'failed', 'credit_card', '2026-01-16 06:30:08', '2026-01-16 06:30:15');

-- membuang struktur untuk table ecommerce.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_id` varchar(100) NOT NULL,
  `external_id` varchar(100) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `payment_url` varchar(500) DEFAULT NULL,
  `callback_url` varchar(500) DEFAULT NULL,
  `merchant_id` varchar(50) DEFAULT 'MOCK_MERCHANT_001',
  `expiry_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_id` (`payment_id`),
  UNIQUE KEY `external_id` (`external_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=latin1;

-- Membuang data untuk tabel ecommerce.payments: ~2 rows (lebih kurang)
INSERT INTO `payments` (`id`, `order_id`, `payment_id`, `external_id`, `amount`, `payment_method`, `status`, `created_at`, `updated_at`, `payment_url`, `callback_url`, `merchant_id`, `expiry_time`) VALUES
	(115, 155, 'PAY-3E0ECA83', 'MOCK-1768544911-4557', 1750000, 'bank_transfer', 'success', '2026-01-16 06:28:31', '2026-01-16 06:28:49', '/payment/mock-checkout/MOCK-1768544911-4557', 'http://localhost:8888/api/v1/mock-payments/webhook', 'MOCK_MERCHANT_001', '2026-01-17 06:28:31'),
	(116, 156, 'PAY-C921E475', 'MOCK-1768545011-4383', 95000, 'credit_card', 'failed', '2026-01-16 06:30:11', '2026-01-16 06:30:15', '/payment/mock-checkout/MOCK-1768545011-4383', 'http://localhost:8888/api/v1/mock-payments/webhook', 'MOCK_MERCHANT_001', '2026-01-17 06:30:11');

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
	('550e8400-e29b-41d4-a716-446655440000', 'Kopi Luwak Asli', 350000, 30, 'Kopi Luwak premium dari Indonesia, cita rasa kaya & aromatik', 'https://images.pexels.com/photos/414645/pexels-photo-414645.jpeg', '2026-01-14 10:48:07'),
	('550e8400-e29b-41d4-a716-446655440001', 'Batik Solo Eksklusif', 250000, 0, 'Batik asli Solo bermotif tradisional untuk fashion & dekorasi', 'https://images.pexels.com/photos/4602437/pexels-photo-4602437.jpeg', '2026-01-14 10:48:07'),
	('550e8400-e29b-41d4-a716-446655440002', 'Kerajinan Anyaman Rotan', 150000, 63, 'Kerajinan tangan rotan Indonesia, cocok untuk rumah & hadiah', 'https://images.pexels.com/photos/459918/pexels-photo-459918.jpeg', '2026-01-14 10:48:07'),
	('550e8400-e29b-41d4-a716-446655440003', 'Teh Hijau Nusantara', 120000, 35, 'Teh hijau khas Indonesia, segar & menyehatkan', 'https://images.pexels.com/photos/461428/pexels-photo-461428.jpeg', '2026-01-14 10:48:07'),
	('550e8400-e29b-41d4-a716-446655440004', 'Coklat Bali Premium', 95000, 18, 'Coklat artisan dari Bali, rasa manis & tekstur lembut', 'https://images.pexels.com/photos/302899/pexels-photo-302899.jpeg', '2026-01-14 10:48:07');

-- membuang struktur untuk table ecommerce.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60853 DEFAULT CHARSET=latin1;

-- Membuang data untuk tabel ecommerce.users: ~1 rows (lebih kurang)
INSERT INTO `users` (`id`, `email`, `password`, `created_at`, `updated_at`) VALUES
	(60852, 'user@gmail.com', '$2y$10$5nha6q7EDOpdoHN7PAB.u.jQgZVLJuaE6xlxjKyL1Ig7NLrBczPsi', NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

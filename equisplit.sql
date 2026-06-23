-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for equisplit


-- Dumping structure for table equisplit.expenses
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `participant_id` int NOT NULL,
  `receipt_id` int DEFAULT NULL,
  `waypoint_id` int DEFAULT NULL,
  `description` varchar(200) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `expense_type` enum('personal','group') NOT NULL,
  `split_type` enum('equal','proportional') NOT NULL,
  `created_at` datetime NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `participant_id` (`participant_id`),
  KEY `fk_expenses_receipt` (`receipt_id`),
  KEY `waypoint_id` (`waypoint_id`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `group_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_expenses_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `receipts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_expenses_waypoint_new` FOREIGN KEY (`waypoint_id`) REFERENCES `session_waypoints` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.expenses: ~9 rows (approximately)
INSERT INTO `expenses` (`id`, `session_id`, `participant_id`, `receipt_id`, `waypoint_id`, `description`, `total_amount`, `quantity`, `expense_type`, `split_type`, `created_at`, `image_url`) VALUES
	(32, 13, 35, NULL, NULL, 'Coke can', 2.00, 1, 'group', 'equal', '2026-05-24 20:51:52', 'https://res.cloudinary.com/dpk0usbzb/image/upload/v1779628373/equisplit/receipts/session_13/hayu2xkcwj7qo1itwzzd.png'),
	(33, 13, 34, 2, NULL, 'ayam', 24.00, 2, 'group', 'equal', '2026-05-24 21:16:01', NULL),
	(34, 13, 34, 2, NULL, 'egg', 12.00, 2, 'group', 'equal', '2026-05-24 21:16:01', NULL),
	(35, 17, 41, NULL, 1, 'Fuel', 50.00, 1, 'group', 'equal', '2026-05-31 04:26:49', NULL),
	(36, 17, 41, NULL, 4, 'Toll expenses', 2.00, 1, 'group', 'equal', '2026-05-31 04:27:28', NULL),
	(37, 17, 41, NULL, 3, 'Fuel 2', 40.00, 1, 'group', 'equal', '2026-05-31 04:28:04', NULL),
	(38, 17, 41, 3, 3, 'Nasi Lemak', 7.00, 1, 'group', 'equal', '2026-05-31 04:30:15', NULL),
	(39, 17, 41, 3, 3, 'Teh o ais', 5.00, 2, 'group', 'equal', '2026-05-31 04:30:15', NULL),
	(40, 17, 41, 3, 3, 'Mee goreng', 5.00, 1, 'group', 'equal', '2026-05-31 04:30:15', NULL);

-- Dumping structure for table equisplit.expense_allocations
CREATE TABLE IF NOT EXISTS `expense_allocations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `expense_id` int NOT NULL,
  `participant_id` int NOT NULL,
  `amount_owed` decimal(10,2) NOT NULL,
  `is_payer` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `expense_id` (`expense_id`),
  KEY `participant_id` (`participant_id`),
  CONSTRAINT `expense_allocations_ibfk_1` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `expense_allocations_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.expense_allocations: ~21 rows (approximately)
INSERT INTO `expense_allocations` (`id`, `expense_id`, `participant_id`, `amount_owed`, `is_payer`) VALUES
	(120, 32, 35, 0.00, 1),
	(121, 32, 34, 2.00, 0),
	(122, 33, 34, 0.00, 1),
	(123, 33, 34, 12.00, 0),
	(124, 33, 35, 12.00, 0),
	(125, 34, 34, 0.00, 1),
	(126, 34, 34, 6.00, 0),
	(127, 34, 35, 6.00, 0),
	(128, 35, 41, 0.00, 1),
	(129, 35, 41, 25.00, 0),
	(130, 35, 43, 25.00, 0),
	(131, 36, 41, 0.00, 1),
	(132, 36, 41, 1.00, 0),
	(133, 36, 43, 1.00, 0),
	(134, 37, 41, 0.00, 1),
	(135, 37, 41, 20.00, 0),
	(136, 37, 43, 20.00, 0),
	(137, 38, 41, 0.00, 1),
	(138, 38, 41, 7.00, 0),
	(139, 39, 41, 0.00, 1),
	(140, 39, 41, 2.50, 0),
	(141, 39, 43, 2.50, 0),
	(142, 40, 41, 0.00, 1),
	(143, 40, 43, 5.00, 0);

-- Dumping structure for table equisplit.group_sessions
CREATE TABLE IF NOT EXISTS `group_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `host_id` int NOT NULL,
  `preset_type` enum('dining','road_trip','long_trip','custom','grocery') NOT NULL,
  `status` enum('open','locked','closed') NOT NULL DEFAULT 'open',
  `max_participants` int NOT NULL,
  `created_at` datetime NOT NULL,
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_UUID` (`uuid`),
  KEY `host_id` (`host_id`),
  CONSTRAINT `group_sessions_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.group_sessions: ~4 rows (approximately)
INSERT INTO `group_sessions` (`id`, `uuid`, `name`, `host_id`, `preset_type`, `status`, `max_participants`, `created_at`, `closed_at`) VALUES
	(13, 'e5c4da6a-e440-498e-99ae-a0b19efda526', 'lotus', 1, 'grocery', 'closed', 4, '2026-05-24 20:44:03', '2026-05-31 02:57:16'),
	(16, 'aaab06b9-105b-4b58-b15c-f8deb657a703', 'Penang Ride', 1, 'road_trip', 'open', 3, '2026-05-31 03:28:30', NULL),
	(17, '6007ada6-fabc-407d-a6bc-a1c9883c9cd3', 'Kedah', 1, 'road_trip', 'open', 4, '2026-05-31 03:56:31', NULL),
	(18, 'f07d5616-fe2c-4537-b9f8-e09e54c5dbf7', 'test123', 1, 'dining', 'open', 10, '2026-05-31 04:12:52', NULL);

-- Dumping structure for table equisplit.participants
CREATE TABLE IF NOT EXISTS `participants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `role` enum('host','registered','guest') NOT NULL,
  `joined_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `group_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.participants: ~6 rows (approximately)
INSERT INTO `participants` (`id`, `session_id`, `user_id`, `guest_name`, `role`, `joined_at`) VALUES
	(34, 13, 1, NULL, 'host', '2026-05-24 20:44:03'),
	(35, 13, 2, NULL, 'registered', '2026-05-24 20:44:45'),
	(39, 13, NULL, 'Karreem', 'guest', '2026-05-31 02:35:22'),
	(40, 16, 1, NULL, 'host', '2026-05-31 03:28:30'),
	(41, 17, 1, NULL, 'host', '2026-05-31 03:56:31'),
	(42, 18, 1, NULL, 'host', '2026-05-31 04:12:52'),
	(43, 17, 2, NULL, 'registered', '2026-05-31 04:13:46');

-- Dumping structure for table equisplit.payment_proofs
CREATE TABLE IF NOT EXISTS `payment_proofs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `participant_id` int NOT NULL,
  `settlement_transaction_id` int DEFAULT NULL,
  `proof_url` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `submitted_at` datetime NOT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `participant_id` (`participant_id`),
  KEY `fk_pp_settlement` (`settlement_transaction_id`),
  CONSTRAINT `fk_pp_settlement` FOREIGN KEY (`settlement_transaction_id`) REFERENCES `settlement_transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_proofs_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `group_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payment_proofs_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.payment_proofs: ~0 rows (approximately)

-- Dumping structure for table equisplit.phinxlog
CREATE TABLE IF NOT EXISTS `phinxlog` (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.phinxlog: ~0 rows (approximately)
INSERT INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
	(20260517154301, 'CreateUsers', '2026-05-17 00:48:10', '2026-05-17 00:48:10', 0),
	(20260517154302, 'CreateGroupSessions', '2026-05-17 00:48:10', '2026-05-17 00:48:10', 0),
	(20260517154303, 'CreateParticipants', '2026-05-17 00:48:10', '2026-05-17 00:48:10', 0),
	(20260517154304, 'CreateSessionCharges', '2026-05-17 00:48:10', '2026-05-17 00:48:11', 0),
	(20260517154305, 'CreateExpenses', '2026-05-17 00:48:11', '2026-05-17 00:48:11', 0),
	(20260517154306, 'CreateExpenseAllocations', '2026-05-17 00:48:11', '2026-05-17 00:48:11', 0),
	(20260517154307, 'CreatePaymentProofs', '2026-05-17 00:48:11', '2026-05-17 00:48:11', 0),
	(20260517154308, 'CreateSettlementTransactions', '2026-05-17 00:48:11', '2026-05-17 00:48:11', 0),
	(20260517154309, 'CreateSessionNotifications', '2026-05-17 00:48:11', '2026-05-17 00:48:12', 0);

-- Dumping structure for table equisplit.receipts
CREATE TABLE IF NOT EXISTS `receipts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `payer_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_receipts_session` (`session_id`),
  KEY `fk_receipts_payer` (`payer_id`),
  CONSTRAINT `fk_receipts_payer` FOREIGN KEY (`payer_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_receipts_session` FOREIGN KEY (`session_id`) REFERENCES `group_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.receipts: ~1 rows (approximately)
INSERT INTO `receipts` (`id`, `session_id`, `payer_id`, `name`, `created_at`, `image_url`) VALUES
	(2, 13, 34, 'Lotus', '2026-05-24 21:16:01', 'https://res.cloudinary.com/dpk0usbzb/image/upload/v1779628560/equisplit/receipts/session_13/d0oyfxl6kb19nwfpsuxv.png'),
	(3, 17, 41, 'Eat at RNR Perak River', '2026-05-31 04:30:15', NULL);

-- Dumping structure for table equisplit.session_charges
CREATE TABLE IF NOT EXISTS `session_charges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `charge_name` varchar(100) NOT NULL,
  `charge_type` enum('percentage','flat') NOT NULL,
  `charge_value` decimal(8,2) NOT NULL,
  `applies_to` enum('proportional','equal') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `session_charges_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `group_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.session_charges: ~2 rows (approximately)
INSERT INTO `session_charges` (`id`, `session_id`, `charge_name`, `charge_type`, `charge_value`, `applies_to`) VALUES
	(17, 18, 'SST', 'percentage', 6.00, 'proportional'),
	(18, 18, 'Service Charge', 'percentage', 10.00, 'proportional');

-- Dumping structure for table equisplit.session_notifications
CREATE TABLE IF NOT EXISTS `session_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `participant_id` int NOT NULL,
  `type` enum('reupload_request','approved','rejected') NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `participant_id` (`participant_id`),
  CONSTRAINT `session_notifications_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `group_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `session_notifications_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.session_notifications: ~0 rows (approximately)

-- Dumping structure for table equisplit.session_waypoints
CREATE TABLE IF NOT EXISTS `session_waypoints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `type` enum('start','stop','toll','destination') NOT NULL,
  `name` varchar(255) NOT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `session_waypoints_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `group_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.session_waypoints: ~4 rows (approximately)
INSERT INTO `session_waypoints` (`id`, `session_id`, `type`, `name`, `lat`, `lng`, `sort_order`, `created_at`) VALUES
	(1, 17, 'start', 'Kajang Municipal Council', 2.99484370, 101.78965950, 0, '2026-05-31 03:58:43'),
	(2, 17, 'destination', 'Sungai Petani', 5.64351950, 100.48695130, 1, '2026-05-31 03:59:28'),
	(3, 17, 'stop', 'Perak River', 4.89912400, 100.99509750, 2, '2026-05-31 04:03:37'),
	(4, 17, 'toll', 'Batu Toll Plaza', 3.19215760, 101.67518630, 3, '2026-05-31 04:11:45');

-- Dumping structure for table equisplit.settlement_transactions
CREATE TABLE IF NOT EXISTS `settlement_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `debtor_id` int NOT NULL,
  `creditor_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','settled','unresolved','claimed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `debtor_id` (`debtor_id`),
  KEY `creditor_id` (`creditor_id`),
  CONSTRAINT `settlement_transactions_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `group_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `settlement_transactions_ibfk_2` FOREIGN KEY (`debtor_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `settlement_transactions_ibfk_3` FOREIGN KEY (`creditor_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.settlement_transactions: ~1 rows (approximately)
INSERT INTO `settlement_transactions` (`id`, `session_id`, `debtor_id`, `creditor_id`, `amount`, `status`, `created_at`) VALUES
	(7, 13, 35, 34, 16.00, 'pending', '2026-05-31 02:57:16');

-- Dumping structure for table equisplit.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `google_id` varchar(100) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_EMAIL` (`email`),
  UNIQUE KEY `UNIQUE_GOOGLE_ID` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.users: ~3 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `google_id`, `avatar_url`, `created_at`) VALUES
	(1, 'Muhammad Faries Hafiz Hidayatullah', 'muhammadfarieshafiz@gmail.com', '116739889368013596733', 'https://lh3.googleusercontent.com/a/ACg8ocIn6Ikb79IHsIeWY9R3OWjLrEAymlrD8ox7nyxRNRseyT8whQ=s96-c', '2026-05-18 23:39:15'),
	(2, 'Faries Hafiz', 'farieshafiz@gmail.com', '107582401726402520024', 'https://lh3.googleusercontent.com/a/ACg8ocLE9Zxh_YJ1p8MFz0cYOOIxEYjuZ5qFfmFdRDLn-B8N_Ohs-w=s96-c', '2026-05-24 19:06:45'),
	(3, 'Malique Kareem', 'farieshafiz2003@gmail.com', '118189319779246244653', 'https://lh3.googleusercontent.com/a/ACg8ocJxVFjCG6F_a9bpvIpqHwrNZjJ1iZIyCnbiGWeDIBbVdjMWb_E=s96-c', '2026-05-29 22:31:38');

-- Dumping structure for table equisplit.user_payment_methods
CREATE TABLE IF NOT EXISTS `user_payment_methods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `method_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_value` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_public_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_upm_user` (`user_id`),
  CONSTRAINT `fk_upm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table equisplit.user_payment_methods: ~7 rows (approximately)
INSERT INTO `user_payment_methods` (`id`, `user_id`, `method_type`, `label`, `account_name`, `account_value`, `bank_name`, `qr_image_url`, `qr_public_id`, `created_at`) VALUES
	(2, 1, 'duitnow_qr', 'Maybank QR', NULL, NULL, NULL, 'https://res.cloudinary.com/dpk0usbzb/image/private/s--h_t-g1ju--/v1779621422/equisplit/payment_qr/user_1/hlfwoqllolszqib7icuf.png', 'equisplit/payment_qr/user_1/hlfwoqllolszqib7icuf', '2026-05-24 19:17:03'),
	(3, 2, 'duitnow_qr', 'OCBC', NULL, NULL, NULL, 'https://res.cloudinary.com/dpk0usbzb/image/private/s--Ef0B5c7X--/v1779621652/equisplit/payment_qr/user_2/eodz0w70zreutf02ohsp.png', 'equisplit/payment_qr/user_2/eodz0w70zreutf02ohsp', '2026-05-24 19:20:53'),
	(4, 2, 'bank_transfer', 'CIMB Bank account', 'Faries hafiz', '5140 1234 5678', 'CIMB', NULL, NULL, '2026-05-24 19:22:20'),
	(5, 1, 'bank_transfer', 'Savings Account', 'faries', '7019876543', 'CIMB', NULL, NULL, '2026-05-24 19:26:13'),
	(6, 1, 'duitnow_id', 'Personal Phone', NULL, '0123456789', NULL, NULL, NULL, '2026-05-24 19:26:51'),
	(7, 1, 'tng', 'Personal TnG', NULL, '0179876543', NULL, NULL, NULL, '2026-05-24 19:27:42'),
	(8, 1, 'paypal', 'Personal PayPal', NULL, 'faries.hafiz@example.com', NULL, NULL, NULL, '2026-05-24 19:28:35');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

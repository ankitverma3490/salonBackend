-- Database Export from Local MySQL
-- Database: salon_booking
-- Generated: 2026-02-14 08:48:45
-- 

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- --------------------------------------------------------
-- Table structure for table `admin_activity_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `admin_activity_logs`;

CREATE TABLE `admin_activity_logs` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `admin_id` varchar(36) NOT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(100) NOT NULL,
  `entity_id` varchar(36) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_entity_type` (`entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `booking_reviews`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `booking_reviews`;

CREATE TABLE `booking_reviews` (
  `id` varchar(36) NOT NULL,
  `booking_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `rating` int(11) DEFAULT 5,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `salon_id` (`salon_id`),
  CONSTRAINT `booking_reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_reviews_ibfk_3` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `booking_reviews`
-- 2 rows

INSERT INTO `booking_reviews` (`id`, `booking_id`, `user_id`, `salon_id`, `rating`, `comment`, `created_at`) VALUES
('839c38ef-7ec7-4463-9e1b-55fbed17c762', 'a757c2b4-5c2e-4ed2-bcd1-c91a7db53fac', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', '5', 'this is good service.', '2026-02-02 21:15:57'),
('a66c82c2-2518-49f9-96a6-0a01b699560d', '1a0ce12a-df55-4db9-8c10-a2161b837a96', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', '3cb7202f-f091-41f5-92de-8a4f35990713', '5', 'this is good ', '2026-02-01 20:11:01');


-- --------------------------------------------------------
-- Table structure for table `bookings`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `bookings`;

CREATE TABLE `bookings` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `service_id` varchar(36) NOT NULL,
  `price_paid` decimal(10,2) DEFAULT NULL,
  `coins_used` decimal(15,2) DEFAULT 0.00,
  `coin_currency_value` decimal(10,4) DEFAULT 1.0000,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `staff_id` varchar(36) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'confirmed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_salon_id` (`salon_id`),
  KEY `idx_booking_date` (`booking_date`),
  KEY `idx_status` (`status`),
  KEY `fk_booking_staff` (`staff_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `bookings`
-- 36 rows

INSERT INTO `bookings` (`id`, `user_id`, `salon_id`, `service_id`, `price_paid`, `coins_used`, `coin_currency_value`, `discount_amount`, `coupon_code`, `staff_id`, `booking_date`, `booking_time`, `status`, `notes`, `created_at`, `updated_at`) VALUES
('03e2907a-83d7-499c-9d69-c6e7455b8c84', '500d3490-5844-4507-9104-d2fc3e873256', '987bb6be-1178-495e-9af7-3819a91a24b0', 'de46370d-200b-44b5-85d5-e0cb3db47d9b', '200.00', '0.00', '1.0000', '0.00', NULL, 'fdc36db5-de3e-4fac-903b-1260782b9160', '2026-02-13', '09:30:00', 'completed', '[GUEST: rahulq1 | +607410258963]', '2026-02-12 01:33:26', '2026-02-12 22:52:39'),
('0512959c-eb55-410b-aedf-a4074930618d', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '10e88c64-35f0-4497-adfb-19d50e716ff1', '250.00', '0.00', '1.0000', '0.00', NULL, 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', '2026-02-13', '09:00:00', 'completed', '[GUEST: test | 789456622] test', '2026-02-13 14:27:17', '2026-02-13 14:29:11'),
('0c9bb788-1d4c-455b-8eca-dcfde1c594a4', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '10e88c64-35f0-4497-adfb-19d50e716ff1', '250.00', '0.00', '1.0000', '0.00', NULL, 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', '2026-02-12', '10:30:00', 'confirmed', '[GUEST: jatin | +607894561230] test', '2026-02-12 19:55:46', '2026-02-12 21:57:17'),
('0ee2e96f-6a36-419e-86dc-f5f4be75c07e', '35128bea-2e85-48e6-91ff-ea99739a74a5', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '10e88c64-35f0-4497-adfb-19d50e716ff1', '250.00', '0.00', '1.0000', '0.00', NULL, 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', '2026-02-12', '10:30:00', 'confirmed', '[GUEST: deepak | +607894561230] test', '2026-02-12 21:30:23', '2026-02-12 21:57:22'),
('104f61e0-4a04-45b8-ab1b-7bead5dfbc29', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '3cb7202f-f091-41f5-92de-8a4f35990713', 'f0eb84da-0e2f-4475-bec2-7d1b86238c24', '40.00', '10.00', '1.0000', '0.00', NULL, NULL, '2026-02-04', '09:30:00', 'cancelled', NULL, '2026-02-03 02:37:29', '2026-02-03 02:37:48'),
('11b2c259-5ff8-4b56-9867-71e04253cdfc', '500d3490-5844-4507-9104-d2fc3e873256', '987bb6be-1178-495e-9af7-3819a91a24b0', 'de46370d-200b-44b5-85d5-e0cb3db47d9b', '200.00', '0.00', '1.0000', '0.00', NULL, 'fdc36db5-de3e-4fac-903b-1260782b9160', '2026-02-13', '09:00:00', 'confirmed', '[GUEST: rahulq1 | 741008520]', '2026-02-12 02:42:54', '2026-02-12 02:47:51'),
('14e49c3d-eb1c-41c8-9bb9-96ec80b63cd4', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '10e88c64-35f0-4497-adfb-19d50e716ff1', '250.00', '0.00', '1.0000', '0.00', NULL, 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', '2026-02-13', '10:30:00', 'completed', '[GUEST: test | 3232232323] test', '2026-02-13 14:32:18', '2026-02-13 14:55:15'),
('17dd9673-b9ba-4ebd-a857-413ea7381bd7', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', '71a9b518-f586-4906-b827-a0cc4619decc', 'c3a8e4ca-59db-4c8a-b98c-49f090938ccd', '13.00', '0.00', '1.0000', '0.00', NULL, '2a41fdbd-fa37-4d13-b8b7-082fd537c030', '2026-02-03', '09:30:00', 'confirmed', NULL, '2026-02-02 00:46:46', '2026-02-02 00:47:28'),
('1a0ce12a-df55-4db9-8c10-a2161b837a96', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', '3cb7202f-f091-41f5-92de-8a4f35990713', 'f0eb84da-0e2f-4475-bec2-7d1b86238c24', '50.00', '0.00', '1.0000', '0.00', NULL, 'e63c3964-9897-41c9-b74a-cfcfbb494102', '2026-02-02', '10:30:00', 'completed', NULL, '2026-02-01 19:18:10', '2026-02-01 20:09:36'),
('1d04f305-12f3-43c5-8865-590ec819986c', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '100.00', '0.00', '1.0000', '0.00', NULL, '70e9375d-8b2e-453f-b833-9d7a2d96a753', '2026-02-04', '09:30:00', 'completed', NULL, '2026-02-03 02:13:05', '2026-02-12 22:52:38'),
('2111001d-82c4-4e52-919f-bb607689f022', '500d3490-5844-4507-9104-d2fc3e873256', '987bb6be-1178-495e-9af7-3819a91a24b0', 'de46370d-200b-44b5-85d5-e0cb3db47d9b', '200.00', '0.00', '1.0000', '0.00', NULL, 'fdc36db5-de3e-4fac-903b-1260782b9160', '2026-02-13', '10:30:00', 'completed', '[GUEST: rahulq1 | +607410258963] this is for test', '2026-02-12 00:59:37', '2026-02-12 22:52:39'),
('24e94f67-722d-499f-838c-1744043652dc', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '10e88c64-35f0-4497-adfb-19d50e716ff1', '250.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-13', '10:30:00', 'confirmed', '[GUEST: test | 3232322] f', '2026-02-13 14:54:27', '2026-02-13 14:54:56'),
('421a2b6c-abfa-419e-b245-faf9911888c1', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '10e88c64-35f0-4497-adfb-19d50e716ff1', '250.00', '0.00', '1.0000', '0.00', NULL, 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', '2026-02-13', '12:00:00', 'completed', '[GUEST: jatin | +607894561230]', '2026-02-13 15:37:58', '2026-02-13 15:51:51'),
('484fb881-5961-4a21-8f18-20ec0f0bbde5', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '100.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-04', '10:00:00', 'cancelled', NULL, '2026-02-03 02:06:37', '2026-02-03 02:07:05'),
('4f8b87b2-08ec-4881-ae83-5af82be20f40', '35128bea-2e85-48e6-91ff-ea99739a74a5', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '10e88c64-35f0-4497-adfb-19d50e716ff1', '250.00', '0.00', '1.0000', '0.00', NULL, 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', '2026-02-12', '10:30:00', 'completed', '[GUEST: deepak | +607894561230] test', '2026-02-12 20:05:29', '2026-02-12 21:54:14'),
('512e2631-d532-4b1e-8f56-ef025b6eef41', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '100.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-04', '09:30:00', 'cancelled', NULL, '2026-02-03 02:01:47', '2026-02-03 02:07:05'),
('588b3f8f-6845-4471-859c-12eb126d1316', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '20.00', '0.00', '1.0000', '0.00', NULL, '70e9375d-8b2e-453f-b833-9d7a2d96a753', '2026-02-06', '10:00:00', 'completed', NULL, '2026-02-03 01:50:25', '2026-02-12 22:52:38'),
('61960abe-a671-43bd-bb49-827d081c5be5', 'bb18903e-be28-4be7-9e0e-583f08149b77', '3cb7202f-f091-41f5-92de-8a4f35990713', 'f0eb84da-0e2f-4475-bec2-7d1b86238c24', '50.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-12', '10:30:00', 'pending', '[GUEST: muskan | 78994561230] test', '2026-02-12 16:25:21', '2026-02-12 16:25:21'),
('652e4653-cd7e-4e4e-9b71-458f3902701c', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '90.00', '10.00', '1.0000', '0.00', NULL, NULL, '2026-02-04', '09:30:00', 'cancelled', NULL, '2026-02-03 02:29:27', '2026-02-03 02:30:06'),
('673c3781-bfe3-4303-8664-4208f9d14cbb', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'de46370d-200b-44b5-85d5-e0cb3db47d9b', '0.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-12', '01:23:00', 'pending', 'rahulq1', '2026-02-12 01:24:39', '2026-02-12 01:24:39'),
('858ef03b-30ea-458a-a453-b2708b7c4a02', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '20.00', '0.00', '1.0000', '0.00', NULL, '70e9375d-8b2e-453f-b833-9d7a2d96a753', '2026-02-03', '09:30:00', 'completed', NULL, '2026-02-02 21:48:47', '2026-02-12 22:52:38'),
('8ab40336-e612-4e5d-9d5d-d2747f3fe518', '500d3490-5844-4507-9104-d2fc3e873256', '987bb6be-1178-495e-9af7-3819a91a24b0', 'de46370d-200b-44b5-85d5-e0cb3db47d9b', '200.00', '0.00', '1.0000', '0.00', NULL, 'fdc36db5-de3e-4fac-903b-1260782b9160', '2026-02-13', '12:00:00', 'confirmed', '[GUEST: Amanjeetsingh | 09882354391]', '2026-02-12 02:07:55', '2026-02-12 02:47:40'),
('92fc25bc-0cb4-42ec-b0a4-e7780abe26c5', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'de46370d-200b-44b5-85d5-e0cb3db47d9b', '0.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-12', '01:23:00', 'pending', 'rahulq1', '2026-02-12 01:25:03', '2026-02-12 01:25:03'),
('98703b80-2f69-460a-9ad2-4a4d379cdfba', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '100.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-04', '09:30:00', 'confirmed', NULL, '2026-02-03 02:02:19', '2026-02-03 02:03:19'),
('a757c2b4-5c2e-4ed2-bcd1-c91a7db53fac', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '20.00', '0.00', '1.0000', '0.00', NULL, '70e9375d-8b2e-453f-b833-9d7a2d96a753', '2026-02-03', '09:30:00', 'completed', NULL, '2026-02-02 21:11:40', '2026-02-02 21:13:42'),
('ab88929e-aabd-45ba-8a65-d6162b096854', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'fdb72c18-07c6-4a09-a289-d5b37229a6e9', '150.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-13', '09:00:00', 'pending', '[GUEST: ankit1 | 7894561230] this is for test', '2026-02-12 00:35:06', '2026-02-12 00:35:06'),
('b95047ba-af37-4a96-a063-5bf461156a5a', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '3cb7202f-f091-41f5-92de-8a4f35990713', 'f0eb84da-0e2f-4475-bec2-7d1b86238c24', '50.00', '0.00', '1.0400', '0.00', NULL, NULL, '2026-02-04', '09:00:00', 'cancelled', NULL, '2026-02-03 01:19:42', '2026-02-03 02:06:50'),
('bbcb9ac4-5f71-43f7-babe-d853ae4c83d0', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '17.00', '0.00', '1.0000', '3.00', 'SMY2026', '70e9375d-8b2e-453f-b833-9d7a2d96a753', '2026-02-03', '09:30:00', 'completed', 'test', '2026-02-02 21:31:42', '2026-02-12 22:52:38'),
('c0172b32-b2e7-4267-b4be-8871f8001a34', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '100.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-04', '09:30:00', 'confirmed', NULL, '2026-02-03 02:36:26', '2026-02-03 02:36:32'),
('cb27be0e-abe7-4dd9-9a7f-0c3b1445b6cc', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '10e88c64-35f0-4497-adfb-19d50e716ff1', '250.00', '0.00', '1.0000', '0.00', NULL, 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', '2026-02-13', '10:30:00', 'completed', '[GUEST: test | 7410852096] test', '2026-02-13 14:25:09', '2026-02-13 14:25:34'),
('da71d2b6-a842-4779-8d42-df75ab0c613b', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '20.00', '0.00', '1.0000', '0.00', NULL, '70e9375d-8b2e-453f-b833-9d7a2d96a753', '2026-02-04', '09:30:00', 'completed', NULL, '2026-02-03 01:41:15', '2026-02-12 22:52:38'),
('e5cd2404-96c9-414a-854f-4781f2d1c85a', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '3cb7202f-f091-41f5-92de-8a4f35990713', 'f0eb84da-0e2f-4475-bec2-7d1b86238c24', '50.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-03', '09:30:00', 'cancelled', NULL, '2026-02-02 21:53:47', '2026-02-03 02:06:53'),
('e86da149-152a-490b-be0f-7ae906a22cd3', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '3cb7202f-f091-41f5-92de-8a4f35990713', 'f0eb84da-0e2f-4475-bec2-7d1b86238c24', '50.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-03', '09:30:00', 'cancelled', NULL, '2026-02-02 21:47:47', '2026-02-03 02:06:55'),
('f08537dd-4827-4234-949c-7e05d78d0a75', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'de46370d-200b-44b5-85d5-e0cb3db47d9b', '0.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-12', '10:00:00', 'cancelled', 'Walk-in: aman | 7894561230', '2026-02-12 00:40:24', '2026-02-12 00:53:30'),
('f96e705a-42c7-48e3-8f4e-da5bb119ba00', '2a25937e-1c25-46a6-bccb-ffaf217a7274', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'd99dea97-1e1b-455c-8dfd-eb21ea1684a0', '20.00', '0.00', '1.0000', '0.00', NULL, '70e9375d-8b2e-453f-b833-9d7a2d96a753', '2026-02-03', '09:30:00', 'completed', 'test', '2026-02-02 21:10:39', '2026-02-12 22:52:38'),
('fe931978-6b0c-4141-9084-ba1c98a2e064', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'ab015886-44d9-4b1d-a1fb-33809a5373b2', '250.00', '0.00', '1.0000', '0.00', NULL, NULL, '2026-02-12', '18:30:00', 'pending', '[GUEST: krishna | 7894561230]', '2026-02-11 19:11:44', '2026-02-11 19:11:44');


-- --------------------------------------------------------
-- Table structure for table `coin_transactions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `coin_transactions`;

CREATE TABLE `coin_transactions` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_type` enum('earned','spent','refunded','admin_adjustment') NOT NULL,
  `description` text DEFAULT NULL,
  `reference_id` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `coin_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `coin_transactions`
-- 25 rows

INSERT INTO `coin_transactions` (`id`, `user_id`, `amount`, `transaction_type`, `description`, `reference_id`, `created_at`) VALUES
('053a30a6-f5fc-4074-9a19-953a01f65969', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', '25.00', 'earned', 'Coins earned for booking: 421a2b6c-abfa-419e-b245-faf9911888c1', '421a2b6c-abfa-419e-b245-faf9911888c1', '2026-02-13 15:51:51'),
('0c62fc7b-5d2b-4069-9a73-1fb584f9d961', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', '25.00', 'earned', 'Coins earned for booking: 0512959c-eb55-410b-aedf-a4074930618d', '0512959c-eb55-410b-aedf-a4074930618d', '2026-02-13 14:29:11'),
('196a7a6f-526a-4be5-a6bc-01aaeda310e0', '500d3490-5844-4507-9104-d2fc3e873256', '20.00', 'earned', 'Coins earned for booking: 8ab40336-e612-4e5d-9d5d-d2747f3fe518', '8ab40336-e612-4e5d-9d5d-d2747f3fe518', '2026-02-12 02:08:14'),
('19ae610f-185a-4783-872a-8d7a6bd9692b', '2a25937e-1c25-46a6-bccb-ffaf217a7274', '2.00', 'earned', 'Reward for completing: Hair Care & Styling', 'f96e705a-42c7-48e3-8f4e-da5bb119ba00', '2026-02-03 02:37:03'),
('1ff8e5b0-ae0e-4a2b-93e5-828eed2cc5c1', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '-10.00', 'spent', 'Booking redemption for 1 services', NULL, '2026-02-03 02:29:27'),
('227cbb56-d457-4659-af1a-0a833bb81d54', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2.00', 'earned', 'Coins earned for booking: bbcb9ac4-5f71-43f7-babe-d853ae4c83d0', 'bbcb9ac4-5f71-43f7-babe-d853ae4c83d0', '2026-02-02 21:32:11'),
('248f3555-3df0-4efa-9e82-c252b327f9a9', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2.00', 'earned', 'Coins earned for booking: Hair Care & Styling', '588b3f8f-6845-4471-859c-12eb126d1316', '2026-02-03 01:50:43'),
('29ec8f1f-95e4-4937-9869-85329ecf85cb', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '10.00', 'refunded', 'Refund for cancelled booking: #104f61e0', '104f61e0-4a04-45b8-ab1b-7bead5dfbc29', '2026-02-03 02:37:48'),
('303626c6-4a46-4046-b6f4-c23201b97c33', '35128bea-2e85-48e6-91ff-ea99739a74a5', '25.00', 'earned', 'Coins earned for booking: 0ee2e96f-6a36-419e-86dc-f5f4be75c07e', '0ee2e96f-6a36-419e-86dc-f5f4be75c07e', '2026-02-12 21:54:03'),
('4c571f88-b914-41f5-8ba1-163d0eada717', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', '25.00', 'earned', 'Coins earned for booking: 14e49c3d-eb1c-41c8-9bb9-96ec80b63cd4', '14e49c3d-eb1c-41c8-9bb9-96ec80b63cd4', '2026-02-13 14:32:37'),
('77c318ec-fd7b-46ec-b7b6-b0865ad5a235', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '10.00', 'earned', 'Reward for completing: Hair Care & Styling', '1d04f305-12f3-43c5-8865-590ec819986c', '2026-02-03 02:13:25'),
('7f1bee83-5724-458f-9cc3-d3819f662d02', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2.00', 'earned', 'Coins earned for booking: 858ef03b-30ea-458a-a453-b2708b7c4a02', '858ef03b-30ea-458a-a453-b2708b7c4a02', '2026-02-02 21:49:05'),
('8356dcce-947e-4e6c-a2f5-b595b92184a7', '500d3490-5844-4507-9104-d2fc3e873256', '20.00', 'earned', 'Coins earned for booking: 03e2907a-83d7-499c-9d69-c6e7455b8c84', '03e2907a-83d7-499c-9d69-c6e7455b8c84', '2026-02-12 02:02:09'),
('ad4cb60e-1a2e-4a5f-936a-e5724b9385f0', '35128bea-2e85-48e6-91ff-ea99739a74a5', '25.00', 'earned', 'Coins earned for booking: 4f8b87b2-08ec-4881-ae83-5af82be20f40', '4f8b87b2-08ec-4881-ae83-5af82be20f40', '2026-02-12 21:54:14'),
('b2961ff9-7a7b-41d7-9b96-6a397086cd9c', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2.00', 'earned', 'Coins earned for booking: da71d2b6-a842-4779-8d42-df75ab0c613b', 'da71d2b6-a842-4779-8d42-df75ab0c613b', '2026-02-03 01:42:14'),
('bb09555a-c0d2-4264-a1ea-6786eaef3fe2', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '10.00', 'earned', 'Reward for completing: Hair Care & Styling', 'c0172b32-b2e7-4267-b4be-8871f8001a34', '2026-02-03 02:36:32'),
('bdd8bab2-4225-4c9e-b224-6a5d07666297', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', '25.00', 'earned', 'Coins earned for booking: 0c9bb788-1d4c-455b-8eca-dcfde1c594a4', '0c9bb788-1d4c-455b-8eca-dcfde1c594a4', '2026-02-12 20:55:11'),
('be1672db-2586-4a0b-ab25-128d89d5b709', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', '25.00', 'earned', 'Coins earned for booking: 14e49c3d-eb1c-41c8-9bb9-96ec80b63cd4', '14e49c3d-eb1c-41c8-9bb9-96ec80b63cd4', '2026-02-13 14:55:15'),
('c18c6e99-c8a8-4fbf-a4cd-43909b49c867', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', '25.00', 'earned', 'Coins earned for booking: cb27be0e-abe7-4dd9-9a7f-0c3b1445b6cc', 'cb27be0e-abe7-4dd9-9a7f-0c3b1445b6cc', '2026-02-13 14:25:34'),
('c1b78acf-bbb8-47cb-a252-b52b91b5aa7e', '35128bea-2e85-48e6-91ff-ea99739a74a5', '25.00', 'earned', 'Coins earned for booking: 4f8b87b2-08ec-4881-ae83-5af82be20f40', '4f8b87b2-08ec-4881-ae83-5af82be20f40', '2026-02-12 20:53:59'),
('c971645a-2b75-4878-a6c9-01b14fcf7395', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', '25.00', 'earned', 'Coins earned for booking: 0c9bb788-1d4c-455b-8eca-dcfde1c594a4', '0c9bb788-1d4c-455b-8eca-dcfde1c594a4', '2026-02-12 21:38:32'),
('d01001ac-96fd-4618-94ca-3760a11b5599', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2.00', 'earned', 'Coins earned for booking: a757c2b4-5c2e-4ed2-bcd1-c91a7db53fac', 'a757c2b4-5c2e-4ed2-bcd1-c91a7db53fac', '2026-02-02 21:13:42'),
('d83f429a-23df-4418-92a8-4fb78ea1d244', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', '5.00', 'earned', 'Coins earned for booking: 1a0ce12a-df55-4db9-8c10-a2161b837a96', '1a0ce12a-df55-4db9-8c10-a2161b837a96', '2026-02-01 20:09:36'),
('d937a7cc-1464-43e0-a6db-48732cedaeec', '500d3490-5844-4507-9104-d2fc3e873256', '20.00', 'earned', 'Coins earned for booking: 2111001d-82c4-4e52-919f-bb607689f022', '2111001d-82c4-4e52-919f-bb607689f022', '2026-02-12 01:07:03'),
('e9ceda35-bcc9-451a-8a1c-8124a33c2a78', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '-10.00', 'spent', 'Booking redemption for 1 services', NULL, '2026-02-03 02:37:29');


-- --------------------------------------------------------
-- Table structure for table `contact_enquiries`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `contact_enquiries`;

CREATE TABLE `contact_enquiries` (
  `id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','replied','closed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `contact_enquiries`
-- 4 rows

INSERT INTO `contact_enquiries` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
('2b232b80-38fe-4be0-9923-c0c613c80d67', 'aman', 'vfdgfd@gmail.com', '8956231478', 'for test subject', 'for test message', 'pending', '2026-01-28 03:15:18', '2026-01-28 03:15:18'),
('2d453d04-0cbb-428e-8221-0a27c897511b', 'aaaa', 'amanjeetthakur644@gmail.com', '7894561230', 'wwww', 'wwww', 'closed', '2026-01-28 03:10:02', '2026-01-30 04:25:15'),
('97ce3df6-23a5-40ff-86ab-9a6c1c558e67', 'cx', 'gfd@gmail.com', '8956231478', 'Enable Authorization Header for JWT REST API (NGINX fix)', 'njm', 'pending', '2026-01-29 16:31:02', '2026-01-29 16:31:02'),
('9a4f5db4-a3e4-4635-9438-5e3c2369ee5a', 'aman', 'vfdgfd@gmail.com', '8520147963', 'for test subject', 'for test message', 'pending', '2026-01-28 03:13:31', '2026-01-28 03:13:31');


-- --------------------------------------------------------
-- Table structure for table `customer_product_purchases`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `customer_product_purchases`;

CREATE TABLE `customer_product_purchases` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `salon_id` (`salon_id`),
  KEY `idx_user_salon` (`user_id`,`salon_id`),
  CONSTRAINT `customer_product_purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_product_purchases_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `customer_salon_profiles`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `customer_salon_profiles`;

CREATE TABLE `customer_salon_profiles` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `skin_type` varchar(100) DEFAULT NULL,
  `skin_issues` text DEFAULT NULL,
  `allergy_records` text DEFAULT NULL,
  `photo_url` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `medical_conditions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `concern_photo_url` varchar(255) DEFAULT NULL,
  `concern_photo_public_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_salon_profile` (`user_id`,`salon_id`),
  KEY `salon_id` (`salon_id`),
  CONSTRAINT `customer_salon_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_salon_profiles_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `customer_salon_profiles`
-- 3 rows

INSERT INTO `customer_salon_profiles` (`id`, `user_id`, `salon_id`, `date_of_birth`, `skin_type`, `skin_issues`, `allergy_records`, `photo_url`, `created_at`, `updated_at`, `medical_conditions`, `notes`, `concern_photo_url`, `concern_photo_public_id`) VALUES
('4ffe5270-41d8-491f-a659-07f1b51a2681', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '2026-02-11', 'oily', '', NULL, NULL, '2026-02-12 20:27:43', '2026-02-13 14:34:37', NULL, NULL, NULL, NULL),
('b3092336-215b-4b5f-886b-d254e93ed403', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', '1998-08-20', 'dry', 'no issue', 'Deep Cleansing Facial with gentle exfoliation, steam therapy, blackhead removal, calming massage, and hydration mask application. The procedure was customized according to the client’s skin type and sensitivity level.', NULL, '2026-02-02 21:22:10', '2026-02-02 21:22:10', NULL, NULL, NULL, NULL),
('d67b66d1-25e2-4807-9abc-722109707b8e', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', '71a9b518-f586-4906-b827-a0cc4619decc', '0000-00-00', '', '', '', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770270358/salon_uploads/cyrqsd4byd6ybopgdudy.jpg', '2026-02-04 08:52:22', '2026-02-05 11:16:03', NULL, NULL, NULL, NULL);


-- --------------------------------------------------------
-- Table structure for table `loyalty_programs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `loyalty_programs`;

CREATE TABLE `loyalty_programs` (
  `id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `program_name` varchar(255) DEFAULT 'Loyalty Program',
  `is_active` tinyint(1) DEFAULT 0,
  `points_per_currency_unit` decimal(10,2) DEFAULT 1.00,
  `min_points_redemption` int(11) DEFAULT 100,
  `signup_bonus_points` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_salon` (`salon_id`),
  CONSTRAINT `fk_loyalty_salon` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `loyalty_programs`
-- 4 rows

INSERT INTO `loyalty_programs` (`id`, `salon_id`, `program_name`, `is_active`, `points_per_currency_unit`, `min_points_redemption`, `signup_bonus_points`, `description`, `created_at`, `updated_at`) VALUES
('40a2911e-943d-49d1-b4c5-5bf78901d077', '987bb6be-1178-495e-9af7-3819a91a24b0', 'Loyalty Program', '0', '1.00', '100', '0', NULL, '2026-02-12 01:07:03', '2026-02-12 01:07:03'),
('4bc0aae3-305b-4617-b0ac-5a0b3a8c802d', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Loyalty Program', '0', '1.00', '100', '0', NULL, '2026-01-31 22:59:08', '2026-01-31 22:59:08'),
('98bd572d-0a40-4bd2-aeea-c282af55eb56', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'Loyalty Program', '0', '1.00', '100', '0', NULL, '2026-02-12 20:53:59', '2026-02-12 20:53:59'),
('e46866ea-2082-4054-b0bb-701bb83aff0e', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Loyalty Program', '0', '1.00', '100', '0', NULL, '2026-02-02 21:13:42', '2026-02-02 21:13:42');


-- --------------------------------------------------------
-- Table structure for table `loyalty_rewards`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `loyalty_rewards`;

CREATE TABLE `loyalty_rewards` (
  `id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `points_required` int(11) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_salon` (`salon_id`),
  CONSTRAINT `fk_loyalty_rewards_salon` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `loyalty_transactions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `loyalty_transactions`;

CREATE TABLE `loyalty_transactions` (
  `id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `points` int(11) NOT NULL,
  `transaction_type` enum('earned','redeemed','adjusted','bonus','refunded') NOT NULL,
  `reference_id` varchar(36) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_salon_user` (`salon_id`,`user_id`),
  KEY `fk_loyalty_trans_user` (`user_id`),
  CONSTRAINT `fk_loyalty_trans_salon` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loyalty_trans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `id` varchar(36) NOT NULL,
  `sender_id` varchar(36) NOT NULL,
  `receiver_id` varchar(36) DEFAULT NULL,
  `salon_id` varchar(36) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `recipient_type` enum('owner','super_admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `salon_id` (`salon_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `messages`
-- 3 rows

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `salon_id`, `subject`, `content`, `is_read`, `recipient_type`, `created_at`) VALUES
('3e7a0efa-1b4f-45e3-abc2-5bbf99bccde5', '2a25937e-1c25-46a6-bccb-ffaf217a7274', '89140b81-bf98-494e-bf6c-31472d2b11e2', '71a9b518-f586-4906-b827-a0cc4619decc', 'sdc', 'dcs', '1', 'owner', '2026-02-02 01:32:35'),
('8381afb3-dc32-4404-9750-4c015b550053', '2a25937e-1c25-46a6-bccb-ffaf217a7274', '89140b81-bf98-494e-bf6c-31472d2b11e2', '71a9b518-f586-4906-b827-a0cc4619decc', 'dd', 'dd', '1', 'owner', '2026-02-02 01:19:09'),
('d57cd4d3-5ba9-42fe-8139-f49be451ce0d', 'b5108e08-c320-4204-b712-b6df306c3ed4', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'test', 'use for test ', '0', 'owner', '2026-02-11 23:14:23');


-- --------------------------------------------------------
-- Table structure for table `newsletter_subscribers`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `newsletter_subscribers`;

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_first_visit_eligible` tinyint(1) DEFAULT 1,
  `discount_code` varchar(20) DEFAULT NULL,
  `discount_used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `newsletter_subscribers`
-- 3 rows

INSERT INTO `newsletter_subscribers` (`id`, `email`, `created_at`, `is_first_visit_eligible`, `discount_code`, `discount_used`) VALUES
('1', 'amanjeetthakur@gmail.com', '2026-01-29 20:54:28', '1', NULL, '0'),
('2', 'amanjeetthakur644@gmail.com', '2026-02-06 18:53:44', '1', NULL, '0'),
('3', 'aman@gmail.com', '2026-02-12 17:45:26', '1', NULL, '0');


-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'general',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `notifications`
-- 79 rows

INSERT INTO `notifications` (`id`, `user_id`, `salon_id`, `title`, `message`, `type`, `link`, `is_read`, `created_at`) VALUES
('060b1942-8aae-4f0f-8ab1-ffced638b333', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Appointment Cancelled', 'A booking has been cancelled by the customer.', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:07:05'),
('064bd669-7263-4ce2-b265-fc47ca453531', 'cf230af6-8c44-40a3-b0c8-9427cd09ef9e', NULL, 'Account Activated', 'Welcome to the elite grooming network, riya! Your account has been successfully initialized.', 'success', NULL, '0', '2026-02-11 23:25:35'),
('0949d1f2-ac12-4e79-96ef-fb75a1c623b7', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'Staff Assignment Required', 'A new booking for body massage needs a specialist assigned for Feb 12', 'booking', '/dashboard/appointments', '0', '2026-02-12 01:25:03'),
('0d09f7b5-2eba-464c-9a0b-fd9a72973095', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'Staff Assignment Required', 'A new booking for body massage needs a specialist assigned for Feb 12', 'booking', '/dashboard/appointments', '0', '2026-02-12 01:24:39'),
('12e04975-1eea-4769-9526-d0ba3a1d29a5', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'Staff Assignment Required', 'A new booking for spa needs a specialist assigned for Feb 12', 'booking', '/dashboard/appointments', '0', '2026-02-12 19:55:46'),
('1e827d7b-1a55-48e6-9e4d-0e7e8c9a66b3', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', NULL, 'Account Activated', 'Welcome to the elite grooming network, krishna! Your account has been successfully initialized.', 'success', NULL, '1', '2026-02-01 19:17:36'),
('279c7a7d-bf0a-4ee0-9ea2-701de184bc07', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', NULL, 'Account Activated', 'Welcome to the elite grooming network, aman12! Your account has been successfully initialized.', 'success', NULL, '0', '2026-02-03 02:12:46'),
('2b451677-d70d-4aa9-87c8-889419bce316', '55c5c311-71b7-409b-920e-f68eda0e103f', NULL, 'Access Granted', 'Your salon has been approved. You now have full access to the management console.', 'success', '/dashboard', '0', '2026-02-11 23:49:11'),
('2b56a07c-2e8b-4657-8cc0-465e8107e93e', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Appointment Cancelled', 'A booking has been cancelled by the customer.', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:07:05'),
('2be90b59-4047-4b75-a2a8-3cf8412b5dac', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', NULL, 'Access Granted', 'Your salon has been approved. You now have full access to the management console.', 'success', '/dashboard', '0', '2026-02-02 21:00:44'),
('2e5dfce7-be94-451b-aad6-1e2445c0688a', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Appointment Cancelled', 'A booking has been cancelled by the customer.', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:06:53'),
('3293717c-c593-40c5-9607-1dc977c3e89b', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', NULL, 'Order Confirmation', 'Your order #ff0a59cb-9fa9-4cc2-9488-6e5270ed1806 has been successfully placed. We are processing it now.', 'success', '/user/profile', '0', '2026-02-05 12:10:21'),
('35d092d2-784b-4d8f-9cc0-e1a4c9aba184', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'New Booking Batch', 'Multiple services booked for Feb 04', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:36:26'),
('38b9fe6a-4824-4c28-86aa-779417903c1c', '01cada4b-d998-4681-83a8-a5e31e8addac', NULL, 'New Product Order', 'New order #a84d0919-2e33-44ba-8fbe-4e85282cca7a placed by cxz cxz for RM 681.58', 'success', '/super-admin/orders', '0', '2026-02-05 12:04:42'),
('3aed13b3-fe5c-443e-9f9e-f6fd135e23b9', '55c5c311-71b7-409b-920e-f68eda0e103f', NULL, 'Account Activated', 'Your partner node \'test1@gmail.com\' has been initialized. Please wait for Super Admin approval to activate your dashboard.', 'success', NULL, '0', '2026-02-11 23:45:03'),
('3b9f569c-5203-4deb-9af5-bebfd89f985a', '50f093c6-9762-44ef-a7b5-10c3e455efed', NULL, 'Account Activated', 'Your partner node \'rahul\' has been initialized. Please wait for Super Admin approval to activate your dashboard.', 'success', NULL, '1', '2026-02-01 18:47:13'),
('4ab71905-ba4f-479c-bd93-c8a1e9e246c3', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', NULL, 'Plan Updated', 'Your salon plan has been updated to Professional by Admin.', 'info', '/dashboard/settings', '0', '2026-02-02 21:08:37'),
('4b5d9848-24a7-495c-bd31-ea92d8c80052', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Staff Assignment Required', 'A new booking for Glow Signature Keratin Hair Treatment needs a specialist assigned for Feb 04', 'booking', '/dashboard/appointments', '0', '2026-02-03 01:19:42'),
('4f609b21-b1d8-467c-93bf-f8926124df94', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', NULL, 'Plan Updated', 'Your salon plan has been updated to Professional by Admin.', 'info', '/dashboard/settings', '0', '2026-02-01 20:08:26'),
('598f088e-8a3a-413d-8a63-35141a395111', '01cada4b-d998-4681-83a8-a5e31e8addac', NULL, 'New Product Order', 'New order #c83b3822-6d35-4c39-b01f-06996cc2a55f placed by krrisha singh for RM 212', 'success', '/super-admin/orders', '0', '2026-02-02 21:42:35'),
('5ec5baeb-5650-4a74-b0c1-fdbceb9365ca', '50f093c6-9762-44ef-a7b5-10c3e455efed', NULL, 'Access Granted', 'Your salon has been approved. You now have full access to the management console.', 'success', '/dashboard', '1', '2026-02-01 18:47:47'),
('5fd394f9-8086-40bc-ba75-b5b53150ca5c', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', NULL, 'Access Granted', 'Your salon has been approved. You now have full access to the management console.', 'success', '/dashboard', '0', '2026-02-12 19:45:12'),
('64e2e3c3-51f9-43fe-bf1a-16dee65858f8', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'Staff Assignment Required', 'A new booking for body massage needs a specialist assigned for Feb 12', 'booking', '/dashboard/appointments', '0', '2026-02-12 00:40:24'),
('66313714-11af-4aa3-a1b8-4dbe9825c1e0', '500d3490-5844-4507-9104-d2fc3e873256', NULL, 'Account Activated', 'Welcome to the elite grooming network, rahulq1! Your account has been successfully initialized.', 'success', NULL, '0', '2026-02-12 00:57:10'),
('67a7dc77-d34a-4365-a6c5-2605594d5fbe', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', NULL, 'Access Granted', 'Your salon has been approved. You now have full access to the management console.', 'success', '/dashboard', '0', '2026-02-12 00:36:22'),
('78b59591-5e0a-4a93-98c2-27aba6fab839', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'New Appointment', 'New session booked for spa on Feb 13', 'booking', '/dashboard/appointments', '0', '2026-02-13 14:27:17'),
('790c1419-a8e6-44ef-9b72-38c42b912671', '3276e64751aa7d5dbfe4646c4c872b50', NULL, 'New Product Order', 'New order #36e01bf9-6234-4ac1-8de8-095d02effd75 placed by jatin KUMAR for RM 508.8', 'success', '/super-admin/orders', '0', '2026-02-13 14:19:39'),
('7a4a0c3b-c5a6-41d1-95b6-5d92fa977f77', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'New Booking Batch', 'Multiple services booked for Feb 04', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:01:47'),
('7c4b0330-3a33-4fe5-8807-896d7e49cfb2', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', NULL, 'Account Activated', 'Your partner node \'rahuljain@gmail.com\' has been initialized. Please wait for Super Admin approval to activate your dashboard.', 'success', NULL, '0', '2026-02-12 19:45:07'),
('7e32168e-7682-43be-9e29-25de0b827a8f', '038aaf6a-d8e1-40f7-989c-da6d9294bc72', NULL, 'Password Reset', 'A password reset email has been sent to amanjeetthakur644@gmail.com', 'info', NULL, '0', '2026-02-01 04:15:33'),
('8175c830-71a9-4fd5-b29b-294d704f0f07', '038aaf6a-d8e1-40f7-989c-da6d9294bc72', NULL, 'Password Reset', 'A password reset email has been sent to amanjeetthakur644@gmail.com', 'info', NULL, '0', '2026-02-01 18:32:32'),
('826ba7b4-3806-4ff0-8b03-f982654ee7e6', '01cada4b-d998-4681-83a8-a5e31e8addac', NULL, 'New Product Order', 'New order #ff0a59cb-9fa9-4cc2-9488-6e5270ed1806 placed by xs csxz for RM 151.74', 'success', '/super-admin/orders', '0', '2026-02-05 12:10:23'),
('82f4a293-6e1c-47b8-9181-f61674bb9626', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'Staff Assignment Required', 'A new booking for body massage needs a specialist assigned for Feb 13', 'booking', '/dashboard/appointments', '0', '2026-02-12 02:07:55'),
('82f6260d-ffb9-47b6-8ed7-f575e3ad0171', '521b14ee-9cdc-44f1-a464-f3efa99b5dc2', NULL, 'Account Activated', 'Welcome to the elite grooming network, ankit! Your account has been successfully initialized.', 'success', NULL, '0', '2026-02-11 23:19:08'),
('83c43c43-6a10-4df2-b287-9b358f169d91', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'New Booking Batch', 'Multiple services booked for Feb 04', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:13:05'),
('84189a69-0a9d-4ab3-8727-d581335224c7', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'Staff Assignment Required', 'A new booking for spa needs a specialist assigned for Feb 13', 'booking', '/dashboard/appointments', '0', '2026-02-13 14:54:27'),
('8537aee2-3399-4ccb-8c3d-f0ad21f3a793', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Staff Assignment Required', 'A new booking for Hair Care & Styling needs a specialist assigned for Feb 03', 'booking', '/dashboard/appointments', '0', '2026-02-02 21:48:47'),
('869db38c-c217-41eb-bd25-1068615bd041', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', NULL, 'Account Activated', 'Welcome to the elite grooming network, jatin! Your account has been successfully initialized.', 'success', NULL, '1', '2026-02-12 19:54:23'),
('86bb5956-8144-4442-a584-47560083ce21', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Appointment Cancelled', 'A booking has been cancelled by the customer.', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:37:48'),
('8b5337b8-8a58-4ed8-b2c0-b2cccfe637f8', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Appointment Cancelled', 'A booking has been cancelled by the customer.', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:06:55'),
('8ea25c57-9430-400c-8022-487e8b3b73bc', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'New Booking Batch', 'Multiple services booked for Feb 06', 'booking', '/dashboard/appointments', '0', '2026-02-03 01:50:25'),
('9039fda0-fad8-43b0-80b5-4706eeb3c7b2', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Staff Assignment Required', 'A new booking for Hair Care & Styling needs a specialist assigned for Feb 03', 'booking', '/dashboard/appointments', '0', '2026-02-02 21:31:42'),
('91af11a7-0248-4cd8-b860-2bcc97f78e2f', '50f093c6-9762-44ef-a7b5-10c3e455efed', NULL, 'Access Granted', 'Your salon has been approved. You now have full access to the management console.', 'success', '/dashboard', '1', '2026-02-01 18:47:49'),
('91e76a97-c336-41fa-a497-34e9c51c56a6', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'New Booking Batch', 'Multiple services booked for Feb 04', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:37:29'),
('93db14b9-28e7-4c21-8fa9-f671cc5f6bb7', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Staff Assignment Required', 'A new booking for Signature Body Massage needs a specialist assigned for Feb 13', 'booking', '/dashboard/appointments', '0', '2026-02-12 00:35:06'),
('9c2ff89b-a058-4e14-acde-2c40550bd4c2', '1450a01e-6e5e-4f3c-a953-22653cf13c19', NULL, 'Account Activated', 'Welcome to the elite grooming network, krrisha singh! Your account has been successfully initialized.', 'success', NULL, '0', '2026-02-02 21:11:26'),
('9f8ef891-67f2-4c8d-9276-8bcf60eac53e', '01cada4b-d998-4681-83a8-a5e31e8addac', NULL, 'New Product Order', 'New order #564ed2d9-a0a9-4db6-a823-a809991c588e placed by krishna Amanjeetsingh for RM 151.74', 'success', '/super-admin/orders', '0', '2026-02-01 20:11:38'),
('a5616757-9fc3-4eef-a365-277b0b5c364c', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'Staff Assignment Required', 'A new booking for body massage needs a specialist assigned for Feb 13', 'booking', '/dashboard/appointments', '0', '2026-02-12 01:33:26'),
('a68ea55b-b6f4-4ab0-8322-90af2779820e', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Staff Assignment Required', 'A new booking for Glow Signature Keratin Hair Treatment needs a specialist assigned for Feb 03', 'booking', '/dashboard/appointments', '0', '2026-02-02 21:47:47'),
('b457c036-568f-430a-b694-be84c6612cb6', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'Staff Assignment Required', 'A new booking for spa needs a specialist assigned for Feb 12', 'booking', '/dashboard/appointments', '0', '2026-02-12 21:30:23'),
('b997acc9-7de6-4bb6-8bab-d15865ffbb3b', '89140b81-bf98-494e-bf6c-31472d2b11e2', NULL, 'Plan Updated', 'Your salon plan has been updated to Professional by Admin.', 'info', '/dashboard/settings', '0', '2026-02-02 00:43:57'),
('be32dac4-c003-4dab-9c17-ebc793930e40', '038aaf6a-d8e1-40f7-989c-da6d9294bc72', NULL, 'Account Activated', 'Welcome to the elite grooming network, aman jeet singh! Your account has been successfully initialized.', 'success', NULL, '0', '2026-02-01 04:15:12'),
('be5a1ed0-7632-47e9-8243-e2468acf2586', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Staff Assignment Required', 'A new booking for Glow Signature Keratin Hair Treatment needs a specialist assigned for Feb 03', 'booking', '/dashboard/appointments', '0', '2026-02-02 21:53:47'),
('bf535f9a-d4e0-4e3a-a620-082a1718e029', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', NULL, 'Account Activated', 'Your partner node \'ankit1 salon\' has been initialized. Please wait for Super Admin approval to activate your dashboard.', 'success', NULL, '0', '2026-02-12 00:31:42'),
('c113620d-4950-40e3-8885-6d75529fce05', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Staff Assignment Required', 'A new booking for Amber Cellular Facial needs a specialist assigned for Feb 12', 'booking', '/dashboard/appointments', '0', '2026-02-11 19:11:44'),
('c66c483f-d3db-401b-82ce-dfc7724f90d6', '89140b81-bf98-494e-bf6c-31472d2b11e2', '71a9b518-f586-4906-b827-a0cc4619decc', 'Staff Assignment Required', 'A new booking for haircut test needs a specialist assigned for Feb 03', 'booking', '/dashboard/appointments', '1', '2026-02-02 00:46:46'),
('c74fe721-a74f-45af-8c93-91d329180086', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', NULL, 'Account Activated', 'Your partner node \'LuxeGlow Salon & Studio\' has been initialized. Please wait for Super Admin approval to activate your dashboard.', 'success', NULL, '0', '2026-02-02 21:00:12'),
('c7f79c95-1afe-4093-96a6-7f9853f22282', '89140b81-bf98-494e-bf6c-31472d2b11e2', NULL, 'Account Activated', 'Your partner node \'ravi salon\' has been initialized. Please wait for Super Admin approval to activate your dashboard.', 'success', NULL, '0', '2026-02-02 00:38:25'),
('c9ed5c7c-328f-4f09-8abd-d0c0f4c3a610', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Appointment Cancelled', 'A booking has been cancelled by the customer.', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:06:50'),
('ccc13613-2bf9-44ac-bc3f-7c7b3d018c5c', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'New Booking Batch', 'Multiple services booked for Feb 04', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:29:27'),
('d4c4eac1-59be-4b93-bbb7-2e8a12a467ae', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', NULL, 'Plan Updated', 'Your salon plan has been updated to Enterprise by Admin.', 'info', '/dashboard/settings', '0', '2026-02-02 21:08:59'),
('d541c10b-6478-4862-aa2c-7e403b8b1676', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'Appointment Cancelled', 'A booking has been cancelled by the customer.', 'booking', '/dashboard/appointments', '0', '2026-02-12 00:53:30'),
('d80411b6-878a-4469-a09e-ec39a3211944', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'New Booking Batch', 'Multiple services booked for Feb 04', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:06:37'),
('d8820079-139c-4a95-be5c-a62979e4991b', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'New Booking Batch', 'Multiple services booked for Feb 04', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:02:19'),
('dbd34a40-3b5f-40df-b168-633d813b9982', '89140b81-bf98-494e-bf6c-31472d2b11e2', NULL, 'Access Granted', 'Your salon has been approved. You now have full access to the management console.', 'success', '/dashboard', '0', '2026-02-02 00:39:37'),
('dbedfe0d-3596-4618-99a6-d182a69b5946', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'Staff Assignment Required', 'A new booking for body massage needs a specialist assigned for Feb 13', 'booking', '/dashboard/appointments', '1', '2026-02-12 00:59:37'),
('e0445eac-efee-43d8-9b7c-8d3808e5f751', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Appointment Cancelled', 'A booking has been cancelled by the customer.', 'booking', '/dashboard/appointments', '0', '2026-02-03 02:30:06'),
('e27ecdf0-8797-4da7-a217-aec1bec27592', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Staff Assignment Required', 'A new booking for Hair Care & Styling needs a specialist assigned for Feb 03', 'booking', '/dashboard/appointments', '0', '2026-02-02 21:10:39'),
('e6ee4a40-6591-40fd-9700-7fdfcb552aa8', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Staff Assignment Required', 'A new booking for Hair Care & Styling needs a specialist assigned for Feb 03', 'booking', '/dashboard/appointments', '0', '2026-02-02 21:11:40'),
('e6f2926c-da2a-4f22-ac9b-d0c879898d71', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'New Appointment', 'New session booked for spa on Feb 13', 'booking', '/dashboard/appointments', '0', '2026-02-13 14:25:09'),
('e7f8ac29-9c8e-4c30-ab4a-a6236e4cba5b', '89140b81-bf98-494e-bf6c-31472d2b11e2', NULL, 'Plan Updated', 'Your salon plan has been updated to Enterprise by Admin.', 'info', '/dashboard/settings', '0', '2026-02-02 01:58:45'),
('e99d6500-317b-4342-a17a-ee9d595d8509', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Staff Assignment Required', 'A new booking for Glow Signature Keratin Hair Treatment needs a specialist assigned for Feb 12', 'booking', '/dashboard/appointments', '0', '2026-02-12 16:25:21'),
('ee7ef96e-d10f-4fa5-bc11-a3c74ed4fac8', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'New Appointment', 'New session booked for spa on Feb 13', 'booking', '/dashboard/appointments', '0', '2026-02-13 15:37:58'),
('eec396dc-1577-473b-b635-dc49a0c61019', '35128bea-2e85-48e6-91ff-ea99739a74a5', NULL, 'Account Activated', 'Welcome to the elite grooming network, deepak! Your account has been successfully initialized.', 'success', NULL, '0', '2026-02-12 20:04:57'),
('f00f635b-9753-4df4-af64-c91ea34eb620', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'Staff Assignment Required', 'A new booking for body massage needs a specialist assigned for Feb 13', 'booking', '/dashboard/appointments', '0', '2026-02-12 02:42:54'),
('f1bb0045-796c-4ffd-a727-b9757c4450ea', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'Staff Assignment Required', 'A new booking for spa needs a specialist assigned for Feb 13', 'booking', '/dashboard/appointments', '0', '2026-02-13 14:32:18'),
('f7e5f211-a98d-4968-8332-563618e04f52', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'Staff Assignment Required', 'A new booking for spa needs a specialist assigned for Feb 12', 'booking', '/dashboard/appointments', '0', '2026-02-12 20:05:29'),
('fd52bd01-8cc6-4cd3-ba7b-15d7f51ebb4e', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Staff Assignment Required', 'A new booking for Hair Care & Styling needs a specialist assigned for Feb 04', 'booking', '/dashboard/appointments', '0', '2026-02-03 01:41:15'),
('ff313aa2-0b42-4525-bbd5-cb2d478123d1', '01cada4b-d998-4681-83a8-a5e31e8addac', NULL, 'New Product Order', 'New order #36e01bf9-6234-4ac1-8de8-095d02effd75 placed by jatin KUMAR for RM 508.8', 'success', '/super-admin/orders', '0', '2026-02-13 14:19:39');


-- --------------------------------------------------------
-- Table structure for table `password_resets`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `password_resets`;

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `password_resets`
-- 9 rows

INSERT INTO `password_resets` (`id`, `email`, `token`, `created_at`, `expires_at`) VALUES
('1', 'amanjeetthakur644@gmail.com', '55a9f0b30c907b83400897972ef7caf6', '2026-02-01 03:56:26', '2026-02-01 04:56:26'),
('2', 'amanjeetthakur644@gmail.com', 'c8b9d54c9438603c737e05fe4f96c155', '2026-02-01 04:00:54', '2026-02-01 05:00:54'),
('3', 'amanjeetthakur644@gmail.com', '48be48ce925d9752324a035fc56e1013', '2026-02-01 04:00:55', '2026-02-01 05:00:55'),
('4', 'amanjeetthakur644@gmail.com', '86a49fe3727a439f3083e3d476d66544', '2026-02-01 04:00:56', '2026-02-01 05:00:56'),
('5', 'amanjeetthakur644@gmail.com', 'b4a091e4da858e9c63b789713757ffda', '2026-02-01 04:02:46', '2026-02-01 05:02:46'),
('6', 'amanjeetthakur644@gmail.com', '33be4c8226686ed79f5cff767f149f42', '2026-02-01 04:03:53', '2026-02-01 05:03:53'),
('7', 'amanjeetthakur644@gmail.com', '4108f263110a1a9916a0432fb3f1feca', '2026-02-01 04:11:07', '2026-02-01 05:11:07'),
('8', 'amanjeetthakur644@gmail.com', 'aaadb3de89085812e3b4fb9c0f71614e', '2026-02-01 04:15:31', '2026-02-01 05:15:31'),
('9', 'amanjeetthakur644@gmail.com', '52361ae51d509ac421a6d24c69e22fcb', '2026-02-01 18:32:28', '2026-02-01 19:32:28');


-- --------------------------------------------------------
-- Table structure for table `permissions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `permissions`
-- 8 rows

INSERT INTO `permissions` (`id`, `name`, `description`, `module`, `created_at`) VALUES
('248432dc-fae9-11f0-b172-a4f9334d99db', 'view_bookings', 'Can view salon bookings', 'bookings', '2026-01-27 00:29:27'),
('24843a63-fae9-11f0-b172-a4f9334d99db', 'manage_bookings', 'Can create, update, and cancel bookings', 'bookings', '2026-01-27 00:29:27'),
('24843b09-fae9-11f0-b172-a4f9334d99db', 'view_staff', 'Can view salon staff roster', 'staff', '2026-01-27 00:29:27'),
('24843b43-fae9-11f0-b172-a4f9334d99db', 'manage_staff', 'Can add, remove, or edit staff details', 'staff', '2026-01-27 00:29:27'),
('24843b7b-fae9-11f0-b172-a4f9334d99db', 'view_reports', 'Can view business revenue and analytics', 'reports', '2026-01-27 00:29:27'),
('24843bb6-fae9-11f0-b172-a4f9334d99db', 'manage_services', 'Can manage salon services and pricing', 'services', '2026-01-27 00:29:27'),
('24843bf2-fae9-11f0-b172-a4f9334d99db', 'track_attendance', 'Can check-in/out and view own attendance', 'attendance', '2026-01-27 00:29:27'),
('24843c3d-fae9-11f0-b172-a4f9334d99db', 'manage_attendance', 'Can view and edit everyone\'s attendance', 'attendance', '2026-01-27 00:29:27');


-- --------------------------------------------------------
-- Table structure for table `platform_admins`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `platform_admins`;

CREATE TABLE `platform_admins` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `platform_admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `platform_admins`
-- 2 rows

INSERT INTO `platform_admins` (`id`, `user_id`, `is_active`, `created_at`, `updated_at`) VALUES
('62b7d72e-044d-4262-bce0-4753fd5e1589', '01cada4b-d998-4681-83a8-a5e31e8addac', '1', '2026-02-01 17:00:50', '2026-02-01 17:00:50'),
('931dcc0581736bc41ceb57e27481eade', '3276e64751aa7d5dbfe4646c4c872b50', '1', '2026-02-11 21:22:54', '2026-02-11 21:22:54');


-- --------------------------------------------------------
-- Table structure for table `platform_banners`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `platform_banners`;

CREATE TABLE `platform_banners` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `link_url` text DEFAULT NULL,
  `link_text` varchar(255) DEFAULT NULL,
  `position` enum('home_hero','home_secondary','sidebar','popup') DEFAULT 'home_hero',
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_by` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `platform_offers`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `platform_offers`;

CREATE TABLE `platform_offers` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `discount_type` enum('percentage','fixed','free_trial_days') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `applicable_to` enum('all','new_salons','existing_salons','specific_plans') DEFAULT 'all',
  `applicable_plan_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_plan_ids`)),
  `max_uses` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `platform_orders`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `platform_orders`;

CREATE TABLE `platform_orders` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('placed','dispatched','delivered','cancelled') DEFAULT 'placed',
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `shipping_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shipping_address`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `coins_used` decimal(15,2) DEFAULT 0.00,
  `coin_currency_value` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `platform_orders`
-- 7 rows

INSERT INTO `platform_orders` (`id`, `user_id`, `guest_email`, `guest_name`, `total_amount`, `status`, `items`, `shipping_address`, `created_at`, `updated_at`, `coins_used`, `coin_currency_value`) VALUES
('36e01bf9-6234-4ac1-8de8-095d02effd75', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'jatin@gmail.com', 'jatin KUMAR', '508.80', 'delivered', '[{\"id\":\"34290db5-54f4-4cc7-96bb-6732636213c5\",\"name\":\"testproduct\",\"price\":120,\"image_url\":\"https:\\/\\/res.cloudinary.com\\/de28lezdr\\/image\\/upload\\/v1770972095\\/salon_uploads\\/fn1gz5mizzbgusvxqmh6.png\",\"type\":\"product\",\"quantity\":4}]', '{\"address\":\"test\",\"apartment\":\"test\",\"city\":\"test\",\"state\":\"test\",\"postalCode\":\"177101\",\"country\":\"Malaysia\"}', '2026-02-13 14:19:39', '2026-02-13 14:22:46', '0.00', '0.00'),
('564ed2d9-a0a9-4db6-a823-a809991c588e', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', 'krishna@gmail.com', 'krishna Amanjeetsingh', '151.74', 'delivered', '[{\"id\":\"4fbb03b8-3bc4-480b-9c90-fee068d0f231\",\"name\":\"Luxe Glow Keratin Repair Hair Serum\",\"price\":129,\"image_url\":\"https:\\/\\/res.cloudinary.com\\/de28lezdr\\/image\\/upload\\/v1769879527\\/salon_uploads\\/e5zefznbjr1yu4q36a5w.webp\",\"type\":\"product\",\"quantity\":1}]', '{\"address\":\"village dhawala teh. dehra dist. kangra himachal pradesh\",\"apartment\":\"\",\"city\":\"kangra\",\"state\":\"HIMACHAL PRADES\",\"postalCode\":\"177101\",\"country\":\"Malaysia\"}', '2026-02-01 20:11:38', '2026-02-01 20:12:18', '0.00', '0.00'),
('6f7eb72a-0d78-4a79-a6d9-27931483108e', '', 'john@example.com', 'John Doe', '70.00', 'dispatched', '[{\"name\":\"Premium Shampoo\",\"quantity\":2,\"price\":25},{\"name\":\"Conditioner\",\"quantity\":1,\"price\":20}]', '{\"street\":\"123 Salon St\",\"city\":\"Beauty City\",\"state\":\"NY\",\"zip\":\"10001\"}', '2026-02-01 02:30:07', '2026-02-01 02:49:03', '0.00', '0.00'),
('a84d0919-2e33-44ba-8fbe-4e85282cca7a', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', 'krishna@gmail.com', 'cxz cxz', '681.58', 'dispatched', '[{\"id\":\"11111111-1111-1111-1111-111111111111\",\"name\":\"Premium Lavender Essential Oil\",\"price\":25,\"image_url\":null,\"type\":\"product\",\"quantity\":1},{\"id\":\"22222222-2222-2222-2222-222222222222\",\"name\":\"Professional Hair Dryer Pro X\",\"price\":120,\"image_url\":null,\"type\":\"product\",\"quantity\":3},{\"id\":\"4fbb03b8-3bc4-480b-9c90-fee068d0f231\",\"name\":\"Luxe Glow Keratin Repair Hair Serum\",\"price\":129,\"image_url\":\"https:\\/\\/res.cloudinary.com\\/de28lezdr\\/image\\/upload\\/v1769879527\\/salon_uploads\\/e5zefznbjr1yu4q36a5w.webp\",\"type\":\"product\",\"quantity\":2}]', '{\"address\":\"ksudh\",\"apartment\":\"\",\"city\":\"kangra\",\"state\":\"karachi\",\"postalCode\":\"1212\",\"country\":\"Malaysia\"}', '2026-02-05 12:04:42', '2026-02-11 22:38:24', '0.00', '1.00'),
('c83b3822-6d35-4c39-b01f-06996cc2a55f', '1450a01e-6e5e-4f3c-a953-22653cf13c19', 'krishnasingh12@gmail.com', 'krrisha singh', '212.00', 'delivered', '[{\"id\":\"7bd00a36-a82c-4373-ae55-fe26aef67d4e\",\"name\":\"face cream\",\"price\":200,\"image_url\":\"https:\\/\\/res.cloudinary.com\\/de28lezdr\\/image\\/upload\\/v1770048661\\/salon_uploads\\/geiovyp5hzpslsmoqqlq.webp\",\"type\":\"product\",\"quantity\":1}]', '{\"address\":\"Malaysia\",\"apartment\":\"appartment\",\"city\":\"\",\"state\":\"\",\"postalCode\":\"\",\"country\":\"Malaysia\"}', '2026-02-02 21:42:35', '2026-02-02 21:43:49', '0.00', '0.00'),
('d9cdbd8e-77fd-44fb-a729-7361414ae993', '7060d5bd-2d8d-45e2-8cba-8647a0fc3106', 'krishna12@gmail.com', 'krishna Singh', '151.74', 'dispatched', '[{\"id\":\"4fbb03b8-3bc4-480b-9c90-fee068d0f231\",\"name\":\"Luxe Glow Keratin Repair Hair Serum\",\"price\":129,\"image_url\":\"https:\\/\\/res.cloudinary.com\\/de28lezdr\\/image\\/upload\\/v1769879527\\/salon_uploads\\/e5zefznbjr1yu4q36a5w.webp\",\"type\":\"product\",\"quantity\":1}]', '{\"address\":\"village dhawala teh. dehra dist. kangra himachal pradesh\",\"apartment\":\"\",\"city\":\"kangra\",\"state\":\"Himachal Pradesh\",\"postalCode\":\"177101\",\"country\":\"Malaysia\"}', '2026-02-01 02:47:28', '2026-02-01 02:49:01', '0.00', '0.00'),
('ff0a59cb-9fa9-4cc2-9488-6e5270ed1806', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', 'krishna@gmail.com', 'xs csxz', '151.74', 'dispatched', '[{\"id\":\"4fbb03b8-3bc4-480b-9c90-fee068d0f231\",\"name\":\"Luxe Glow Keratin Repair Hair Serum\",\"price\":129,\"image_url\":\"https:\\/\\/res.cloudinary.com\\/de28lezdr\\/image\\/upload\\/v1769879527\\/salon_uploads\\/e5zefznbjr1yu4q36a5w.webp\",\"type\":\"product\",\"quantity\":1}]', '{\"address\":\"sdcx\",\"apartment\":\"dcsx\",\"city\":\"sx\",\"state\":\"dsx\",\"postalCode\":\"sxz\",\"country\":\"Malaysia\"}', '2026-02-05 12:10:21', '2026-02-11 22:38:28', '0.00', '1.00');


-- --------------------------------------------------------
-- Table structure for table `platform_payments`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `platform_payments`;

CREATE TABLE `platform_payments` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `salon_id` varchar(36) NOT NULL,
  `subscription_id` varchar(36) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'MYR',
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(100) DEFAULT NULL,
  `payment_gateway` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `invoice_url` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `idx_salon_id` (`salon_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `platform_payments_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `platform_payments_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `salon_subscriptions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `platform_payments`
-- 6 rows

INSERT INTO `platform_payments` (`id`, `salon_id`, `subscription_id`, `amount`, `currency`, `status`, `payment_method`, `payment_gateway`, `transaction_id`, `invoice_number`, `invoice_url`, `notes`, `paid_at`, `created_at`) VALUES
('0c2020b8-8926-4bec-abdc-daa29a39b70d', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', '0ea6737f-621a-4d17-af81-dbf498b342eb', '2499.00', 'MYR', 'completed', 'admin_assignment', NULL, NULL, NULL, NULL, NULL, '2026-02-02 21:08:37', '2026-02-02 21:08:37'),
('17ab02f4-f0f7-405b-a58f-9fa19f1140e2', '71a9b518-f586-4906-b827-a0cc4619decc', '40de210b-451e-4061-bfe5-d175b7bd9a5e', '4999.00', 'MYR', 'completed', 'admin_assignment', NULL, NULL, NULL, NULL, NULL, '2026-02-02 01:58:45', '2026-02-02 01:58:45'),
('4334a4d9-3cf2-4222-a43b-3972a0159c63', '71a9b518-f586-4906-b827-a0cc4619decc', '40de210b-451e-4061-bfe5-d175b7bd9a5e', '2499.00', 'MYR', 'completed', 'admin_assignment', NULL, NULL, NULL, NULL, NULL, '2026-02-02 00:43:57', '2026-02-02 00:43:57'),
('70c65e60-f09d-4c9b-8792-d325dde5e94d', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', '0ea6737f-621a-4d17-af81-dbf498b342eb', '4999.00', 'MYR', 'completed', 'admin_assignment', NULL, NULL, NULL, NULL, NULL, '2026-02-02 21:08:59', '2026-02-02 21:08:59'),
('d06a973f-75e0-438d-a424-d32cfa7d008e', '3cb7202f-f091-41f5-92de-8a4f35990713', '6041c5f3-35ae-4451-a77f-cd2492ce805b', '2499.00', 'MYR', 'completed', 'admin_assignment', NULL, NULL, NULL, NULL, NULL, '2026-02-01 20:08:26', '2026-02-01 20:08:26'),
('d75e8a61-ae29-4809-b77c-4b8513e17999', '3cb7202f-f091-41f5-92de-8a4f35990713', '6041c5f3-35ae-4451-a77f-cd2492ce805b', '2499.00', 'MYR', 'completed', 'admin_assignment', NULL, NULL, NULL, NULL, NULL, '2026-01-31 22:45:33', '2026-01-31 22:45:33');


-- --------------------------------------------------------
-- Table structure for table `platform_products`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `platform_products`;

CREATE TABLE `platform_products` (
  `id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `stock_quantity` int(11) DEFAULT 0,
  `image_url` text DEFAULT NULL,
  `image_url_2` text DEFAULT NULL,
  `image_url_3` text DEFAULT NULL,
  `image_url_4` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `target_audience` enum('salon','customer','both') DEFAULT 'both',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_public_id` varchar(255) DEFAULT NULL,
  `image_2_public_id` varchar(255) DEFAULT NULL,
  `image_3_public_id` varchar(255) DEFAULT NULL,
  `image_4_public_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `platform_products`
-- 3 rows

INSERT INTO `platform_products` (`id`, `name`, `description`, `features`, `price`, `discount`, `stock_quantity`, `image_url`, `image_url_2`, `image_url_3`, `image_url_4`, `category`, `brand`, `target_audience`, `is_active`, `created_at`, `updated_at`, `image_public_id`, `image_2_public_id`, `image_3_public_id`, `image_4_public_id`) VALUES
('34290db5-54f4-4cc7-96bb-6732636213c5', 'testproduct', 'testproduct', '', '120.00', '20.00', '20', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770972095/salon_uploads/fn1gz5mizzbgusvxqmh6.png', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770972382/salon_uploads/coub3vuypjtqlwdy6kjh.png', '', '', 'Nails', 'Olaplex', 'both', '1', '2026-02-13 14:11:49', '2026-02-13 14:16:26', NULL, NULL, NULL, NULL),
('4fbb03b8-3bc4-480b-9c90-fee068d0f231', 'Luxe Glow Keratin Repair Hair Serum', 'Luxe Glow Keratin Repair Hair Serum ek professional salon-grade treatment product hai jo damaged, dry aur chemically treated hair ko deeply nourish karta hai. Ismein infused Keratin Protein, Argan Oil aur Vitamin E baalon ko smooth, shiny aur frizz-free banate hain.\n\nYeh serum baalon ki breakage kam karta hai, split ends repair karta hai aur long-lasting softness provide karta hai. Daily use aur professional salon treatments dono ke liye suitable hai.\n\nBest for:\n\nDry & damaged hair\n\nColored / chemically treated hair\n\nFrizzy & dull hair', '', '129.00', '10.00', '50', 'https://res.cloudinary.com/de28lezdr/image/upload/v1769879527/salon_uploads/e5zefznbjr1yu4q36a5w.webp', 'https://res.cloudinary.com/de28lezdr/image/upload/v1769879537/salon_uploads/fuabqkgvvzhcv3wxzdya.webp', '', '', 'Hair Care / Salon Professional', 'Luxe Glow Professional', 'both', '1', '2026-01-31 22:42:22', '2026-01-31 22:42:22', NULL, NULL, NULL, NULL),
('7bd00a36-a82c-4373-ae55-fe26aef67d4e', 'face cream', 'for face', '', '200.00', '30.00', '45', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770048661/salon_uploads/geiovyp5hzpslsmoqqlq.webp', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770048676/salon_uploads/mptnuup5dzoutbxixbte.webp', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770048689/salon_uploads/czigohmc7ddk15sbs4ly.webp', '', 'Skin Care', 'test brand ', 'both', '1', '2026-02-02 21:41:37', '2026-02-02 21:41:37', NULL, NULL, NULL, NULL);


-- --------------------------------------------------------
-- Table structure for table `platform_settings`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `platform_settings`;

CREATE TABLE `platform_settings` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `setting_key` varchar(255) NOT NULL,
  `setting_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`setting_value`)),
  `description` text DEFAULT NULL,
  `updated_by` varchar(36) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `platform_settings`
-- 12 rows

INSERT INTO `platform_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_by`, `updated_at`) VALUES
('0cfee9aaf03b30c8c6d247b6aa117424', 'coin_max_discount_percent', '50', 'Maximum percentage of service price that can be paid with coins', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('1fdf8ed6dd40615d2cb15d09156c48db', 'coin_earning_rate', '10', 'Currency units spent to earn 1 coin', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('3318f3b22b50ebb36c195e53aa52e692', 'coin_signup_bonus', '0', 'Coins awarded to new clinical accounts upon registration', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('450d97a1-f969-11f0-9909-a4f9334d99db', 'platform_name', '\"NoamSkin\"', 'Platform brand name', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('450d9963-f969-11f0-9909-a4f9334d99db', 'platform_commission', '10', 'Platform commission percentage', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('450d99d8-f969-11f0-9909-a4f9334d99db', 'trial_days', '14', 'Default trial period in days', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('450d9a07-f969-11f0-9909-a4f9334d99db', 'support_email', '\"support@noamskin.com\"', 'Support email address', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('450d9a33-f969-11f0-9909-a4f9334d99db', 'currency', '\"MYR\"', 'Default currency', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('450d9a5d-f969-11f0-9909-a4f9334d99db', 'auto_approve_salons', 'true', 'Automatically approve new salon registrations', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('5580ad9c597391d7ec7fe2834831a740', 'coin_price', '1', 'Value of 1 coin in platform currency', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-03 01:37:10'),
('6bb17707a3009fe333a106b2c4bdc0b6', 'coin_min_redemption', '1', 'Minimum coins required for a single redemption', '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-02 21:47:01'),
('a52c3e1e-ea80-42db-b364-942a9b9edf91', 'coin_max_limit', '10000', NULL, '01cada4b-d998-4681-83a8-a5e31e8addac', '2026-02-03 01:37:10');


-- --------------------------------------------------------
-- Table structure for table `profiles`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `profiles`;

CREATE TABLE `profiles` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar_url` text DEFAULT NULL,
  `user_type` enum('customer','salon_owner','admin','staff') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar_public_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `profiles`
-- 24 rows

INSERT INTO `profiles` (`id`, `user_id`, `full_name`, `phone`, `avatar_url`, `user_type`, `created_at`, `updated_at`, `avatar_public_id`) VALUES
('01f2a43f-dbcc-4a76-ae5d-3d476787c12c', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'rahuljain', '+607894561230', NULL, 'salon_owner', '2026-02-12 19:45:07', '2026-02-12 19:45:07', NULL),
('256d476f-4089-457f-86d4-161990bcde74', '2772c605-856c-436a-9de6-6c83cfea6ac1', 'sanjna', NULL, NULL, 'customer', '2026-02-12 00:43:57', '2026-02-12 00:43:57', NULL),
('2f44a696-67ec-4c2e-93dc-dc1147963ebb', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', 'aman12', '+607894561230', NULL, 'customer', '2026-02-03 02:12:46', '2026-02-03 02:12:46', NULL),
('330440e2-7e6d-4c28-80ed-5d974204da8c', '50f093c6-9762-44ef-a7b5-10c3e455efed', 'rahul', '+60789456123', NULL, 'salon_owner', '2026-02-01 18:47:13', '2026-02-01 18:47:13', NULL),
('38e2f400-5c68-4444-9742-10465bb40afa', '01cada4b-d998-4681-83a8-a5e31e8addac', 'Super Administrator', NULL, NULL, 'admin', '2026-02-01 17:00:50', '2026-02-01 17:00:50', NULL),
('476e9b04-ff7b-11f0-b059-088fc3ed01dd', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', 'Luxe Glow Owner', NULL, NULL, 'salon_owner', '2026-02-01 20:05:39', '2026-02-01 20:05:39', NULL),
('50acfbd9-cc60-413a-98d0-a73559b0cade', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', 'Aisha Rahman', '+607894561230', NULL, 'salon_owner', '2026-02-02 21:00:12', '2026-02-02 21:00:12', NULL),
('6221990d-18b2-4cb7-82f1-1d0503489d04', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', NULL, NULL, 'https://res.cloudinary.com/de28lezdr/image/upload/v1770270358/salon_uploads/cyrqsd4byd6ybopgdudy.jpg', 'customer', '2026-02-01 19:17:36', '2026-02-05 11:15:57', 'salon_uploads/cyrqsd4byd6ybopgdudy'),
('642b2fe3-c78c-4677-a2d8-9d90434dda8b', '521b14ee-9cdc-44f1-a464-f3efa99b5dc2', 'ankit', '+607889456123', NULL, 'salon_owner', '2026-02-11 23:19:08', '2026-02-11 23:20:32', NULL),
('6b92c419-55fc-4d9e-b935-64b05ea90fc7', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', 'ankit1', '+6078945612302', NULL, 'salon_owner', '2026-02-12 00:31:42', '2026-02-12 00:31:42', NULL),
('78756a6d-fa5d-48ae-87bb-b35c267121ba', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'jatin', '+607894561230', NULL, 'customer', '2026-02-12 19:54:23', '2026-02-12 19:54:23', NULL),
('7ad17ab9-9d90-499d-bc55-e6c5c92adb8a', 'b5108e08-c320-4204-b712-b6df306c3ed4', 'aman', NULL, NULL, 'customer', '2026-02-01 20:09:03', '2026-02-01 20:09:03', NULL),
('7ca51807-006d-480a-958f-c911dbd2a194', 'cf230af6-8c44-40a3-b0c8-9427cd09ef9e', 'riya', '+607412589630', NULL, 'customer', '2026-02-11 23:25:35', '2026-02-11 23:25:35', NULL),
('80c01689-1078-431f-b6a9-25234e1ca22f', '76fa1757-d292-4031-b136-67b1ce0d05bb', 'amrita', NULL, NULL, 'customer', '2026-02-12 19:45:56', '2026-02-12 19:45:56', NULL),
('a6db2575-4655-457b-ac9e-55071cf24df4', '500d3490-5844-4507-9104-d2fc3e873256', 'rahulq1', '+607410258963', NULL, 'customer', '2026-02-12 00:57:10', '2026-02-12 00:57:10', NULL),
('a9ba92b8-e339-45b3-b2ec-79d7ecd88461', '21443efe-57bb-47bc-8048-1c5c478b824f', 'john doe', NULL, NULL, 'customer', '2026-02-02 21:13:11', '2026-02-02 21:13:11', NULL),
('aa41bb35-7217-4d4a-87f4-a1ece03a36ed', '35128bea-2e85-48e6-91ff-ea99739a74a5', 'deepak', '+607894561230', NULL, 'customer', '2026-02-12 20:04:56', '2026-02-12 20:04:56', NULL),
('b5f29378-a8cb-48b6-956a-dae538482fe3', '55c5c311-71b7-409b-920e-f68eda0e103f', 'test', '+6085207410', NULL, 'salon_owner', '2026-02-11 23:45:03', '2026-02-11 23:45:03', NULL),
('ba4ec2b0-0761-11f1-a0e4-088fc3ed01dd', '3276e64751aa7d5dbfe4646c4c872b50', 'Platform Overseer', NULL, NULL, 'admin', '2026-02-11 21:22:54', '2026-02-11 21:22:54', NULL),
('bfe7c994-3150-4713-b7bd-3fcacac41502', 'bb18903e-be28-4be7-9e0e-583f08149b77', 'muskan', NULL, NULL, 'customer', '2026-02-11 23:48:23', '2026-02-11 23:48:23', NULL),
('ce6243c0-99c4-4e11-b363-15374f7d9169', '1450a01e-6e5e-4f3c-a953-22653cf13c19', 'krrisha singh', '+607894561230', NULL, 'customer', '2026-02-02 21:11:26', '2026-02-02 21:11:26', NULL),
('db2686ae-4352-4a61-970b-8a5b49b4ea6f', '2a25937e-1c25-46a6-bccb-ffaf217a7274', 'test', NULL, NULL, 'customer', '2026-02-02 00:44:35', '2026-02-02 00:44:35', NULL),
('e5ef4584-2320-4166-9dc7-684b5bfc7b35', '038aaf6a-d8e1-40f7-989c-da6d9294bc72', 'aman jeet singh', '+609015202035', NULL, 'customer', '2026-02-01 04:15:12', '2026-02-01 04:15:12', NULL),
('f384642d-fc0a-4891-9aca-4f61dadcabcf', '89140b81-bf98-494e-bf6c-31472d2b11e2', 'ravi', '+607894561230', NULL, 'salon_owner', '2026-02-02 00:38:25', '2026-02-02 00:38:25', NULL);


-- --------------------------------------------------------
-- Table structure for table `reminders`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `reminders`;

CREATE TABLE `reminders` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `booking_id` varchar(36) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` enum('pending','sent','cancelled') DEFAULT 'pending',
  `reminder_type` enum('manual','automated_followup') DEFAULT 'manual',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `salon_id` (`salon_id`),
  KEY `booking_id` (`booking_id`),
  KEY `idx_scheduled_at` (`scheduled_at`),
  KEY `idx_status` (`status`),
  CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reminders_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reminders_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `role_permissions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `role_permissions`;

CREATE TABLE `role_permissions` (
  `role` varchar(50) NOT NULL,
  `permission_id` varchar(36) NOT NULL,
  PRIMARY KEY (`role`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `role_permissions`
-- 17 rows

INSERT INTO `role_permissions` (`role`, `permission_id`) VALUES
('manager', '248432dc-fae9-11f0-b172-a4f9334d99db'),
('manager', '24843a63-fae9-11f0-b172-a4f9334d99db'),
('manager', '24843b09-fae9-11f0-b172-a4f9334d99db'),
('manager', '24843b43-fae9-11f0-b172-a4f9334d99db'),
('manager', '24843bb6-fae9-11f0-b172-a4f9334d99db'),
('manager', '24843bf2-fae9-11f0-b172-a4f9334d99db'),
('manager', '24843c3d-fae9-11f0-b172-a4f9334d99db'),
('owner', '248432dc-fae9-11f0-b172-a4f9334d99db'),
('owner', '24843a63-fae9-11f0-b172-a4f9334d99db'),
('owner', '24843b09-fae9-11f0-b172-a4f9334d99db'),
('owner', '24843b43-fae9-11f0-b172-a4f9334d99db'),
('owner', '24843b7b-fae9-11f0-b172-a4f9334d99db'),
('owner', '24843bb6-fae9-11f0-b172-a4f9334d99db'),
('owner', '24843bf2-fae9-11f0-b172-a4f9334d99db'),
('owner', '24843c3d-fae9-11f0-b172-a4f9334d99db'),
('staff', '248432dc-fae9-11f0-b172-a4f9334d99db'),
('staff', '24843bf2-fae9-11f0-b172-a4f9334d99db');


-- --------------------------------------------------------
-- Table structure for table `salon_addons`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `salon_addons`;

CREATE TABLE `salon_addons` (
  `id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `addon_id` varchar(36) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `salon_id` (`salon_id`,`addon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table structure for table `salon_inventory`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `salon_inventory`;

CREATE TABLE `salon_inventory` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `salon_id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `min_stock_level` int(11) DEFAULT 5,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `supplier_name` varchar(255) DEFAULT NULL,
  `last_restocked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_public_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_salon_id` (`salon_id`),
  KEY `idx_category` (`category`),
  CONSTRAINT `salon_inventory_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `salon_knowledge_base`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `salon_knowledge_base`;

CREATE TABLE `salon_knowledge_base` (
  `id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `service_id` varchar(36) DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Skin Care',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `salon_id` (`salon_id`),
  KEY `fk_knowledge_base_service` (`service_id`),
  CONSTRAINT `fk_knowledge_base_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salon_knowledge_base_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `salon_offers`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `salon_offers`;

CREATE TABLE `salon_offers` (
  `id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed','bogo') NOT NULL,
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_usage` int(11) DEFAULT NULL,
  `usage_count` int(11) DEFAULT 0,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_salon_code` (`salon_id`,`code`),
  KEY `idx_salon_id` (`salon_id`),
  CONSTRAINT `salon_offers_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `salon_offers`
-- 3 rows

INSERT INTO `salon_offers` (`id`, `salon_id`, `title`, `description`, `code`, `type`, `value`, `max_usage`, `usage_count`, `status`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
('43aadfbe-9d67-440a-9ca9-d934b9b56e4d', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'HHS', 'THIS IS TEST', 'SUPER 30', 'percentage', '30.00', NULL, '0', 'active', NULL, NULL, '2026-02-13 15:31:07', '2026-02-13 15:31:07'),
('8a74a3f2-8a15-47e3-b0af-8b6c6501872a', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'New Year discount 15%', 'for new year', 'SMY2026', 'percentage', '15.00', '30', '0', 'active', '2026-02-01 00:00:00', '2026-02-06 00:00:00', '2026-02-02 21:30:02', '2026-02-02 21:30:02'),
('f772df43-cd8b-405b-997d-d8fc2f4c846c', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'save 20', 'save your 20%', 'SAVE20', 'fixed', '20.00', '20', '0', 'active', '2026-02-09 00:00:00', '2026-02-15 00:00:00', '2026-02-13 14:45:02', '2026-02-13 14:49:37');


-- --------------------------------------------------------
-- Table structure for table `salon_subscriptions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `salon_subscriptions`;

CREATE TABLE `salon_subscriptions` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `salon_id` varchar(36) NOT NULL,
  `plan_id` varchar(36) NOT NULL,
  `status` enum('active','cancelled','expired','upgraded','downgraded') DEFAULT 'active',
  `amount` decimal(10,2) NOT NULL,
  `billing_cycle` enum('monthly','yearly') NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_date` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subscription_start_date` datetime DEFAULT NULL,
  `subscription_end_date` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  KEY `idx_salon_id` (`salon_id`),
  CONSTRAINT `salon_subscriptions_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salon_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `salon_subscriptions`
-- 3 rows

INSERT INTO `salon_subscriptions` (`id`, `salon_id`, `plan_id`, `status`, `amount`, `billing_cycle`, `start_date`, `end_date`, `payment_method`, `payment_reference`, `created_at`, `subscription_start_date`, `subscription_end_date`, `updated_at`) VALUES
('0ea6737f-621a-4d17-af81-dbf498b342eb', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', '4ba56ba7-fe7b-11f0-bc07-a4f9334d99db', 'active', '0.00', 'monthly', '2026-02-02 21:08:37', NULL, NULL, NULL, '2026-02-02 21:08:37', '2026-02-02 21:08:37', '2026-03-02 21:08:59', '2026-02-02 21:08:59'),
('40de210b-451e-4061-bfe5-d175b7bd9a5e', '71a9b518-f586-4906-b827-a0cc4619decc', '4ba56ba7-fe7b-11f0-bc07-a4f9334d99db', 'active', '0.00', 'monthly', '2026-02-02 00:43:57', NULL, NULL, NULL, '2026-02-02 00:43:57', '2026-02-02 00:43:57', '2026-03-02 01:58:45', '2026-02-02 01:58:45'),
('6041c5f3-35ae-4451-a77f-cd2492ce805b', '3cb7202f-f091-41f5-92de-8a4f35990713', '4ba518ee-fe7b-11f0-bc07-a4f9334d99db', 'active', '0.00', 'monthly', '2026-01-31 22:45:33', NULL, NULL, NULL, '2026-01-31 22:45:33', '2026-01-31 22:45:33', '2026-03-01 20:08:26', '2026-02-01 20:08:26');


-- --------------------------------------------------------
-- Table structure for table `salon_suppliers`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `salon_suppliers`;

CREATE TABLE `salon_suppliers` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `salon_id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_salon_id` (`salon_id`),
  CONSTRAINT `salon_suppliers_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `salons`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `salons`;

CREATE TABLE `salons` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `owner_password_plain` varchar(255) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `upi_id` varchar(255) DEFAULT NULL,
  `bank_details` text DEFAULT NULL,
  `logo_url` text DEFAULT NULL,
  `cover_image_url` text DEFAULT NULL,
  `business_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`business_hours`)),
  `tax_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tax_settings`)),
  `notification_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_settings`)),
  `is_active` tinyint(1) DEFAULT 1,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `blocked_at` timestamp NULL DEFAULT NULL,
  `blocked_by` varchar(36) DEFAULT NULL,
  `block_reason` text DEFAULT NULL,
  `subscription_plan_id` varchar(36) DEFAULT NULL,
  `subscription_status` enum('trial','active','past_due','cancelled','expired') DEFAULT 'trial',
  `subscription_start_date` timestamp NULL DEFAULT NULL,
  `subscription_end_date` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 14 day),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logo_public_id` varchar(255) DEFAULT NULL,
  `cover_image_public_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_approval_status` (`approval_status`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `salons`
-- 8 rows

INSERT INTO `salons` (`id`, `name`, `slug`, `description`, `address`, `city`, `state`, `pincode`, `phone`, `email`, `owner_password_plain`, `gst_number`, `upi_id`, `bank_details`, `logo_url`, `cover_image_url`, `business_hours`, `tax_settings`, `notification_settings`, `is_active`, `approval_status`, `approved_at`, `approved_by`, `rejection_reason`, `blocked_at`, `blocked_by`, `block_reason`, `subscription_plan_id`, `subscription_status`, `subscription_start_date`, `subscription_end_date`, `trial_ends_at`, `created_at`, `updated_at`, `logo_public_id`, `cover_image_public_id`) VALUES
('2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'LuxeGlow Salon & Studio', 'luxeglow-salon-studio', NULL, NULL, NULL, NULL, NULL, '+607894561230', 'contact@gmail.com', NULL, NULL, NULL, NULL, 'https://res.cloudinary.com/de28lezdr/image/upload/v1770046697/salon_uploads/cciqarkrgt7yizvumzj3.jpg', NULL, NULL, NULL, NULL, '1', 'approved', '2026-02-02 21:00:44', '01cada4b-d998-4681-83a8-a5e31e8addac', NULL, NULL, NULL, NULL, NULL, 'trial', NULL, NULL, '2026-02-16 21:00:12', '2026-02-02 21:00:12', '2026-02-02 21:08:17', 'salon_uploads/cciqarkrgt7yizvumzj3', NULL),
('2d6dba5c-8526-480a-94c8-00a76f00790c', 'ankit', 'ankit', '', NULL, 'bhopal', 'bhopal', NULL, NULL, NULL, 'ankit@gmail.com', NULL, NULL, NULL, 'https://res.cloudinary.com/de28lezdr/image/upload/v1770893907/salon_uploads/rh0dolq2rurdvujgpcpx.jpg', NULL, NULL, NULL, NULL, '1', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'trial', NULL, NULL, '2026-02-25 23:20:32', '2026-02-11 23:20:32', '2026-02-12 16:28:29', 'salon_uploads/rh0dolq2rurdvujgpcpx', NULL),
('3cb7202f-f091-41f5-92de-8a4f35990713', 'Luxe Glow Studio', 'luxe-glow-studio', 'Luxe Glow Studio Malaysia ka ek premium aur modern beauty & wellness salon hai, jo international standards ke sath personalized services provide karta hai. Yeh salon un logon ke liye design kiya gaya hai jo luxury, hygiene aur expert care ek hi jagah chahte hain.\n\nLuxe Glow Studio mein aapko milti hain highly trained stylists aur certified beauticians, jo latest global beauty trends aur advanced techniques ka use karte hain. Chahe aapko simple grooming chahiye ya complete makeover – yahan har client ko individual attention di jati hai.', 'Sunway Geo Avenue, Bandar Sunway, Selangor, Malaysia', NULL, NULL, NULL, '+60123456789', 'luxeglowstudio@gmail.com', 'LuxeGlow@123', NULL, NULL, NULL, 'https://res.cloudinary.com/de28lezdr/image/upload/v1769871854/salon_uploads/onmchbgt4opc8jz4qzzk.png', 'https://res.cloudinary.com/de28lezdr/image/upload/v1769871784/salon_uploads/yydgjq155qbqeiiwvhem.avif', NULL, NULL, NULL, '1', 'approved', '2026-01-31 20:18:34', '629be37cbc9287123147bdc116b6861b', NULL, NULL, NULL, NULL, NULL, 'trial', NULL, NULL, '2026-02-14 20:18:01', '2026-01-31 20:18:01', '2026-02-01 20:05:39', 'salon_uploads/onmchbgt4opc8jz4qzzk', 'salon_uploads/yydgjq155qbqeiiwvhem'),
('71a9b518-f586-4906-b827-a0cc4619decc', 'ravi salon', 'ravisalon-gmail-com', NULL, NULL, NULL, NULL, NULL, '+607894561230', 'ravisalon@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', 'approved', '2026-02-02 00:39:37', '01cada4b-d998-4681-83a8-a5e31e8addac', NULL, NULL, NULL, NULL, NULL, 'trial', NULL, NULL, '2026-02-16 00:38:25', '2026-02-02 00:38:25', '2026-02-02 00:39:37', NULL, NULL),
('987bb6be-1178-495e-9af7-3819a91a24b0', 'ankit1 salon', 'ankit1-gmail-com', NULL, 'malysia', 'malysia', NULL, NULL, '+6078945612302', 'ankit1@gmail.com', NULL, NULL, NULL, NULL, 'https://res.cloudinary.com/de28lezdr/image/upload/v1770836996/salon_uploads/dstvgay7dx7ly2sovtom.webp', NULL, '{\"Monday\":{\"open\":\"09:00\",\"close\":\"20:00\",\"closed\":true},\"Tuesday\":{\"open\":\"09:00\",\"close\":\"20:00\",\"closed\":true},\"Wednesday\":{\"open\":\"09:00\",\"close\":\"20:00\",\"closed\":true},\"Thursday\":{\"open\":\"09:00\",\"close\":\"20:00\",\"closed\":true},\"Friday\":{\"open\":\"09:00\",\"close\":\"20:00\",\"closed\":true},\"Saturday\":{\"open\":\"09:00\",\"close\":\"20:00\",\"closed\":true},\"Sunday\":{\"open\":\"09:00\",\"close\":\"20:00\",\"closed\":true}}', NULL, NULL, '1', 'approved', '2026-02-12 00:36:22', '3276e64751aa7d5dbfe4646c4c872b50', NULL, NULL, NULL, NULL, NULL, 'trial', NULL, NULL, '2026-02-26 00:31:42', '2026-02-12 00:31:42', '2026-02-12 01:32:21', 'salon_uploads/dstvgay7dx7ly2sovtom', NULL),
('a78aba5e-517c-461e-a071-2b7efd0c9060', 'test1@gmail.com', 'test1-gmail-com', NULL, NULL, NULL, NULL, NULL, '+6085207410', 'test1@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', 'approved', '2026-02-11 23:49:11', '3276e64751aa7d5dbfe4646c4c872b50', NULL, NULL, NULL, NULL, NULL, 'trial', NULL, NULL, '2026-02-25 23:45:03', '2026-02-11 23:45:03', '2026-02-11 23:49:11', NULL, NULL),
('bb5ba87f-7f93-4dee-9191-04fc0254ce76', 'rahul', 'r', NULL, NULL, NULL, NULL, NULL, '+60789456123', 'rahul@gmail.com', 'Rahul@123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', 'approved', '2026-02-01 18:47:49', '01cada4b-d998-4681-83a8-a5e31e8addac', NULL, NULL, NULL, NULL, NULL, 'trial', NULL, NULL, '2026-02-15 18:47:13', '2026-02-01 18:47:13', '2026-02-01 19:57:34', NULL, NULL),
('d439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'rahuljain@gmail.com', 'rahuljain-gmail-com', NULL, NULL, NULL, NULL, NULL, '+607894561230', 'rahuljain@gmail.com', NULL, NULL, NULL, NULL, 'https://res.cloudinary.com/de28lezdr/image/upload/v1770905857/salon_uploads/cbkwj8irzqimibwmbryy.jpg', NULL, NULL, NULL, NULL, '1', 'approved', '2026-02-12 19:45:12', '3276e64751aa7d5dbfe4646c4c872b50', NULL, NULL, NULL, NULL, NULL, 'trial', NULL, NULL, '2026-02-26 19:45:07', '2026-02-12 19:45:07', '2026-02-12 19:47:39', 'salon_uploads/cbkwj8irzqimibwmbryy', NULL);


-- --------------------------------------------------------
-- Table structure for table `services`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `services`;

CREATE TABLE `services` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `salon_id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_public_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_salon_id` (`salon_id`),
  KEY `idx_category` (`category`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `services`
-- 10 rows

INSERT INTO `services` (`id`, `salon_id`, `name`, `description`, `price`, `duration_minutes`, `category`, `image_url`, `is_active`, `created_at`, `updated_at`, `image_public_id`) VALUES
('10e88c64-35f0-4497-adfb-19d50e716ff1', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'spa', 'test', '250.00', '30', NULL, 'https://res.cloudinary.com/de28lezdr/image/upload/v1770905850/salon_uploads/qiir2a2dkcd58qk7u5rq.jpg', '1', '2026-02-12 19:47:44', '2026-02-12 19:47:44', 'salon_uploads/qiir2a2dkcd58qk7u5rq'),
('ab015886-44d9-4b1d-a1fb-33809a5373b2', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Amber Cellular Facial', 'High-performance treatment for acne scars and uneven skin tone. Stimulates skin renewal at a cellular level.', '250.00', '90', 'Facials', 'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?w=800&auto=format&fit=crop', '1', '2026-02-07 04:17:41', '2026-02-07 04:17:41', NULL),
('c3a8e4ca-59db-4c8a-b98c-49f090938ccd', '71a9b518-f586-4906-b827-a0cc4619decc', 'haircut test', 'test', '13.00', '30', 'Hair', 'https://res.cloudinary.com/de28lezdr/image/upload/v1769973176/salon_uploads/ake4vstuwsbzxcnmnbln.jpg', '1', '2026-02-02 00:44:03', '2026-02-02 00:44:03', 'salon_uploads/ake4vstuwsbzxcnmnbln'),
('c60b85fa-6234-4614-8130-fdeb03710e7c', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Absolute Regeneration Treatment', 'Specialized treatment for sensitive skin and redness. Restores the skin barrier and provides instant relief.', '220.00', '75', 'Facials', 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=800&auto=format&fit=crop', '1', '2026-02-07 04:17:41', '2026-02-07 04:17:41', NULL),
('d99dea97-1e1b-455c-8dfd-eb21ea1684a0', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Hair Care & Styling', 'At LuxeGlow Salon & Studio, we offer a complete range of premium hair, beauty, and grooming services designed to enhance your natural look and boost your confidence. Our experienced stylists and beauty professionals use high-quality products, modern techniques, and personalized consultations to deliver flawless results every time', '100.00', '30', 'Hair', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770046779/salon_uploads/xpmtmzv6gilhrqyrtsfa.jpg', '1', '2026-02-02 21:09:46', '2026-02-03 02:01:08', 'salon_uploads/xpmtmzv6gilhrqyrtsfa'),
('de46370d-200b-44b5-85d5-e0cb3db47d9b', '987bb6be-1178-495e-9af7-3819a91a24b0', 'body massage', 'this is use for test', '200.00', '45', 'Body massage', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770836990/salon_uploads/rujxbqyqckybanuyslac.webp', '1', '2026-02-12 00:40:02', '2026-02-12 00:40:02', 'salon_uploads/rujxbqyqckybanuyslac'),
('f0eb84da-0e2f-4475-bec2-7d1b86238c24', '3cb7202f-f091-41f5-92de-8a4f35990713', 'Glow Signature Keratin Hair Treatment', 'A premium salon-grade keratin treatment designed to deeply repair damaged, frizzy, and chemically treated hair. This service restores natural shine, improves hair texture, reduces frizz, and strengthens hair from root to tip using professional keratin-infused formulas.\n\nBest for: Dry, damaged, frizzy & color-treated hair.', '50.00', '30', 'Hair', 'https://res.cloudinary.com/de28lezdr/image/upload/v1769879899/salon_uploads/sc4zynlmtd1mlc5qnlia.jpg', '1', '2026-01-31 22:48:27', '2026-01-31 22:49:53', 'salon_uploads/sc4zynlmtd1mlc5qnlia'),
('f2455145-41d4-4387-a604-408e26dde22c', '2d6dba5c-8526-480a-94c8-00a76f00790c', 'body massage ', 'this is for test now', '200.00', '30', NULL, 'https://res.cloudinary.com/de28lezdr/image/upload/v1770893897/salon_uploads/a9bi6ohp7hlozelgeba2.jpg', '1', '2026-02-12 16:41:40', '2026-02-12 16:41:40', 'salon_uploads/a9bi6ohp7hlozelgeba2'),
('fdb72c18-07c6-4a09-a289-d5b37229a6e9', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Signature Body Massage', 'A relaxing full-body massage using premium essential oils to release deep seated tension.', '150.00', '60', 'Massage', 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?w=800&auto=format&fit=crop', '1', '2026-02-07 04:17:41', '2026-02-07 04:17:41', NULL),
('fec11104-b3c3-4fe1-87b2-bfe9d286aebb', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Smart Hydra Programme', 'An intensive hydration treatment that refills skin moisture levels. Perfect for dry or tired skin.', '195.00', '60', 'Facials', 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=800&auto=format&fit=crop', '1', '2026-02-07 04:17:41', '2026-02-07 04:17:41', NULL);


-- --------------------------------------------------------
-- Table structure for table `staff_attendance`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `staff_attendance`;

CREATE TABLE `staff_attendance` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `staff_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `check_in` timestamp NOT NULL DEFAULT current_timestamp(),
  `check_out` timestamp NULL DEFAULT NULL,
  `status` enum('present','late','on_leave','absent') DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `salon_id` (`salon_id`),
  KEY `idx_staff_day` (`staff_id`,`check_in`),
  CONSTRAINT `staff_attendance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_attendance_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `staff_attendance`
-- 17 rows

INSERT INTO `staff_attendance` (`id`, `staff_id`, `salon_id`, `check_in`, `check_out`, `status`, `notes`, `created_at`) VALUES
('0aa9d0df-e623-4a20-b3e2-72a27146eee4', 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '2026-02-12 22:00:08', '2026-02-12 22:00:11', 'present', NULL, '2026-02-12 22:00:08'),
('114e0222-e61c-4f0d-b2a7-62d42f3dca4f', 'fdc36db5-de3e-4fac-903b-1260782b9160', '987bb6be-1178-495e-9af7-3819a91a24b0', '2026-02-12 15:02:12', '2026-02-12 15:02:13', 'present', NULL, '2026-02-12 15:02:12'),
('3cf5ed7e-ea9b-4240-8ebe-169a1d698d8c', 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '2026-02-12 20:41:54', '2026-02-12 20:55:09', 'present', NULL, '2026-02-12 20:41:54'),
('4e0132e6-cf55-4488-b06a-cb5880e98d2a', '2a41fdbd-fa37-4d13-b8b7-082fd537c030', '71a9b518-f586-4906-b827-a0cc4619decc', '2026-02-02 02:11:22', '2026-02-02 02:11:59', 'present', NULL, '2026-02-02 02:11:22'),
('4fb95b6d-319c-4c4b-8fc3-45690e41d4f0', 'e63c3964-9897-41c9-b74a-cfcfbb494102', '3cb7202f-f091-41f5-92de-8a4f35990713', '2026-02-01 20:26:06', '2026-02-01 20:26:09', 'present', NULL, '2026-02-01 20:26:06'),
('577e652b-32ab-4756-a042-07a429277205', 'e63c3964-9897-41c9-b74a-cfcfbb494102', '3cb7202f-f091-41f5-92de-8a4f35990713', '2026-02-12 15:05:32', '2026-02-12 15:05:33', 'present', NULL, '2026-02-12 15:05:32'),
('5840457f-d1d0-4a9f-91e2-6963895f3e95', '2a41fdbd-fa37-4d13-b8b7-082fd537c030', '71a9b518-f586-4906-b827-a0cc4619decc', '2026-02-02 02:09:43', '2026-02-02 02:09:45', 'present', NULL, '2026-02-02 02:09:43'),
('6173c79e-4e0e-482a-8801-dd3da7c9602b', 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '2026-02-12 20:10:18', '2026-02-12 20:10:26', 'present', NULL, '2026-02-12 20:10:18'),
('73da3049-4a85-4a9d-89b8-482b76f4dfd4', '2a41fdbd-fa37-4d13-b8b7-082fd537c030', '71a9b518-f586-4906-b827-a0cc4619decc', '2026-02-02 00:47:38', '2026-02-02 02:09:42', 'present', NULL, '2026-02-02 00:47:38'),
('7f9e14cd-e26d-4999-bbf1-cb9eb01074ca', 'e63c3964-9897-41c9-b74a-cfcfbb494102', '3cb7202f-f091-41f5-92de-8a4f35990713', '2026-02-11 23:09:30', '2026-02-11 23:12:12', 'present', NULL, '2026-02-11 23:09:30'),
('90d8d059-020b-42ab-bac1-ff8e3595835c', 'e63c3964-9897-41c9-b74a-cfcfbb494102', '3cb7202f-f091-41f5-92de-8a4f35990713', '2026-02-11 15:12:55', '2026-02-11 15:13:00', 'present', NULL, '2026-02-11 15:12:55'),
('b9bd5e91-b51e-468e-bce9-39d32a99d5aa', 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '2026-02-12 22:21:44', '2026-02-12 22:21:48', 'present', NULL, '2026-02-12 22:21:44'),
('bdea19a3-d400-4fa3-b066-971fb25b4405', 'fdc36db5-de3e-4fac-903b-1260782b9160', '987bb6be-1178-495e-9af7-3819a91a24b0', '2026-02-12 02:41:25', '2026-02-12 04:13:13', 'present', NULL, '2026-02-12 02:41:25'),
('c832b470-edcd-4fff-b4f5-51b742deb5ef', 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '2026-02-12 20:04:00', '2026-02-12 20:07:56', 'present', NULL, '2026-02-12 20:04:00'),
('d5da6da9-7300-406f-ba9c-7d52e660346a', 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '2026-02-12 23:03:19', '2026-02-12 23:03:22', 'present', NULL, '2026-02-12 23:03:19'),
('e28e79d2-1cba-4548-867b-75c5bdcfcee1', 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '2026-02-13 14:25:31', '2026-02-13 18:30:41', 'present', NULL, '2026-02-13 14:25:31'),
('f0142ac9-cc10-4354-bdfc-f1bee84a1d90', 'fdc36db5-de3e-4fac-903b-1260782b9160', '987bb6be-1178-495e-9af7-3819a91a24b0', '2026-02-12 12:57:45', '2026-02-12 12:58:08', 'present', NULL, '2026-02-12 12:57:45');


-- --------------------------------------------------------
-- Table structure for table `staff_leaves`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `staff_leaves`;

CREATE TABLE `staff_leaves` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `staff_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `leave_type` enum('sick','casual','vacation','other') DEFAULT 'casual',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `salon_id` (`salon_id`),
  CONSTRAINT `staff_leaves_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_leaves_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `staff_leaves`
-- 6 rows

INSERT INTO `staff_leaves` (`id`, `staff_id`, `salon_id`, `start_date`, `end_date`, `leave_type`, `status`, `reason`, `created_at`) VALUES
('36ad5a76-e902-46ea-b94c-53d0df77a82c', '2a41fdbd-fa37-4d13-b8b7-082fd537c030', '71a9b518-f586-4906-b827-a0cc4619decc', '2026-02-02', '2026-02-02', 'casual', 'pending', '', '2026-02-02 02:07:23'),
('730037f6-81b1-468e-87c0-4c9b99111573', 'e63c3964-9897-41c9-b74a-cfcfbb494102', '3cb7202f-f091-41f5-92de-8a4f35990713', '2026-02-11', '2026-02-11', '', 'pending', 'need the test', '2026-02-11 23:10:20'),
('8ae3993d-6aeb-48e4-828f-842581600d57', '207ac6cd-029e-40d4-ad47-9cb637ba434b', '2d6dba5c-8526-480a-94c8-00a76f00790c', '2026-02-12', '2026-02-12', 'sick', 'pending', 'test', '2026-02-12 00:24:55'),
('97a8cd74-1466-4b5f-aa40-0cb066fd4507', 'e63c3964-9897-41c9-b74a-cfcfbb494102', '3cb7202f-f091-41f5-92de-8a4f35990713', '2026-02-11', '2026-02-11', 'casual', 'pending', 'need a leave', '2026-02-11 15:13:21'),
('e0932955-a0e0-49d7-ae9e-5b53ef6ab01d', 'e63c3964-9897-41c9-b74a-cfcfbb494102', '3cb7202f-f091-41f5-92de-8a4f35990713', '2026-02-11', '2026-02-11', '', 'pending', 'need the test', '2026-02-11 23:10:20'),
('e4690b76-d016-44a9-a87b-824434c7aa58', 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '2026-02-12', '2026-02-12', 'sick', '', 'test', '2026-02-12 23:06:01');


-- --------------------------------------------------------
-- Table structure for table `staff_profiles`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `staff_profiles`;

CREATE TABLE `staff_profiles` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) DEFAULT NULL,
  `created_by_id` varchar(36) DEFAULT NULL,
  `salon_id` varchar(36) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar_url` text DEFAULT NULL,
  `specializations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specializations`)),
  `commission_percentage` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_salon_id` (`salon_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `fk_staff_creator` (`created_by_id`),
  CONSTRAINT `fk_staff_creator` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `staff_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `staff_profiles_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `staff_profiles`
-- 6 rows

INSERT INTO `staff_profiles` (`id`, `user_id`, `created_by_id`, `salon_id`, `display_name`, `email`, `phone`, `avatar_url`, `specializations`, `commission_percentage`, `is_active`, `created_at`, `updated_at`) VALUES
('207ac6cd-029e-40d4-ad47-9cb637ba434b', 'bb18903e-be28-4be7-9e0e-583f08149b77', '521b14ee-9cdc-44f1-a464-f3efa99b5dc2', '2d6dba5c-8526-480a-94c8-00a76f00790c', 'muskan', 'muskan@gmail.com', '+6074108520852', NULL, '[\"sd\"]', '0.00', '1', '2026-02-11 23:48:23', '2026-02-11 23:48:23'),
('2a41fdbd-fa37-4d13-b8b7-082fd537c030', '2a25937e-1c25-46a6-bccb-ffaf217a7274', '89140b81-bf98-494e-bf6c-31472d2b11e2', '71a9b518-f586-4906-b827-a0cc4619decc', 'test', 'test@gmail.com', '+607894561230', NULL, '[]', '0.00', '1', '2026-02-02 00:44:35', '2026-02-02 00:44:35'),
('70e9375d-8b2e-453f-b833-9d7a2d96a753', '21443efe-57bb-47bc-8048-1c5c478b824f', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'john doe', 'johndoe@gmail.com', '+608523697410', NULL, '[]', '0.00', '1', '2026-02-02 21:13:11', '2026-02-02 21:13:11'),
('cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', '76fa1757-d292-4031-b136-67b1ce0d05bb', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'amrita', 'amrita@gmail.com', '+607894561230', NULL, '[\"hair\"]', '31.00', '1', '2026-02-12 19:45:56', '2026-02-12 23:03:03'),
('e63c3964-9897-41c9-b74a-cfcfbb494102', 'b5108e08-c320-4204-b712-b6df306c3ed4', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'aman', 'aman@gmail.com', '+60788774774', NULL, '[]', '0.00', '1', '2026-02-01 20:09:03', '2026-02-01 20:09:03'),
('fdc36db5-de3e-4fac-903b-1260782b9160', 'bb18903e-be28-4be7-9e0e-583f08149b77', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'muskan', 'muskan@gmail.com', '+607894562222', NULL, '[]', '30.00', '1', '2026-02-12 02:40:47', '2026-02-12 02:40:47');


-- --------------------------------------------------------
-- Table structure for table `staff_services`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `staff_services`;

CREATE TABLE `staff_services` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `staff_id` varchar(36) NOT NULL,
  `service_id` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_staff_service` (`staff_id`,`service_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `staff_services_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `staff_services`
-- 1 rows

INSERT INTO `staff_services` (`id`, `staff_id`, `service_id`, `created_at`) VALUES
('f865fd9d-f714-4924-919f-99f38a1b66a3', 'cc52e1fe-d4e4-4a1a-97f3-7eb2095001e3', '10e88c64-35f0-4497-adfb-19d50e716ff1', '2026-02-12 23:03:03');


-- --------------------------------------------------------
-- Table structure for table `staff_specific_permissions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `staff_specific_permissions`;

CREATE TABLE `staff_specific_permissions` (
  `staff_id` varchar(36) NOT NULL,
  `permission_id` varchar(36) NOT NULL,
  `is_allowed` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`staff_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `staff_specific_permissions_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_specific_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `subscription_addons`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `subscription_addons`;

CREATE TABLE `subscription_addons` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price_monthly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `icon` varchar(50) DEFAULT 'Puzzle',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `subscription_addons`
-- 4 rows

INSERT INTO `subscription_addons` (`id`, `name`, `slug`, `description`, `price_monthly`, `icon`, `is_active`, `created_at`, `updated_at`) VALUES
('2166ead5-dd0c-41de-a72b-b97a7b4822a6', 'Advanced Analytics', 'advanced-analytics', 'Detailed business insights and performance metrics', '499.00', 'BarChart3', '1', '2026-01-31 14:10:07', '2026-01-31 14:10:07'),
('c74df255-9c54-463d-a3cc-609e76cf47ee', 'WhatsApp Integration', 'whatsapp-integration', 'Send booking confirmations and reminders via WhatsApp', '299.00', 'Smartphone', '1', '2026-01-31 14:10:07', '2026-01-31 14:10:07'),
('ef1f5eca-794b-4e9c-8900-71cfc6463279', 'Website Integration', 'website-integration', 'Embed booking widget on your salon website', '799.00', 'Globe', '1', '2026-01-31 14:10:07', '2026-01-31 14:10:07'),
('f4d5b96e-e6a3-4fa6-8464-317dfde6c4e7', 'Dedicated Support', 'dedicated-support', 'Priority support with dedicated account manager', '1999.00', 'Headphones', '1', '2026-01-31 14:10:07', '2026-01-31 14:10:07');


-- --------------------------------------------------------
-- Table structure for table `subscription_plans`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `subscription_plans`;

CREATE TABLE `subscription_plans` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price_monthly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_yearly` decimal(10,2) DEFAULT NULL,
  `max_staff` int(11) DEFAULT 5,
  `max_services` int(11) DEFAULT 20,
  `max_bookings_per_month` int(11) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `subscription_plans`
-- 3 rows

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `description`, `price_monthly`, `price_yearly`, `max_staff`, `max_services`, `max_bookings_per_month`, `features`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`) VALUES
('4ba3da1d-fe7b-11f0-bc07-a4f9334d99db', 'Starter', 'starter', 'Perfect for small salons getting started', '999.00', '9990.00', '5', '20', NULL, '[\"Up to 100 bookings\\/month\",\"Basic appointment management\",\"Customer database\",\"SMS notifications\",\"Mobile app access\",\"Basic reporting\",\"Email support\"]', '1', '0', '1', '2026-01-31 13:33:11', '2026-01-31 13:33:11'),
('4ba518ee-fe7b-11f0-bc07-a4f9334d99db', 'Professional', 'professional', 'Most popular choice for growing salons', '2499.00', '24990.00', '20', '100', NULL, '[\"Unlimited bookings\",\"Advanced appointment management\",\"Customer loyalty programs\",\"SMS + Email + WhatsApp notifications\",\"Staff management\",\"Inventory tracking\",\"Advanced analytics\",\"Online payments\",\"Custom branding\",\"Priority support\"]', '1', '1', '2', '2026-01-31 13:33:11', '2026-01-31 13:33:11'),
('4ba56ba7-fe7b-11f0-bc07-a4f9334d99db', 'Enterprise', 'enterprise', 'Complete solution for salon chains', '4999.00', '49990.00', '999', '999', NULL, '[\"Everything in Professional\",\"Unlimited salon locations\",\"Multi-location management\",\"Advanced staff scheduling\",\"Franchise management\",\"Custom integrations\",\"White-label solution\",\"Dedicated account manager\",\"24\\/7 phone support\",\"Custom training\"]', '1', '0', '3', '2026-01-31 13:33:11', '2026-01-31 13:33:11');


-- --------------------------------------------------------
-- Table structure for table `treatment_records`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `treatment_records`;

CREATE TABLE `treatment_records` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `booking_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `treatment_details` text DEFAULT NULL,
  `products_used` text DEFAULT NULL,
  `skin_reaction` text DEFAULT NULL,
  `improvement_notes` text DEFAULT NULL,
  `recommended_next_treatment` text DEFAULT NULL,
  `post_treatment_instructions` text DEFAULT NULL,
  `follow_up_reminder_date` date DEFAULT NULL,
  `marketing_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `before_photo_url` varchar(255) DEFAULT NULL,
  `after_photo_url` varchar(255) DEFAULT NULL,
  `before_photo_public_id` varchar(255) DEFAULT NULL,
  `after_photo_public_id` varchar(255) DEFAULT NULL,
  `service_name_manual` varchar(255) DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_id` (`booking_id`),
  UNIQUE KEY `idx_unique_booking` (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `salon_id` (`salon_id`),
  CONSTRAINT `treatment_records_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `treatment_records_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `treatment_records_ibfk_3` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `treatment_records`
-- 6 rows

INSERT INTO `treatment_records` (`id`, `booking_id`, `user_id`, `salon_id`, `treatment_details`, `products_used`, `skin_reaction`, `improvement_notes`, `recommended_next_treatment`, `post_treatment_instructions`, `follow_up_reminder_date`, `marketing_notes`, `created_at`, `updated_at`, `before_photo_url`, `after_photo_url`, `before_photo_public_id`, `after_photo_public_id`, `service_name_manual`, `record_date`) VALUES
('07350f15-0212-480e-a951-ee1aaa5f61ba', 'cb27be0e-abe7-4dd9-9a7f-0c3b1445b6cc', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '', '', '', '', NULL, NULL, NULL, NULL, '2026-02-13 14:25:39', '2026-02-13 14:25:39', NULL, NULL, NULL, NULL, NULL, NULL),
('0ba3a699-374d-4aca-b4b9-dcc4fca00aa2', '0512959c-eb55-410b-aedf-a4074930618d', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '', '', '', '', NULL, NULL, NULL, NULL, '2026-02-13 14:29:13', '2026-02-13 14:29:13', NULL, NULL, NULL, NULL, NULL, NULL),
('31bcf7a9-15e0-4ef2-b067-236a7009a894', '14e49c3d-eb1c-41c8-9bb9-96ec80b63cd4', 'c34b21db-81fd-42fa-a830-6d4e08360b5e', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', '', '', '', '', NULL, NULL, NULL, NULL, '2026-02-13 14:32:51', '2026-02-13 14:55:17', NULL, NULL, NULL, NULL, NULL, NULL),
('55b2c3ab-0779-421b-b477-b60f45ef4843', '1a0ce12a-df55-4db9-8c10-a2161b837a96', 'a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', '3cb7202f-f091-41f5-92de-8a4f35990713', '', '', '', '', '', '', NULL, NULL, '2026-02-07 15:34:21', '2026-02-07 15:34:21', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770458640/salon_uploads/xlfeytitdw3ryaffv80j.png', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770458655/salon_uploads/qniqou4glgxrigp6ngdn.png', NULL, NULL, NULL, NULL),
('c20cce33-6e1e-402d-a23d-393c4c9052d8', '1d04f305-12f3-43c5-8865-590ec819986c', '60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'sjn', '', '', '', '', '', NULL, NULL, '2026-02-03 03:36:55', '2026-02-03 03:36:55', '', '', NULL, NULL, NULL, NULL),
('e6bd1fba-7b4a-4fde-8758-d20ae408cefd', 'a757c2b4-5c2e-4ed2-bcd1-c91a7db53fac', '1450a01e-6e5e-4f3c-a953-22653cf13c19', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'Deep Cleansing Facial with gentle exfoliation, steam therapy, blackhead removal, calming massage, and hydration mask application. The procedure was customized according to the client’s skin type and sensitivity level.', 'Cleanser: Cetaphil Gentle Skin Cleanser', 'no', 'Hydration Facial after 3–4 weeks', 'Vitamin C Therapy for skin brightening', 'Regular clean-up every month for maintenance', '0000-00-00', 'Record Status: Completed', '2026-02-02 21:22:10', '2026-02-02 21:22:10', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770047520/salon_uploads/lwyc53lgd087a2kmernh.jpg', 'https://res.cloudinary.com/de28lezdr/image/upload/v1770047528/salon_uploads/cbtppncbpqlkhef9pwvg.jpg', NULL, NULL, NULL, NULL);


-- --------------------------------------------------------
-- Table structure for table `user_roles`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `user_roles`;

CREATE TABLE `user_roles` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `user_id` varchar(36) NOT NULL,
  `salon_id` varchar(36) NOT NULL,
  `role` enum('owner','manager','staff','super_admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_salon` (`user_id`,`salon_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_salon_id` (`salon_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `user_roles`
-- 15 rows

INSERT INTO `user_roles` (`id`, `user_id`, `salon_id`, `role`, `created_at`) VALUES
('11c15e80-6510-41c6-9091-11f1daa9719e', '55c5c311-71b7-409b-920e-f68eda0e103f', 'a78aba5e-517c-461e-a071-2b7efd0c9060', 'owner', '2026-02-11 23:45:03'),
('22bb6b25-2df1-4fdb-b5d4-6a800980811b', '2a25937e-1c25-46a6-bccb-ffaf217a7274', '71a9b518-f586-4906-b827-a0cc4619decc', 'staff', '2026-02-02 00:44:35'),
('2948ad3c-0772-11f1-a0e4-088fc3ed01dd', '521b14ee-9cdc-44f1-a464-f3efa99b5dc2', '2d6dba5c-8526-480a-94c8-00a76f00790c', 'owner', '2026-02-11 23:20:32'),
('2bb15e06-91d2-44e1-b549-40cf73b6a80e', 'ad571322-8d73-4d6c-8c45-c239e0e325f4', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'owner', '2026-02-02 21:00:12'),
('476ef671-ff7b-11f0-b059-088fc3ed01dd', 'e5d342e1-7a2c-45ee-ab1c-564e64523130', '3cb7202f-f091-41f5-92de-8a4f35990713', 'owner', '2026-02-01 20:05:39'),
('710c0464-483c-4a2d-977d-32805bda206a', '76fa1757-d292-4031-b136-67b1ce0d05bb', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'staff', '2026-02-12 19:45:56'),
('8f1b8328-d06a-40dd-bafc-4358f678f659', '89140b81-bf98-494e-bf6c-31472d2b11e2', '71a9b518-f586-4906-b827-a0cc4619decc', 'owner', '2026-02-02 00:38:25'),
('977072fc-36e5-48a7-8340-4653226f2524', '50f093c6-9762-44ef-a7b5-10c3e455efed', 'bb5ba87f-7f93-4dee-9191-04fc0254ce76', 'owner', '2026-02-01 18:47:13'),
('b8f8b321-212d-4530-bb18-ff7543df784f', 'bb18903e-be28-4be7-9e0e-583f08149b77', '2d6dba5c-8526-480a-94c8-00a76f00790c', 'staff', '2026-02-11 23:48:23'),
('bc2c5903-1261-44d7-808e-506584cd9c2e', '82cc4bf0-d85d-4bac-b92c-c064358b7c85', '987bb6be-1178-495e-9af7-3819a91a24b0', 'owner', '2026-02-12 00:31:42'),
('cdfa4691-cb9d-4048-910f-99d81f981820', 'b5108e08-c320-4204-b712-b6df306c3ed4', '3cb7202f-f091-41f5-92de-8a4f35990713', 'staff', '2026-02-01 20:09:03'),
('e9187e49-bb36-40a0-abe6-5d0e7c08c46e', '1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'd439706f-aa86-4faf-8e7a-d73d5c3bce9b', 'owner', '2026-02-12 19:45:07'),
('eadffc68-2081-478f-adb9-e9eb163f7edd', 'bb18903e-be28-4be7-9e0e-583f08149b77', '987bb6be-1178-495e-9af7-3819a91a24b0', 'staff', '2026-02-12 02:40:47'),
('f251542e-6f38-424b-ae79-620deb2f7bd6', '21443efe-57bb-47bc-8048-1c5c478b824f', '2272b598-4c02-4c65-92e8-a7f4ea5d9d2f', 'staff', '2026-02-02 21:13:11'),
('f948c82f-52a4-4dd4-91ee-dcaa70c81650', '2772c605-856c-436a-9de6-6c83cfea6ac1', '987bb6be-1178-495e-9af7-3819a91a24b0', 'staff', '2026-02-12 00:43:57');


-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `coin_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `coin_max_limit` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
-- 24 rows

INSERT INTO `users` (`id`, `email`, `password_hash`, `coin_balance`, `email_verified`, `created_at`, `updated_at`, `coin_max_limit`) VALUES
('01cada4b-d998-4681-83a8-a5e31e8addac', 'superadmin@salon.com', '$2y$10$BGa.5tD/eQjgw2uEFmRm4eJqE8Y6NIkfp4ZwNj255ZSyg98O7Zu2u', '0.00', '0', '2026-02-01 17:00:50', '2026-02-01 17:00:50', NULL),
('038aaf6a-d8e1-40f7-989c-da6d9294bc72', 'amanjeetthakur644@gmail.com', '$2y$10$fxMAxJWzgxr3v3ZzRNs02u3dWWETvY97U4LjyVmjf89RWnTe1kFQS', '0.00', '0', '2026-02-01 04:15:12', '2026-02-01 04:15:12', NULL),
('1450a01e-6e5e-4f3c-a953-22653cf13c19', 'krishnasingh12@gmail.com', '$2y$10$BF55/Z/BmIMYWzqNR89B.eMv2Q3U.XDdXPyHcceV8TXuOaDO9ID8y', '10.00', '0', '2026-02-02 21:11:26', '2026-02-03 01:50:43', NULL),
('1ee8f9ce-4659-4d25-8967-a713ba7fcc5a', 'rahuljain@gmail.com', '$2y$10$0oPhNcslnFK0KVN8vMcHSeMFDL9rCVjydsVoNQbur.vIcBLzyD6aO', '0.00', '0', '2026-02-12 19:45:07', '2026-02-12 19:45:07', NULL),
('21443efe-57bb-47bc-8048-1c5c478b824f', 'johndoe@gmail.com', '$2y$10$cKrUmLJutXEgw0rj1yIyWO5xnS.OppYsdwyQD1b0qwnG1suU.4YLa', '0.00', '0', '2026-02-02 21:13:11', '2026-02-02 21:13:11', NULL),
('2772c605-856c-436a-9de6-6c83cfea6ac1', 'sanjna@gmail.com', '$2y$10$Mj54zguj2BLaUzR4z40BQeAx0H7UUMXkr2eLm.aWErLxyXfnL1lle', '0.00', '0', '2026-02-12 00:43:57', '2026-02-12 00:43:57', NULL),
('2a25937e-1c25-46a6-bccb-ffaf217a7274', 'test@gmail.com', '$2y$10$dxJjPygsrXmHqW7w6O.PFu32BlsLL/73lcoFklr0Wm.Iajsskz7dK', '2.00', '0', '2026-02-02 00:44:35', '2026-02-03 02:37:03', NULL),
('3276e64751aa7d5dbfe4646c4c872b50', 'superadmin@local.host', '$2y$10$1bCYQ8u5F2lymISlbKrQG.XZBPw4wLlQyFc.JwaUp7mdAlBCq3F.m', '0.00', '0', '2026-02-11 21:22:54', '2026-02-11 21:22:54', NULL),
('35128bea-2e85-48e6-91ff-ea99739a74a5', 'deepak@gmail.com', '$2y$10$J6BWe9yAtxLNy5W7q9YO2OEYJ5aGP500ai6DIJQ85.xQBhP.ZrGyG', '75.00', '0', '2026-02-12 20:04:56', '2026-02-12 21:54:14', NULL),
('500d3490-5844-4507-9104-d2fc3e873256', 'rahulq1@gmail.com', '$2y$10$DfX8XZGfkaoIa.Bsyeh2w.q0LQaI7KN03bogPqKEb0L3qb.BLOjqi', '60.00', '0', '2026-02-12 00:57:10', '2026-02-12 02:08:14', NULL),
('50f093c6-9762-44ef-a7b5-10c3e455efed', 'rahul@gmail.com', '$2y$10$1y2w7OBnA7aZ4iMgFM48yuBu1aKTLE6BD6jUyMZKcWjM8/FD.ZNZm', '0.00', '0', '2026-02-01 18:47:13', '2026-02-01 19:57:34', NULL),
('521b14ee-9cdc-44f1-a464-f3efa99b5dc2', 'ankit@gmail.com', '$2y$10$UYzr6iEUWiE71k.jfDtvDuYAQihdGZi7jwwZq5cJe/X0UsAsc0SHi', '0.00', '0', '2026-02-11 23:19:08', '2026-02-11 23:19:08', NULL),
('55c5c311-71b7-409b-920e-f68eda0e103f', 'test1@gmail.com', '$2y$10$M8khyXB4CiWzYCzgAUE2O.AZd3BsE/U9eutZXuKdl3AjqFGRWqd26', '0.00', '0', '2026-02-11 23:45:03', '2026-02-11 23:45:03', NULL),
('60ddf1ef-e42a-44e9-9db2-6a37b0db7c8b', 'aman12@gmail.com', '$2y$10$sdS0B1ThEOrCXiUU.aqb9u0ZOh42vOpDgCQuH9od8yOHuHk.pgfu2', '0.00', '0', '2026-02-03 02:12:46', '2026-02-13 20:08:04', NULL),
('76fa1757-d292-4031-b136-67b1ce0d05bb', 'amrita@gmail.com', '$2y$10$WRlpoeAqA6eBkK1s.iTqcOfNj6kK4RwGsbS9IlhKgcIXgIQlazUhi', '0.00', '0', '2026-02-12 19:45:56', '2026-02-12 19:45:56', NULL),
('82cc4bf0-d85d-4bac-b92c-c064358b7c85', 'ankit1@gmail.com', '$2y$10$QBGj7DwNbAxnFy5NZoq0Ee2oQr5EuafEq9jJOdn4xLnLOxXNaVALC', '0.00', '0', '2026-02-12 00:31:42', '2026-02-12 00:31:42', NULL),
('89140b81-bf98-494e-bf6c-31472d2b11e2', 'ravisalon@gmail.com', '$2y$10$B/NVXnVsmqDD.7pusPdNrOrVvnMY8bv.i6JRKkn33GysmWM.02Qk2', '0.00', '0', '2026-02-02 00:38:25', '2026-02-02 00:38:25', NULL),
('a3ce9cd2-7438-4cbf-b145-d4154ad8fc92', 'krishna@gmail.com', '$2y$10$gUKjzXKg1R3sCvmI4c7dH.0//DG2dLsEegezIqpkJ03/WD25EIpDi', '5.00', '0', '2026-02-01 19:17:36', '2026-02-01 20:09:36', NULL),
('ad571322-8d73-4d6c-8c45-c239e0e325f4', 'contact@gmail.com', '$2y$10$aGLIlSNYxHe//LO5gU.4hOAT8XgXufLBnxjjUPgOwyECrb3hwTSTa', '0.00', '0', '2026-02-02 21:00:12', '2026-02-02 21:00:12', NULL),
('b5108e08-c320-4204-b712-b6df306c3ed4', 'aman@gmail.com', '$2y$10$C/F3Gen/C3hjvV1xqDeswud2kSLok9Aq8Q7fJsiuW4HOa1hPJMVgC', '0.00', '0', '2026-02-01 20:09:03', '2026-02-01 20:09:03', NULL),
('bb18903e-be28-4be7-9e0e-583f08149b77', 'muskan@gmail.com', '$2y$10$jNzvp7zWdbSwdaXNVGwhCO6FfSIl0RghqAtMeqY1/5b0l/sVwKbIy', '0.00', '0', '2026-02-11 23:48:23', '2026-02-11 23:48:23', NULL),
('c34b21db-81fd-42fa-a830-6d4e08360b5e', 'jatin@gmail.com', '$2y$10$aknq4MIxpZoMhmWSM7DVYeEr8SBE2.IXHiYobHRPq6RXys8kxn9sq', '175.00', '0', '2026-02-12 19:54:23', '2026-02-13 15:51:51', NULL),
('cf230af6-8c44-40a3-b0c8-9427cd09ef9e', 'riya@gmail.com', '$2y$10$rXo7mBSJlk11MtkQnJypBOW5QxNHzviu4mHdxpQSOfzemLBZKMAMG', '0.00', '0', '2026-02-11 23:25:35', '2026-02-11 23:25:35', NULL),
('e5d342e1-7a2c-45ee-ab1c-564e64523130', 'luxeglowstudio@gmail.com', '$2y$10$3gJyujBdJqJIirVpdRbioOocIUw5CZSvRI5NLN7yKrtAEzrVjbE..', '0.00', '0', '2026-02-01 20:05:39', '2026-02-01 20:05:39', NULL);


SET FOREIGN_KEY_CHECKS=1;

-- Export completed: 2026-02-14 08:48:45
-- Total tables: 43
-- Total rows: 353

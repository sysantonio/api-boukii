/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `boukii_v5` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `boukii_v5`;
DROP TABLE IF EXISTS `booking_drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_drafts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `session_id` varchar(125) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL CHECK (json_valid(`data`)),
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(125) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(125) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(125) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) NOT NULL,
  `guard_name` varchar(125) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(125) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `school_id` bigint(20) unsigned DEFAULT NULL,
  `season_id` bigint(20) unsigned DEFAULT NULL,
  `context_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`context_data`)),
  `name` varchar(125) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_school_id_season_id_index` (`school_id`,`season_id`),
  KEY `personal_access_tokens_tokenable_id_school_id_index` (`tokenable_id`,`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) NOT NULL,
  `guard_name` varchar(125) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_season_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_season_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `season_id` bigint(20) unsigned NOT NULL,
  `key` varchar(125) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_school_season_key` (`school_id`,`season_id`,`key`),
  KEY `school_season_settings_season_id_foreign` (`season_id`),
  CONSTRAINT `school_season_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `school_season_settings_season_id_foreign` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_users_school_id_user_id_unique` (`school_id`,`user_id`),
  KEY `school_users_user_id_foreign` (`user_id`),
  CONSTRAINT `school_users_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `current_season_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schools_current_season_id_foreign` (`current_season_id`),
  CONSTRAINT `schools_current_season_id_foreign` FOREIGN KEY (`current_season_id`) REFERENCES `seasons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `season_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `season_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` bigint(20) unsigned NOT NULL,
  `key` varchar(125) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `season_settings_season_id_key_unique` (`season_id`,`key`),
  CONSTRAINT `season_settings_season_id_foreign` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `season_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `season_snapshots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` bigint(20) unsigned NOT NULL,
  `snapshot_type` varchar(125) NOT NULL,
  `snapshot_data` longtext DEFAULT NULL,
  `snapshot_date` timestamp NULL DEFAULT NULL,
  `is_immutable` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `checksum` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `season_snapshots_created_by_foreign` (`created_by`),
  KEY `season_snapshots_season_id_snapshot_type_index` (`season_id`,`snapshot_type`),
  KEY `season_snapshots_is_immutable_snapshot_date_index` (`is_immutable`,`snapshot_date`),
  CONSTRAINT `season_snapshots_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `season_snapshots_season_id_foreign` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `seasons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `hour_start` time DEFAULT NULL,
  `hour_end` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `is_historical` tinyint(1) NOT NULL DEFAULT 0,
  `vacation_days` varchar(125) DEFAULT NULL,
  `school_id` bigint(20) unsigned NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_seasons_school_dates` (`school_id`,`start_date`,`end_date`),
  KEY `idx_seasons_current_active` (`school_id`,`is_current`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_season_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_season_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `season_id` bigint(20) unsigned NOT NULL,
  `role` varchar(125) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_season` (`user_id`,`season_id`),
  KEY `user_season_roles_user_id_index` (`user_id`),
  KEY `user_season_roles_season_id_index` (`season_id`),
  KEY `user_season_roles_assigned_by_foreign` (`assigned_by`),
  KEY `idx_user_season_active` (`user_id`,`is_active`),
  CONSTRAINT `user_season_roles_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) DEFAULT NULL,
  `email` varchar(125) NOT NULL,
  `password` varchar(125) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `v5_alert_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `v5_alert_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `alert_id` varchar(125) NOT NULL,
  `type` varchar(100) NOT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`data`)),
  `correlation_id` varchar(125) DEFAULT NULL,
  `resolved` tinyint(1) NOT NULL DEFAULT 0,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint(20) unsigned DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `email_sent` tinyint(1) NOT NULL DEFAULT 0,
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `notification_channels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`notification_channels`)),
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `season_id` bigint(20) unsigned DEFAULT NULL,
  `school_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `v5_alert_logs_alert_id_unique` (`alert_id`),
  KEY `v5_alert_logs_priority_resolved_created_at_index` (`priority`,`resolved`,`created_at`),
  KEY `v5_alert_logs_type_created_at_index` (`type`,`created_at`),
  KEY `v5_alert_logs_correlation_id_created_at_index` (`correlation_id`,`created_at`),
  KEY `v5_alert_logs_type_index` (`type`),
  KEY `v5_alert_logs_priority_index` (`priority`),
  KEY `v5_alert_logs_correlation_id_index` (`correlation_id`),
  KEY `v5_alert_logs_resolved_index` (`resolved`),
  KEY `v5_alert_logs_user_id_index` (`user_id`),
  KEY `v5_alert_logs_season_id_index` (`season_id`),
  KEY `v5_alert_logs_school_id_index` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `v5_booking_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `v5_booking_equipment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `equipment_type` enum('skis','boots','poles','helmet','goggles','snowboard','bindings','clothing','protection','other') NOT NULL,
  `name` varchar(100) NOT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `serial_number` varchar(50) DEFAULT NULL,
  `participant_name` varchar(200) NOT NULL,
  `participant_index` int(11) DEFAULT NULL,
  `daily_rate` decimal(8,2) NOT NULL,
  `rental_days` int(11) NOT NULL,
  `total_price` decimal(8,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'EUR',
  `deposit` decimal(8,2) DEFAULT NULL,
  `condition_out` enum('excellent','good','fair','poor','damaged') NOT NULL DEFAULT 'good',
  `condition_in` enum('excellent','good','fair','poor','damaged') DEFAULT NULL,
  `rented_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `equipment_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`equipment_data`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `v5_booking_equipment_booking_id_equipment_type_index` (`booking_id`,`equipment_type`),
  KEY `v5_booking_equipment_booking_id_participant_name_index` (`booking_id`,`participant_name`),
  KEY `v5_booking_equipment_equipment_type_rented_at_index` (`equipment_type`,`rented_at`),
  KEY `v5_booking_equipment_equipment_type_returned_at_index` (`equipment_type`,`returned_at`),
  KEY `v5_booking_equipment_rented_at_returned_at_index` (`rented_at`,`returned_at`),
  KEY `v5_booking_equipment_serial_number_rented_at_index` (`serial_number`,`rented_at`),
  KEY `v5_booking_equipment_equipment_type_index` (`equipment_type`),
  KEY `v5_booking_equipment_serial_number_index` (`serial_number`),
  KEY `v5_booking_equipment_participant_index_index` (`participant_index`),
  KEY `v5_booking_equipment_rented_at_index` (`rented_at`),
  KEY `v5_booking_equipment_returned_at_index` (`returned_at`),
  CONSTRAINT `v5_booking_equipment_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `v5_bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `v5_booking_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `v5_booking_extras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `extra_type` enum('insurance','equipment','transport','meal','photo','video','certificate','special_service','other') NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `unit_price` decimal(8,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_price` decimal(8,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'EUR',
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`extra_data`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `v5_booking_extras_booking_id_extra_type_index` (`booking_id`,`extra_type`),
  KEY `v5_booking_extras_booking_id_is_active_index` (`booking_id`,`is_active`),
  KEY `v5_booking_extras_extra_type_is_active_index` (`extra_type`,`is_active`),
  KEY `v5_booking_extras_extra_type_index` (`extra_type`),
  KEY `v5_booking_extras_is_required_index` (`is_required`),
  KEY `v5_booking_extras_is_active_index` (`is_active`),
  CONSTRAINT `v5_booking_extras_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `v5_bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `v5_booking_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `v5_booking_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `payment_reference` varchar(50) NOT NULL,
  `payment_type` enum('deposit','full_payment','partial_payment','refund','fee') NOT NULL,
  `payment_method` enum('credit_card','debit_card','bank_transfer','paypal','apple_pay','google_pay','cash','voucher','other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'EUR',
  `status` enum('pending','processing','completed','failed','cancelled','refunded','partially_refunded') NOT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `gateway_transaction_id` varchar(125) DEFAULT NULL,
  `gateway_reference` varchar(125) DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `fee_amount` decimal(8,2) DEFAULT NULL,
  `fee_currency` varchar(3) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `refunded_amount` decimal(10,2) DEFAULT NULL,
  `refund_reason` text DEFAULT NULL,
  `payment_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`payment_data`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `v5_booking_payments_payment_reference_unique` (`payment_reference`),
  KEY `v5_booking_payments_booking_id_status_index` (`booking_id`,`status`),
  KEY `v5_booking_payments_booking_id_payment_type_index` (`booking_id`,`payment_type`),
  KEY `v5_booking_payments_payment_method_status_index` (`payment_method`,`status`),
  KEY `v5_booking_payments_gateway_gateway_transaction_id_index` (`gateway`,`gateway_transaction_id`),
  KEY `v5_booking_payments_status_created_at_index` (`status`,`created_at`),
  KEY `v5_booking_payments_processed_at_status_index` (`processed_at`,`status`),
  KEY `v5_booking_payments_refunded_at_refunded_amount_index` (`refunded_at`,`refunded_amount`),
  KEY `v5_booking_payments_status_processed_at_amount_index` (`status`,`processed_at`,`amount`),
  KEY `v5_booking_payments_payment_method_processed_at_amount_index` (`payment_method`,`processed_at`,`amount`),
  KEY `v5_booking_payments_payment_type_index` (`payment_type`),
  KEY `v5_booking_payments_payment_method_index` (`payment_method`),
  KEY `v5_booking_payments_status_index` (`status`),
  KEY `v5_booking_payments_gateway_index` (`gateway`),
  KEY `v5_booking_payments_gateway_transaction_id_index` (`gateway_transaction_id`),
  KEY `v5_booking_payments_processed_at_index` (`processed_at`),
  KEY `v5_booking_payments_refunded_at_index` (`refunded_at`),
  CONSTRAINT `v5_booking_payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `v5_bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `v5_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `v5_bookings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(50) NOT NULL,
  `season_id` bigint(20) unsigned NOT NULL,
  `school_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `monitor_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('course','activity','material') NOT NULL,
  `status` enum('pending','confirmed','paid','completed','cancelled','no_show') NOT NULL,
  `booking_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`booking_data`)),
  `participants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL CHECK (json_valid(`participants`)),
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extras_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `equipment_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `insurance_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'EUR',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `meeting_point` varchar(125) DEFAULT NULL,
  `has_insurance` tinyint(1) NOT NULL DEFAULT 0,
  `has_equipment` tinyint(1) NOT NULL DEFAULT 0,
  `special_requests` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`metadata`)),
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` varchar(125) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `v5_bookings_booking_reference_unique` (`booking_reference`),
  KEY `v5_bookings_season_id_school_id_status_index` (`season_id`,`school_id`,`status`),
  KEY `v5_bookings_season_id_school_id_type_index` (`season_id`,`school_id`,`type`),
  KEY `v5_bookings_season_id_school_id_start_date_index` (`season_id`,`school_id`,`start_date`),
  KEY `v5_bookings_client_id_status_index` (`client_id`,`status`),
  KEY `v5_bookings_course_id_start_date_start_time_index` (`course_id`,`start_date`,`start_time`),
  KEY `v5_bookings_monitor_id_start_date_start_time_index` (`monitor_id`,`start_date`,`start_time`),
  KEY `v5_bookings_status_start_date_index` (`status`,`start_date`),
  KEY `v5_bookings_created_at_status_index` (`created_at`,`status`),
  KEY `v5_bookings_season_id_index` (`season_id`),
  KEY `v5_bookings_school_id_index` (`school_id`),
  KEY `v5_bookings_client_id_index` (`client_id`),
  KEY `v5_bookings_course_id_index` (`course_id`),
  KEY `v5_bookings_monitor_id_index` (`monitor_id`),
  KEY `v5_bookings_type_index` (`type`),
  KEY `v5_bookings_status_index` (`status`),
  KEY `v5_bookings_total_price_index` (`total_price`),
  KEY `v5_bookings_start_date_index` (`start_date`),
  KEY `v5_bookings_end_date_index` (`end_date`),
  KEY `v5_bookings_has_insurance_index` (`has_insurance`),
  KEY `v5_bookings_has_equipment_index` (`has_equipment`),
  FULLTEXT KEY `bookings_text_search` (`special_requests`,`notes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `v5_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `v5_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `correlation_id` varchar(125) DEFAULT NULL,
  `level` varchar(20) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `operation` varchar(125) DEFAULT NULL,
  `message` text NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`context`)),
  `extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL CHECK (json_valid(`extra`)),
  `request_method` varchar(10) DEFAULT NULL,
  `request_url` text DEFAULT NULL,
  `user_ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `season_id` bigint(20) unsigned DEFAULT NULL,
  `school_id` bigint(20) unsigned DEFAULT NULL,
  `memory_usage_mb` double(8,2) DEFAULT NULL,
  `memory_peak_mb` double(8,2) DEFAULT NULL,
  `response_time_ms` double(8,2) DEFAULT NULL,
  `server_name` varchar(125) DEFAULT NULL,
  `environment` varchar(20) DEFAULT NULL,
  `application_version` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `v5_logs_level_created_at_index` (`level`,`created_at`),
  KEY `v5_logs_category_created_at_index` (`category`,`created_at`),
  KEY `v5_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `v5_logs_correlation_id_created_at_index` (`correlation_id`,`created_at`),
  KEY `v5_logs_correlation_id_index` (`correlation_id`),
  KEY `v5_logs_level_index` (`level`),
  KEY `v5_logs_category_index` (`category`),
  KEY `v5_logs_user_id_index` (`user_id`),
  KEY `v5_logs_season_id_index` (`season_id`),
  KEY `v5_logs_school_id_index` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


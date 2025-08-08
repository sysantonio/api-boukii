/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `api_key_access_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_key_access_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `api_key_id` int(10) unsigned NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `api_key_access_events_ip_address_index` (`ip_address`),
  KEY `api_key_access_events_api_key_id_foreign` (`api_key_id`),
  CONSTRAINT `api_key_access_events_api_key_id_foreign` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `api_key_admin_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_key_admin_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `api_key_id` int(10) unsigned NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `api_key_admin_events_ip_address_index` (`ip_address`),
  KEY `api_key_admin_events_event_index` (`event`),
  KEY `api_key_admin_events_api_key_id_foreign` (`api_key_id`),
  CONSTRAINT `api_key_admin_events_api_key_id_foreign` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `api_keys_name_index` (`name`),
  KEY `api_keys_key_index` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) NOT NULL,
  `action` varchar(100) NOT NULL DEFAULT 'updated',
  `description` text,
  `user_id` bigint(20) DEFAULT NULL,
  `before_change` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bookings_school_idx` (`booking_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `booking_logs_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_payment_notice_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_payment_notice_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) NOT NULL,
  `booking_user_id` bigint(20) NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `booking_user_id` (`booking_user_id`),
  CONSTRAINT `booking_payment_notice_log_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `booking_payment_notice_log_ibfk_2` FOREIGN KEY (`booking_user_id`) REFERENCES `booking_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_user_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_user_extras` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `booking_user_id` bigint(20) NOT NULL,
  `course_extra_id` bigint(20) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bue_booking_user_idx` (`booking_user_id`),
  KEY `course_extra_id` (`course_extra_id`),
  CONSTRAINT `booking_user_extras_ibfk_1` FOREIGN KEY (`booking_user_id`) REFERENCES `booking_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_user_extras_ibfk_2` FOREIGN KEY (`course_extra_id`) REFERENCES `course_extras` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'CHF',
  `course_subgroup_id` bigint(20) DEFAULT NULL,
  `course_id` bigint(20) DEFAULT NULL,
  `course_date_id` bigint(20) NOT NULL,
  `degree_id` bigint(20) DEFAULT NULL,
  `course_group_id` bigint(20) DEFAULT NULL,
  `monitor_id` bigint(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `hour_start` time DEFAULT NULL,
  `hour_end` time DEFAULT NULL,
  `attended` tinyint(1) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `accepted` tinyint(1) NOT NULL DEFAULT '1',
  `group_changed` tinyint(1) NOT NULL DEFAULT '1',
  `color` varchar(45) DEFAULT NULL,
  `notes_school` text,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bu_booking_idx` (`booking_id`),
  KEY `fk_bu_user_main_idx` (`client_id`),
  KEY `fk_bu2_subgroup_idx` (`course_subgroup_id`),
  KEY `fk_bu_course_idx` (`course_id`),
  KEY `fk_bu_monitor_idx` (`monitor_id`),
  KEY `course_date_id` (`course_date_id`),
  KEY `degree_id` (`degree_id`),
  KEY `course_group_id` (`course_group_id`),
  KEY `booking_users_ibfk_8_idx` (`school_id`),
  KEY `idx_booking_users_analytics` (`school_id`,`status`,`date`,`course_id`,`booking_id`,`client_id`),
  KEY `idx_booking_users_date_range` (`date`,`school_id`,`status`),
  KEY `idx_booking_users_client_date_status` (`client_id`,`date`,`status`),
  KEY `idx_booking_users_course_monitor` (`course_id`,`monitor_id`,`status`),
  CONSTRAINT `booking_users_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_users_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `booking_users_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  CONSTRAINT `booking_users_ibfk_4` FOREIGN KEY (`course_date_id`) REFERENCES `course_dates` (`id`),
  CONSTRAINT `booking_users_ibfk_5` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`),
  CONSTRAINT `booking_users_ibfk_6` FOREIGN KEY (`course_group_id`) REFERENCES `course_groups` (`id`),
  CONSTRAINT `booking_users_ibfk_7` FOREIGN KEY (`monitor_id`) REFERENCES `monitors` (`id`),
  CONSTRAINT `booking_users_ibfk_8` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) NOT NULL,
  `client_main_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `price_total` decimal(8,2) NOT NULL,
  `has_cancellation_insurance` tinyint(1) NOT NULL DEFAULT '0',
  `price_cancellation_insurance` decimal(8,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) NOT NULL DEFAULT 'CHF',
  `payment_method_id` bigint(20) DEFAULT NULL,
  `paid_total` decimal(8,2) NOT NULL DEFAULT '0.00',
  `paid` tinyint(1) DEFAULT '0',
  `payrexx_reference` text,
  `payrexx_transaction` text,
  `attendance` tinyint(1) NOT NULL DEFAULT '1',
  `payrexx_refund` tinyint(4) NOT NULL DEFAULT '0',
  `notes` text,
  `notes_school` text,
  `paxes` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `color` varchar(45) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `has_boukii_care` tinyint(1) DEFAULT '0',
  `price_boukii_care` decimal(8,2) DEFAULT '0.00',
  `has_tva` tinyint(1) DEFAULT '0',
  `price_tva` decimal(8,2) DEFAULT '0.00',
  `has_reduction` tinyint(1) DEFAULT '0',
  `price_reduction` decimal(8,2) DEFAULT '0.00',
  `basket` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bookings_school_idx` (`school_id`),
  KEY `fk_bookings_client_main_idx` (`client_main_id`),
  KEY `fk_bookings_payment_idx` (`payment_method_id`),
  KEY `bookings_ibfk_3_idx` (`user_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`client_main_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `client_observations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_observations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `general` varchar(5000) DEFAULT '',
  `notes` varchar(5000) DEFAULT '',
  `historical` varchar(5000) DEFAULT '',
  `client_id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `client_observations_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `client_observations_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `birth_date` date NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `telephone` varchar(255) DEFAULT '',
  `address` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `cp` varchar(100) DEFAULT NULL,
  `city` text,
  `province` int(11) DEFAULT NULL,
  `country` int(11) DEFAULT NULL,
  `language1_id` bigint(20) DEFAULT NULL,
  `language2_id` bigint(20) DEFAULT NULL,
  `language3_id` bigint(20) DEFAULT NULL,
  `language6_id` bigint(20) DEFAULT NULL,
  `language5_id` bigint(20) DEFAULT NULL,
  `language4_id` bigint(20) DEFAULT NULL,
  `image` longtext,
  `user_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `image` (`image`(3072)),
  KEY `language2_id` (`language2_id`),
  KEY `language3_id` (`language3_id`),
  KEY `users_ibfk_6` (`language1_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`language1_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `clients_ibfk_2` FOREIGN KEY (`language2_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `clients_ibfk_3` FOREIGN KEY (`language3_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `clients_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients_schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients_schools` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `clients_schools_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `clients_schools_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients_sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients_sports` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL DEFAULT '1',
  `sport_id` bigint(20) NOT NULL,
  `degree_id` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `sport_id` (`sport_id`),
  KEY `clients_sports_ibfk_3_idx` (`degree_id`),
  KEY `clients_sports_ibfk_4_idx` (`school_id`),
  CONSTRAINT `clients_sports_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `clients_sports_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`),
  CONSTRAINT `clients_sports_ibfk_3` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `clients_sports_ibfk_4` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients_utilizers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients_utilizers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `main_id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `main_id` (`main_id`),
  CONSTRAINT `clients_utilizers_ibfk_1` FOREIGN KEY (`main_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `clients_utilizers_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_dates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `hour_start` time NOT NULL,
  `hour_end` time NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'avoids login',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cd_course2_idx` (`course_id`),
  CONSTRAINT `course_dates_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_extras` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ce_course_idx` (`course_id`),
  CONSTRAINT `course_extras_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_groups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) NOT NULL,
  `course_date_id` bigint(20) NOT NULL,
  `degree_id` bigint(20) NOT NULL,
  `age_min` int(11) DEFAULT '1',
  `age_max` int(11) DEFAULT '99',
  `recommended_age` int(11) DEFAULT '1',
  `teachers_min` int(11) DEFAULT '1',
  `teachers_max` int(11) DEFAULT '1',
  `observations` text,
  `teacher_min_degree` bigint(20) DEFAULT NULL,
  `auto` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cg_course_idx` (`course_id`),
  KEY `fk_cg_course_date_idx` (`course_date_id`),
  KEY `fk_cg_degree_idx` (`degree_id`),
  KEY `fk_cg_teacher_degree_idx` (`teacher_min_degree`),
  CONSTRAINT `course_groups_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_groups_ibfk_2` FOREIGN KEY (`course_date_id`) REFERENCES `course_dates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_groups_ibfk_3` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`),
  CONSTRAINT `course_groups_ibfk_4` FOREIGN KEY (`teacher_min_degree`) REFERENCES `degrees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_subgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_subgroups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) NOT NULL,
  `course_date_id` bigint(20) NOT NULL,
  `degree_id` bigint(20) NOT NULL,
  `course_group_id` bigint(20) NOT NULL,
  `monitor_id` bigint(20) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cgs_group_idx` (`course_group_id`),
  KEY `fk_cgs_course_idx` (`course_id`),
  KEY `fk_cgs_course_date_idx` (`course_date_id`),
  KEY `fk_cgs_degree_idx` (`degree_id`),
  KEY `fk_cgs_monitor_idx` (`monitor_id`),
  CONSTRAINT `course_subgroups_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `course_subgroups_ibfk_2` FOREIGN KEY (`course_date_id`) REFERENCES `course_dates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_subgroups_ibfk_3` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`),
  CONSTRAINT `course_subgroups_ibfk_4` FOREIGN KEY (`course_group_id`) REFERENCES `course_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_subgroups_ibfk_5` FOREIGN KEY (`monitor_id`) REFERENCES `monitors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `course_type` tinyint(4) NOT NULL,
  `is_flexible` tinyint(1) NOT NULL,
  `sport_id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `station_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `name` text CHARACTER SET utf8mb4 NOT NULL,
  `short_description` text CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NOT NULL,
  `price` decimal(8,2) NOT NULL COMMENT 'If duration_flexible, per 15min',
  `currency` varchar(3) NOT NULL DEFAULT 'CHF',
  `max_participants` int(11) NOT NULL DEFAULT '1',
  `duration` varchar(255) DEFAULT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `date_start_res` date DEFAULT NULL,
  `date_end_res` date DEFAULT NULL,
  `hour_min` varchar(255) DEFAULT NULL,
  `hour_max` varchar(255) DEFAULT NULL,
  `age_min` int(11) DEFAULT '1',
  `age_max` int(11) DEFAULT '99',
  `confirm_attendance` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `online` tinyint(1) NOT NULL DEFAULT '0',
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `options` tinyint(1) NOT NULL DEFAULT '0',
  `highlighted` tinyint(1) NOT NULL DEFAULT '0',
  `claim_text` varchar(255) DEFAULT NULL,
  `image` longtext,
  `translations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `price_range` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `discounts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_courses2_type_idx` (`course_type`),
  KEY `fk_courses2_sport_idx` (`sport_id`),
  KEY `fk_courses2_school_idx` (`school_id`),
  KEY `fk_courses_station` (`station_id`),
  KEY `courses_ibfk_4_idx` (`user_id`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`),
  CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  CONSTRAINT `courses_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `degrees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `degrees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `league` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `annotation` text COMMENT 'null for unused at this school',
  `degree_order` int(11) NOT NULL,
  `progress` int(11) NOT NULL,
  `color` varchar(10) NOT NULL,
  `image` longtext,
  `age_min` int(11) DEFAULT '1',
  `age_max` int(11) DEFAULT '99',
  `active` tinyint(1) DEFAULT '1',
  `school_id` bigint(20) DEFAULT NULL COMMENT 'null for default list',
  `sport_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `degrees_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `degrees_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `degrees_school_sport_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `degrees_school_sport_goals` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `degree_id` bigint(10) NOT NULL,
  `name` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_dsso_dss` (`degree_id`),
  CONSTRAINT `degrees_school_sport_goals_ibfk_1` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `discounts_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discounts_codes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `quantity` double(15,2) DEFAULT NULL,
  `percentage` double(15,2) DEFAULT NULL,
  `school_id` bigint(20) NOT NULL,
  `total` int(11) DEFAULT NULL,
  `remaining` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `discounts_codes_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) NOT NULL,
  `date` datetime NOT NULL,
  `from` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `to` text CHARACTER SET latin1,
  `cc` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `bcc` varchar(10000) CHARACTER SET latin1 DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `headers` text CHARACTER SET latin1,
  `attachments` longtext CHARACTER SET latin1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_fk1_school_idx` (`school_id`),
  CONSTRAINT `email_fk1_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evaluation_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `evaluation_id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `file` longtext,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluations_files_fk1_idx` (`evaluation_id`),
  CONSTRAINT `evaluations_files_fk1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evaluation_fulfilled_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_fulfilled_goals` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `evaluation_id` bigint(20) NOT NULL,
  `degrees_school_sport_goals_id` bigint(10) NOT NULL,
  `score` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluation_id` (`evaluation_id`),
  KEY `degrees_school_sport_goals_id` (`degrees_school_sport_goals_id`),
  CONSTRAINT `evaluation_fulfilled_goals_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`),
  CONSTRAINT `evaluation_fulfilled_goals_ibfk_2` FOREIGN KEY (`degrees_school_sport_goals_id`) REFERENCES `degrees_school_sport_goals` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) NOT NULL,
  `degree_id` bigint(20) NOT NULL,
  `observations` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `degree_id` (`degree_id`),
  CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mails` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'booking_confirm',
  `lang` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `subject` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` text COLLATE utf8mb4_unicode_ci,
  `body` text COLLATE utf8mb4_unicode_ci,
  `school_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mails_school_id_fk_idx` (`school_id`),
  CONSTRAINT `mails_school_id_fk` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitor_nwd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_nwd` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `monitor_id` bigint(20) NOT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `station_id` bigint(20) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `full_day` tinyint(1) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `description` text,
  `color` varchar(45) DEFAULT NULL,
  `user_nwd_subtype_id` tinyint(4) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `monitor_id` (`monitor_id`),
  KEY `fk_user_nwd_subtype` (`user_nwd_subtype_id`),
  KEY `fk_user_nwd_school` (`school_id`),
  KEY `fk_nwd_station` (`station_id`),
  CONSTRAINT `monitor_nwd_ibfk_1` FOREIGN KEY (`monitor_id`) REFERENCES `monitors` (`id`),
  CONSTRAINT `monitor_nwd_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `monitor_nwd_ibfk_3` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitor_observations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_observations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `general` varchar(5000) NOT NULL DEFAULT '',
  `notes` varchar(5000) NOT NULL DEFAULT '',
  `historical` varchar(5000) NOT NULL DEFAULT '',
  `monitor_id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `monitor_id` (`monitor_id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `monitor_observations_ibfk_1` FOREIGN KEY (`monitor_id`) REFERENCES `monitors` (`id`),
  CONSTRAINT `monitor_observations_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitor_sport_authorized_degrees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_sport_authorized_degrees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `monitor_sport_id` bigint(20) NOT NULL,
  `degree_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `monitor_sport_id` (`monitor_sport_id`),
  KEY `degree_id` (`degree_id`),
  CONSTRAINT `monitor_sport_authorized_degrees_ibfk_1` FOREIGN KEY (`monitor_sport_id`) REFERENCES `monitor_sports_degrees` (`id`),
  CONSTRAINT `monitor_sport_authorized_degrees_ibfk_2` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitor_sports_degrees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_sports_degrees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sport_id` bigint(20) NOT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `degree_id` bigint(20) NOT NULL,
  `monitor_id` bigint(20) NOT NULL,
  `salary_level` bigint(20) DEFAULT NULL,
  `allow_adults` int(10) DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sport_id` (`sport_id`) USING BTREE,
  KEY `degree_id` (`degree_id`) USING BTREE,
  KEY `monitor_id` (`monitor_id`) USING BTREE,
  KEY `user_sports_school` (`school_id`) USING BTREE,
  KEY `monitor_sports_degrees_ibfk_5_idx` (`salary_level`),
  CONSTRAINT `monitor_sports_degrees_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`),
  CONSTRAINT `monitor_sports_degrees_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `monitor_sports_degrees_ibfk_3` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`),
  CONSTRAINT `monitor_sports_degrees_ibfk_4` FOREIGN KEY (`monitor_id`) REFERENCES `monitors` (`id`),
  CONSTRAINT `monitor_sports_degrees_ibfk_5` FOREIGN KEY (`salary_level`) REFERENCES `school_salary_levels` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitors` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `birth_date` date NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT '',
  `address` varchar(255) DEFAULT NULL,
  `cp` varchar(100) DEFAULT NULL,
  `city` text,
  `province` int(11) DEFAULT NULL,
  `country` int(11) DEFAULT NULL,
  `world_country` int(11) DEFAULT NULL,
  `language1_id` bigint(20) DEFAULT NULL,
  `language2_id` bigint(20) DEFAULT NULL,
  `language3_id` bigint(20) DEFAULT NULL,
  `language6_id` bigint(20) DEFAULT NULL,
  `language5_id` bigint(20) DEFAULT NULL,
  `language4_id` bigint(20) DEFAULT NULL,
  `image` longtext,
  `avs` varchar(255) DEFAULT '',
  `work_license` varchar(255) DEFAULT '',
  `bank_details` varchar(255) DEFAULT '',
  `children` tinyint(11) DEFAULT '0',
  `civil_status` varchar(255) DEFAULT '0',
  `family_allowance` tinyint(1) DEFAULT '0',
  `partner_work_license` varchar(255) DEFAULT '',
  `partner_works` tinyint(1) DEFAULT '0',
  `partner_percentaje` int(11) DEFAULT '0',
  `user_id` bigint(20) DEFAULT NULL,
  `active_school` bigint(20) DEFAULT NULL,
  `active_station` bigint(20) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `image` (`image`(3072)),
  KEY `language2_id` (`language2_id`),
  KEY `language3_id` (`language3_id`),
  KEY `users_ibfk_6` (`language1_id`),
  KEY `user_id` (`user_id`),
  KEY `active_school` (`active_school`),
  KEY `monitors_ibfk_6_idx` (`active_station`),
  CONSTRAINT `monitors_ibfk_1` FOREIGN KEY (`language1_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `monitors_ibfk_2` FOREIGN KEY (`language2_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `monitors_ibfk_3` FOREIGN KEY (`language3_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `monitors_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `monitors_ibfk_5` FOREIGN KEY (`active_school`) REFERENCES `schools` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `monitors_ibfk_6` FOREIGN KEY (`active_station`) REFERENCES `stations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitors_schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitors_schools` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `monitor_id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `station_id` bigint(20) DEFAULT NULL,
  `active_school` tinyint(4) NOT NULL DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `monitor_id` (`monitor_id`),
  KEY `school_id` (`school_id`),
  KEY `fk_us_station` (`station_id`),
  CONSTRAINT `monitors_schools_ibfk_1` FOREIGN KEY (`monitor_id`) REFERENCES `monitors` (`id`),
  CONSTRAINT `monitors_schools_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `monitors_schools_ibfk_3` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pagado',
  `notes` text,
  `payrexx_reference` text,
  `payrexx_transaction` text,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_fk1_idx` (`booking_id`),
  KEY `payments_fk2_idx` (`school_id`),
  CONSTRAINT `payments_fk1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `payments_fk2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_colors` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `color` varchar(45) DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `school_colors_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Revisar si el nombre es adecuado';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_salary_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_salary_levels` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `pay` float(8,2) NOT NULL DEFAULT '0.00',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `school_salary_levels_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_sports` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) NOT NULL,
  `sport_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `school_sports_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `school_users_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `school_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schools` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(100) NOT NULL,
  `contact_email` text,
  `contact_phone` text,
  `contact_telephone` text,
  `contact_address` text,
  `contact_cp` text,
  `contact_city` text,
  `contact_province` varchar(100) DEFAULT NULL,
  `contact_country` varchar(100) DEFAULT NULL,
  `fiscal_name` varchar(100) DEFAULT '',
  `fiscal_id` varchar(100) DEFAULT '',
  `fiscal_address` varchar(100) DEFAULT '',
  `fiscal_cp` varchar(100) DEFAULT '',
  `fiscal_city` varchar(100) DEFAULT '',
  `fiscal_province` varchar(100) DEFAULT NULL,
  `fiscal_country` varchar(100) DEFAULT NULL,
  `iban` varchar(100) DEFAULT '',
  `logo` varchar(500) DEFAULT '',
  `slug` varchar(100) DEFAULT NULL,
  `cancellation_insurance_percent` decimal(5,2) DEFAULT '10.00',
  `payrexx_instance` text,
  `payrexx_key` text,
  `conditions_url` varchar(100) DEFAULT '',
  `bookings_comission_cash` decimal(8,2) DEFAULT '5.00',
  `bookings_comission_boukii_pay` decimal(8,2) DEFAULT '5.00',
  `bookings_comission_other` decimal(8,2) DEFAULT '5.00',
  `school_rate` double(8,2) DEFAULT '0.00',
  `has_ski` tinyint(1) NOT NULL DEFAULT '0',
  `has_snowboard` tinyint(1) NOT NULL DEFAULT '0',
  `has_telemark` tinyint(1) NOT NULL DEFAULT '0',
  `has_rando` tinyint(1) NOT NULL DEFAULT '0',
  `inscription` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(100) DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_rate_id` (`school_rate`),
  KEY `type_id` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seasons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `hour_start` time DEFAULT NULL,
  `hour_end` time DEFAULT NULL,
  `vacation_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `season_school_id_foreign` (`school_id`),
  KEY `idx_seasons_school_dates` (`school_id`,`start_date`,`end_date`),
  CONSTRAINT `seasons_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_type` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sport_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sport_types` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sports` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon_prive` varchar(500) DEFAULT NULL,
  `icon_collective` varchar(500) DEFAULT NULL,
  `icon_activity` varchar(500) DEFAULT NULL,
  `icon_selected` varchar(500) DEFAULT NULL,
  `icon_unselected` varchar(500) DEFAULT NULL,
  `sport_type` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sport_type` (`sport_type`),
  CONSTRAINT `sports_ibfk_1` FOREIGN KEY (`sport_type`) REFERENCES `sport_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `station_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `station_service` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `station_id` bigint(20) NOT NULL,
  `service_type_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(100) DEFAULT '',
  `telephone` varchar(100) DEFAULT '',
  `email` varchar(100) DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `station_id` (`station_id`),
  KEY `service_type_id` (`service_type_id`),
  CONSTRAINT `station_service_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  CONSTRAINT `station_service_ibfk_2` FOREIGN KEY (`service_type_id`) REFERENCES `service_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `cp` text,
  `city` text,
  `country` text NOT NULL,
  `province` text NOT NULL,
  `address` varchar(100) NOT NULL,
  `image` varchar(500) NOT NULL,
  `map` varchar(500) NOT NULL DEFAULT '',
  `latitude` varchar(100) NOT NULL,
  `longitude` varchar(100) NOT NULL,
  `num_hanger` int(11) NOT NULL DEFAULT '0',
  `num_chairlift` int(11) NOT NULL DEFAULT '0',
  `num_cabin` int(11) NOT NULL DEFAULT '0',
  `num_cabin_large` int(11) NOT NULL DEFAULT '0',
  `num_fonicular` int(11) NOT NULL DEFAULT '0',
  `show_details` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `accuweather` text,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stations_schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stations_schools` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `station_id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `station_id` (`station_id`),
  CONSTRAINT `stations_schools_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  CONSTRAINT `stations_schools_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_checks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `text` varchar(200) NOT NULL,
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `task_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  CONSTRAINT `task_checks_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `image` longtext,
  `type` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'avoids login',
  `recover_token` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  `logout` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `image` (`image`(3072))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vouchers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `quantity` double(15,2) NOT NULL,
  `remaining_balance` double(15,2) NOT NULL,
  `payed` tinyint(1) NOT NULL DEFAULT '0',
  `client_id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `payrexx_reference` text,
  `payrexx_transaction` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  `is_gift` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `vouchers_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vouchers_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vouchers_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `voucher_id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `status` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `old_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `voucher_id` (`voucher_id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `vouchers_log_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`),
  CONSTRAINT `vouchers_log_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2023_12_05_210229_add_old_id_to_all_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2024_01_05_210229_payments_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2024_09_06_140402_add_ip_to_activity_log_table',4);

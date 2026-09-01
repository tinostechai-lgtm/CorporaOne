-- Create call_logs table for Bonvoice/IVR
-- Run this SQL on your MySQL database (corporaone)

CREATE TABLE IF NOT EXISTS `call_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `call_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direction` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leg` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `duration` int NOT NULL DEFAULT '0',
  `recording_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dtmf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `call_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `call_logs_created_by_index` (`created_by`),
  KEY `call_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


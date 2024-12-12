<?php 
namespace aw2\db_delta;

function create_awesome_tables() {
		global $wpdb;

		$collate      = $wpdb->get_charset_collate();
		$table_schema = [

			"CREATE TABLE IF NOT EXISTS `awesome_exceptions` (
				  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
				  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
				  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
				  `exception_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
				  `post_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `module` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `app_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `sc` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `position` int(11) DEFAULT NULL,
				  `link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `user` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `header_data` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `request_data` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `sql_query` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `request_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `errno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `errfile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `errline` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `call_stack` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `trace` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `func` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `class` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `method` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `no_of_times` int(11) DEFAULT NULL,
				  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
				  PRIMARY KEY (`ID`),
				  KEY `status` (`status`),
				  KEY `app_name` (`app_name`),
				  KEY `exception_type` (`exception_type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			",

			"CREATE TABLE IF NOT EXISTS `datatype_mismatch` (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
				  `app_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
				  `module_slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
				  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `post_type` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
				  `template_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
				  `sc` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
				  `position` int(10) DEFAULT NULL,
				  `request_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `conditional` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `php7_result` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `lhs_value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `lhs_datatype` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `rhs_value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `rhs_datatype` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `invalid_lhs_dt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `invalid_rhs_dt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `invalid_match` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `link` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `extras` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  PRIMARY KEY (`id`),
				  KEY `invalid_lhs_dt` (`invalid_lhs_dt`),
				  KEY `invalid_rhs_dt` (`invalid_rhs_dt`),
				  KEY `module_slug` (`module_slug`),
				  KEY `template_name` (`template_name`),
				  KEY `conditional` (`conditional`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;",
			
			"CREATE TABLE `notification_log` (
				`ID` bigint(20) NOT NULL AUTO_INCREMENT,
				`timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
				`notification_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`notification_provider` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`notification_to` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`cc` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`bcc` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`notification_from` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`reply_to` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`subject` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
				`object_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`object_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`tracking_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`tracking_status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`tracking_stage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`tracking_set` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				PRIMARY KEY (`ID`),
				KEY `message_to` (`notification_to`),
				KEY `message_from` (`notification_from`),
				KEY `message_type` (`notification_type`),
				KEY `object_id` (`object_id`),
				KEY `tracking_id` (`tracking_id`),
				KEY `subject` (`subject`),
				KEY `tracking_set` (`tracking_set`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
			
			"CREATE TABLE `usage_log` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `stamp` timestamp NOT NULL DEFAULT current_timestamp(),
			  `post_type` text DEFAULT NULL,
			  `module_slug` text DEFAULT NULL,
			  `service` tinyint(4) NOT NULL DEFAULT 0,
			  `count` bigint(20) NOT NULL DEFAULT 1,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

		
		];

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $table_schema as $table ) {
			dbDelta( $table );
		}
	}


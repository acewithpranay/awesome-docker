<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class Tables {

	private static $instance = null;
	private static $smack_csv_instance = null;

	public static function getInstance() {
		if (Tables::$instance == null) {
			Tables::$instance = new Tables;
			Tables::$smack_csv_instance = SmackCSV::getInstance();
			Tables::$instance->create_tables();
			return Tables::$instance;
		}
		return Tables::$instance;
	}

	public function create_tables(){
		global $wpdb;
		$file_table_name = $wpdb->prefix ."smackcsv_file_events";
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS $file_table_name (
			`id` int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`file_name` VARCHAR(255) NOT NULL,
			`status` VARCHAR(255) NOT NULL,
			`mode` VARCHAR(255) NOT NULL,
			`hash_key` VARCHAR(255) NOT NULL,
			`templatekey` VARCHAR(32),
			`total_rows` INT(255) NOT NULL,
			`lock` BOOLEAN DEFAULT false,
			`progress` INT(6)) ENGINE=InnoDB" 
				);

		$image_table =  $wpdb->prefix ."ultimate_csv_importer_media";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $image_table (
			`post_id` INT(6),
			`attach_id` INT(6) NOT NULL,
			`image_url` blob NOT NULL,
			`hash_key` VARCHAR(255) NOT NULL,
			`templatekey` VARCHAR(32),
			`status` VARCHAR(255) DEFAULT 'pending',
			`module` VARCHAR(255) DEFAULT NULL,
			`image_type` VARCHAR(255) DEFAULT NULL
				) ENGINE=InnoDB"
				);

		$post_entries_table = $wpdb->prefix ."ultimate_post_entries";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $post_entries_table (
					`ID` INT(6),
					`file_name` varchar(255) DEFAULT NULL,
					`type` varchar(255) DEFAULT NULL,
					`revision` INT(6),
					`status` varchar(255) DEFAULT NULL
					) ENGINE=InnoDB"
					);
				
		$shortcode_table_name =  $wpdb->prefix ."ultimate_csv_importer_shortcode_manager";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $shortcode_table_name (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`post_id` INT(6),
			`post_title` VARCHAR(255) DEFAULT NULL,
			`image_shortcode` VARCHAR(255) NOT NULL,
			`media_id` INT(6) DEFAULT NULL,
			`original_image` TEXT NOT NULL,
			`indexs` INT(10) DEFAULT NULL,
			`status` VARCHAR(255) DEFAULT 'failed',
			`image_meta` TEXT DEFAULT NULL,
			`hash_key` VARCHAR(255) DEFAULT NULL,
			`import_type` VARCHAR(10) DEFAULT NULL,
			`file_name` VARCHAR(100) ,
			PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
				);

		$template_table_name = $wpdb->prefix ."ultimate_csv_importer_mappingtemplate";
		$wpdb->query( "CREATE TABLE IF NOT EXISTS $template_table_name (
			`id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`templatename` varchar(250) NOT NULL,
			`mapping` blob NOT NULL,
			`createdtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`deleted` int(1) DEFAULT '0',
			`templateused` int(10) DEFAULT '0',
			`mapping_type` varchar(30),
			`module` varchar(50) DEFAULT NULL,
			`csvname` varchar(250) DEFAULT NULL,
			`eventKey` varchar(60) DEFAULT NULL				
				) ENGINE = InnoDB "
				);  

		$log_table_name = $wpdb->prefix ."import_detail_log";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $log_table_name (
			`id` int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`file_name` VARCHAR(255) NOT NULL,
			`status` VARCHAR(255) NOT NULL,
			`hash_key` VARCHAR(255) NOT NULL,
			`templatekey` VARCHAR(32),
			`total_records` INT(6),
			`processing_records` INT(6) default 0,
			`remaining_records` INT(6) default 0,
			`filesize` VARCHAR(255) NOT NULL,
			`created` bigint(20) NOT NULL default 0,
			`updated` bigint(20) NOT NULL default 0,
			`failed` bigint(20) NOT NULL default 0,
			`skipped` bigint(20) NOT NULL default 0
				) ENGINE=InnoDB" 
				);

		$importlog_table_name = $wpdb->prefix ."import_log_detail";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $importlog_table_name (
			`id` int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`hash_key` VARCHAR(255) NOT NULL,
			`message` VARCHAR(255) NOT NULL,
			`status` VARCHAR(255) NOT NULL,
			`verify` blob NOT NULL,
			`categories` VARCHAR(255) NOT NULL,
			`tags` VARCHAR(255) NOT NULL,
			`post_id` int(6) NULL
				) ENGINE=InnoDB" 
				);

		$import_table_name = $wpdb->prefix ."import_postID";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $import_table_name(
			`post_id` int(6) NOT NULL,
			`line_number` int(6) NOT NULL) "
				);

		$import_records_table = $wpdb->prefix ."smackuci_events";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $import_records_table (
			`id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`revision` bigint(20) NOT NULL default 0,
			`name` varchar(255),
			`original_file_name` varchar(255),
			`friendly_name` varchar(255),
			`import_type` varchar(32),
			`filetype` text,
			`filepath` text,
			`eventKey` varchar(32),
			`registered_on` datetime NOT NULL default '0000-00-00 00:00:00',
			`parent_node` varchar(255),
			`processing` tinyint(1) NOT NULL default 0,
			`executing` tinyint(1) NOT NULL default 0,
			`triggered` tinyint(1) NOT NULL default 0,
			`event_started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`count` bigint(20) NOT NULL default 0,
			`processed` bigint(20) NOT NULL default 0,
			`created` bigint(20) NOT NULL default 0,
			`updated` bigint(20) NOT NULL default 0,
			`skipped` bigint(20) NOT NULL default 0,
			`failed` bigint(20) NOT NULL default 0,
			`deleted` bigint(20) NOT NULL default 0,
			`is_terminated` tinyint(1) NOT NULL default 0,
			`terminated_on` datetime NOT NULL default '0000-00-00 00:00:00',
			`last_activity` datetime NOT NULL default '0000-00-00 00:00:00',
			`siteid` int(11) NOT NULL DEFAULT 1,
			`month` varchar(60) DEFAULT NULL,
			`year` varchar(60) DEFAULT NULL,
			`deletelog` BOOLEAN DEFAULT false
				) ENGINE=InnoDB"
				);

		$acf_fields_table = $wpdb->prefix ."ultimate_csv_importer_acf_fields";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $acf_fields_table (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`groupId` varchar(100) NOT NULL,
			`fieldId` varchar(100) NOT NULL,
			`fieldLabel` varchar(100) NOT NULL,
			`fieldName` varchar(100) NOT NULL,
			`fieldType` varchar(60) NOT NULL,
			`fdOption` varchar(100) DEFAULT NULL,
			PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
				);

		$clitemplate = $wpdb->prefix. "cli_csv_template";
		$clitemplate = $wpdb->query("CREATE TABLE IF NOT EXISTS $clitemplate (
			`ID` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`template_name` varchar(255) DEFAULT NULL,
			`file_name` varchar(255) DEFAULT NULL,
			`type` varchar(255) DEFAULT NULL,
			`templatekey` varchar(32) NOT NuLL,	
			`month` varchar(60) DEFAULT NULL,
			`year` varchar(60) DEFAULT NULL					
			) ENGINE=InnoDB"
			); 
	
//summary log
			$summarylog = $wpdb->prefix . "summary";
			$wpdb->query("
    	CREATE TABLE IF NOT EXISTS $summarylog (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `event_id` VARCHAR(255) NOT NULL,
        `post_id` INT  NULL,
        `post_title` VARCHAR(255)  NULL,
        `post_type` VARCHAR(255)  NULL,
        `status` VARCHAR(255)  NULL,
        `is_category` TINYINT NOT NULL,
        `associated_media` INT(250)  NULL,
        `failed_media` INT(250)  NULL
    		) ENGINE=InnoDB;
			");

					//failed media log
		$failed_media = $wpdb->prefix . "failed_media";
		$wpdb->query("
		CREATE TABLE IF NOT EXISTS $failed_media (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`event_id` VARCHAR(255) NOT NULL,
		`post_id` INT NOT NULL,
		`media_id` INT NOT NULL,
		`title` VARCHAR(255)  NULL,
		`file_name` VARCHAR(255)  NULL,
		`caption` VARCHAR(255)  NULL,
		`description` VARCHAR(255)  NULL,
		`alt_text` VARCHAR(255)  NULL,
		`actual_url` VARCHAR(255)  NULL,
		`file_url` VARCHAR(255)  NULL,
		`status` VARCHAR(255)  NULL

		) ENGINE=InnoDB;
		");
		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}failed_media` LIKE 'post_title'");
		if($result){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}failed_media` CHANGE post_title title VARCHAR(20)");
		}
			
		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}import_detail_log` LIKE 'running'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}import_detail_log` ADD COLUMN running boolean not null default 1");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}import_detail_log` LIKE 'templatekey'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}import_detail_log` ADD COLUMN templatekey varchar(32)");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}import_detail_log` LIKE 'failed'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}import_detail_log` ADD COLUMN failed bigint(20) NOT NULL default 0");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}smackuci_events` LIKE 'failed'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}smackuci_events` ADD COLUMN failed bigint(20) NOT NULL default 0");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM $file_table_name LIKE 'templatekey'");
		if($result == 0){
			$wpdb->query("ALTER TABLE $file_table_name ADD COLUMN templatekey varchar(32)");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM $image_table LIKE 'templatekey'");
		if($result == 0){
			$wpdb->query("ALTER TABLE $image_table ADD COLUMN templatekey varchar(32)");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM $shortcode_table_name LIKE 'templatekey'");
		if($result == 0){
			$wpdb->query("ALTER TABLE $shortcode_table_name ADD COLUMN templatekey varchar(32)");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}smackuci_events` LIKE 'deletelog'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}smackuci_events` ADD COLUMN deletelog boolean default false");
		}
		$image_shortcode_result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'original_image'");
		if($image_shortcode_result){
			$wpdb->query("ALTER table `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` MODIFY original_image TEXT NOT NULL;");
		}
		$result_1 = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'indexs'");
		if($result_1 == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` ADD COLUMN indexs int(10) DEFAULT NULL");
		}
		$result_2 = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'import_type'");
		if($result_2 == 0){
			$wpdb->query("ALTER table `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` ADD COLUMN import_type VARCHAR(10);");
		}
		$result_3 =  $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'templatekey'");
		if($result_3 == 0){
			$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}ultimate_csv_importer_shortcode_manager DROP COLUMN templatekey");     
		}
		$result_4 = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'file_name'");
		if($result_4 == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` ADD COLUMN file_name varchar(100)");
		}
		$result_5 = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'post_title'");
		if($result_5 == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` ADD COLUMN post_title varchar(255)");
		}
		$result_6 = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'media_id'");
		if($result_6 == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` ADD COLUMN media_id INT(6)");
		}
		$result_7 = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'status'");
		if($result_7 == 1){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` Modify COLUMN status VARCHAR(255) DEFAULT 'failed'");
		}
		$result_8 = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'hash_key'");
		if($result_8 == 1){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` Modify COLUMN hash_key VARCHAR(255) DEFAULT NULL ");
		}
	}
}
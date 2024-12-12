<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class ElementorImport {
	private static $elementor_instance = null,$media_instance;

	public static function getInstance() {
		if (ElementorImport::$elementor_instance == null) {
			ElementorImport::$elementor_instance = new ElementorImport;
			return ElementorImport::$elementor_instance;
		}
		return ElementorImport::$elementor_instance;
	}

	public function set_elementor_value($header_array ,$value_array , $map, $post_id , $type, $hash_key, $gmode, $templatekey){	

		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		foreach ($post_values as $custom_key => $custom_value) {
			if(is_serialized($custom_value) && $custom_key != '_elementor_data'){
				$custom_value = unserialize($custom_value);		
			}
			elseif($custom_key =='_elementor_data'){
				$custom_value = wp_slash(base64_decode($custom_value));
			}
			update_post_meta($post_id, $custom_key, $custom_value);
		}

	}

	function set_elementor_values($header_array ,$value_array , $map, $post_id , $type, $mode, $line_number , $hash_key){	

		global $wpdb;
        $smackcsv_instance = SmackCSV::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		$upload_dir = $smackcsv_instance->create_upload_dir();
		$file_table_name = $wpdb->prefix . "smackcsv_file_events";

		$file = $wpdb->get_results("SELECT file_name,total_rows FROM $file_table_name WHERE `hash_key` = '$hash_key'");
		$file_name = $file[0]->file_name;
		$total_rows = $file[0]->total_rows;
		$addHeader =1;
		$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
		
		$csv_file = $upload_dir.$hash_key.'/'.$hash_key;
		$file_handle = fopen($csv_file, 'r');
		$first_row = true;
		while (($data = fgetcsv($file_handle, 1000, ",")) !== FALSE) {
			// Skip header row
			if ($first_row) {
				$first_row = false;
				continue;
			}
			$content_data=$data[2];

			$content=unserialize($content_data);
			$styles =unserialize($data[3]);
			$style_data =unserialize($styles);
			$style_encode= json_encode($style_data);
			$style_encode = wp_slash( $style_encode );
				$template_data = [
					'post_title'   => $data[1],
					'post_content' => $content['content'],
					'post_type'    => 'elementor_library',
					'post_status'  => $data[7],
					'post_date'    => $data[5],
					'post_author'  => 1,
				];
			$post_id = wp_insert_post($template_data);
			$id[]= $post_id;


			if ($id) {
				foreach($id as $post_id){
				if(isset($style_encode)){
					add_post_meta($post_id, '_elementor_data', $style_encode);
					add_post_meta($post_id, '_elementor_edit_mode', 'builder');
                    add_post_meta($post_id, '_elementor_version', '3.17.3');
                    add_post_meta($post_id, '_wp_page_template', 'default');
				}
				update_post_meta($post_id, '_elementor_template_type', $data[4]);
				}
			}
		}
		fclose($file_handle);
		if ($id) {
			foreach($id as $post_id){
			$core_instance->detailed_log[$post_id]['Message'] = "Imported Successfully.Imported Template ID: $post_id <br/>";
			$core_instance->detailed_log[$post_id]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $template_title ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $post_id, true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";					
		}
		$log_manager_instance = LogManager::getInstance();
		$log_manager_instance->get_event_log($hash_key , $file_name , $file_extension , $mode , $total_rows , $type , $core_instance->detailed_log, $addHeader,$templatekey = null);

		}
		return $post_id;
	}
}
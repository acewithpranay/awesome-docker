<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class MappingExtension {
	private static $instance = null,$extension_handler ;
	private static $extension = [];
	private static $validatefile;

	private function __construct(){
		$plugin_pages = ['com.smackcoders.csvimporternew.menu'];
		global $plugin_ajax_hooks;

		$request_page = isset($_REQUEST['page']) ?sanitize_text_field($_REQUEST['page']) : '';
		$request_action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
		if (in_array($request_page, $plugin_pages) || in_array($request_action, $plugin_ajax_hooks)) {
			add_action('wp_ajax_mappingfields',array($this,'mapping_field_function')); 
		}
	}

	public static function getInstance() {
		if (MappingExtension::$instance == null) {
			MappingExtension::$instance = new MappingExtension;
			MappingExtension::$validatefile = new ValidateFile;
	
			foreach(get_declared_classes() as $class){
				if(is_subclass_of($class, 'Smackcoders\FCSV\ExtensionHandler')){ 
					array_push(MappingExtension::$extension ,$class::getInstance() );
				}
			}
			return MappingExtension::$instance;
		}
		return MappingExtension::$instance;
	}

	/**
	 * Ajax Call 
	 * Provides all Widget Fields for Mapping Section
	 * @return array - mapping fields
	 */
	public function mapping_field_function(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		$import_type = sanitize_text_field($_POST['Types']);
		if(isset($_POST['MediaType'])){
			$media_type = sanitize_key($_POST['MediaType']);
		}
		$hash_key = sanitize_key($_POST['HashKey']);
		$get_key = get_option('openAI_settings');
		$mode = sanitize_text_field($_POST['Mode']);
		global $wpdb;

		$response = [];
		$current_user = wp_get_current_user();
		$current_user_role = $current_user->roles[0];
		$response['currentuser']=$current_user_role;
		$details = [];
		$info = [];

		$table_name = $wpdb->prefix."smackcsv_file_events";
		$fields = $wpdb->get_results("UPDATE $table_name SET mode ='$mode' WHERE hash_key = '$hash_key'");

		$get_result = $wpdb->get_results("SELECT file_name, total_rows FROM $table_name WHERE hash_key = '$hash_key' ");
		$filename = $get_result[0]->file_name;
		$total_rows = $get_result[0]->total_rows;
		$file_extension = pathinfo($filename, PATHINFO_EXTENSION);
		if(empty($file_extension)){
			$file_extension = 'xml';
		}
		$template_table_name = $wpdb->prefix."ultimate_csv_importer_mappingtemplate";
		$smackcsv_instance = SmackCSV::getInstance();
		$upload_dir = $smackcsv_instance->create_upload_dir();
		if($file_extension == 'csv' || $file_extension == 'txt'){
			if (version_compare(PHP_VERSION, '8.1.0', '<')) {  // Only do this if PHP version is less than 8.1.0
				if (!ini_get("auto_detect_line_endings")) {
					ini_set("auto_detect_line_endings", true);
				}
			}
			$info = [];
			if (($h = fopen($upload_dir.$hash_key.'/'.$hash_key, "r")) !== FALSE) 
			{
				// Convert each line into the local $data variable

				$delimiters = array( ',','\t',';','|',':','&nbsp');
				$file_path = $upload_dir . $hash_key . '/' . $hash_key;
				$delimiter = MappingExtension::$validatefile->getFileDelimiter($file_path, 5);
				$array_index = array_search($delimiter,$delimiters);
				if($array_index == 5){
					$delimiters[$array_index] = ' ';
				}
				if($delimiter == '\t'){
					$delimiter ='~';
					 $temp=$file_path.'temp';
					 if (($handles = fopen($temp, 'r')) !== FALSE){
						while (($data = fgetcsv($handles, 0, $delimiter)) !== FALSE)
						{
							$trimmed_array = array_map('trim', $data);
							array_push($info , $trimmed_array);	
							$exp_line = $info[0];
							$response['success'] = true;
							$response['show_template'] = false;
							$response['csv_fields'] = $exp_line;
							$response['currentuser']=$current_user_role;
							if(!empty($media_type) && $import_type == 'Media'){
								$value = $this->media_mapping_fields($import_type,$mode,$media_type);
							}else{
								$value = $this->mapping_fields($import_type);
							}
							$response['fields'] = $value;					
							echo wp_json_encode($response);
							wp_die();  			  			
						}
					}

					fclose($handles);
				}
				else{
					while (($data = fgetcsv($h, 0, $delimiters[$array_index])) !== FALSE) 
					{		
						// Read the data from a single line
						$trimmed_info = array_map('trim', $data);
						array_push($info , $trimmed_info);
						$exp_line = $info[0];

						$response['success'] = true;
						$response['get_key'] = $get_key;
						$response['show_template'] = false;
						$response['csv_fields'] = $exp_line;
						if(!empty($media_type) && $import_type == 'Media'){
							$value = $this->media_mapping_fields($import_type,$mode,$media_type);
						}else{
							$value = $this->mapping_fields($import_type);
						}
						$response['fields'] = $value;
						$response['total_records'] = (int)$total_rows;
						echo wp_json_encode($response);
						wp_die();  			
					}	
					// Close the file
					fclose($h);
				}
			}
		}
		if($file_extension == 'xml'){
			$xml_class = new XmlHandler();
			$upload_dir_path = $upload_dir. $hash_key;
			if (!is_dir($upload_dir_path)) {
				wp_mkdir_p( $upload_dir_path);
			}
			chmod($upload_dir_path, 0777);   
			$path = $upload_dir . $hash_key . '/' . $hash_key;   

			$xml = simplexml_load_file($path);
			$xml_arr = json_decode( json_encode($xml) , 1);

			foreach($xml->children() as $child){   
				$child_name = $child->getName();    
			}
			$parse_xml = $xml_class->parse_xmls($hash_key);
			$i = 0;
			$headers=[];
			foreach($parse_xml as $xml_key => $xml_value){
				if(is_array($xml_value)){
					foreach ($xml_value as $e_key => $e_value){
						$headers[$i] = $e_value['name'];
						$i++;
					}
				}
			}
			$response['success'] = true;
			$response['show_template'] = false;
			$response['csv_fields'] = $headers;
			if(!empty($media_type) && $import_type == 'Media'){
				$value = $this->media_mapping_fields($import_type,$mode,$media_type);
			}else{
				$value = $this->mapping_fields($import_type);
			}
			$response['fields'] = $value;
			$response['total_records'] = (int)$total_rows;
			echo wp_json_encode($response);
			wp_die();  			
		}
	}

	/**
	 * Provides active plugins
	 * @return array - active plugins
	 */
	public function get_active_plugins() {
		$active_plugins = get_option('active_plugins');
		return $active_plugins;
	}

	/**
	 * Provides all Widget Fields for Export Section
	 * @return array - mapping fields
	 */
	public function get_fields($module){ 
		$import_type = $module;
		$response = [];
		$value = $this->mapping_fields($import_type,'Export');
		$response['fields'] = $value;
		return $response;
	}

	public function mapping_fields($import_type,$process_type = null){
		$support_instance = [];
		$value = [];
		//SmackCSV::getInstance();
		for($i = 0 ; $i < count(MappingExtension::$extension) ; $i++){
			$extension_instance = MappingExtension::$extension[$i];
			if($extension_instance->extensionSupportedImportType($import_type)){
				array_push($support_instance , $extension_instance);		
			}	
		}		
		
		for($i = 0 ;$i < count($support_instance) ; $i++){	
			$supporting_instance = $support_instance[$i];
			$fields = $supporting_instance->processExtension($import_type,$process_type);
			array_push($value , $fields);			
		}
		return $value;
	}

	public function media_mapping_fields($import_type,$mode =null,$media_type=null){
		MappingExtension::$extension_handler = new ExtensionHandler();
		$support_instance = [];
		if($import_type == 'Media') {
			if($media_type == 'local'){
				$wordpressfields = array(
					'File Name' => 'file_name',
					'Caption' => 'caption',
					'Alt text' => 'alt_text',
					'Desctiption' => 'description',
					'Title' => 'title',
					'Media ID' => 'media_id',
				);
				if(trim($mode) == 'Insert'){
					unset($wordpressfields['Media ID']);
				}
			}else{
				$wordpressfields = array(
					'Post ID' => 'post_id',
					'Media ID' => 'media_id',
					'Actual URL' => 'actual_url',
					'File Name' => 'file_name',
					'Title' => 'title',
					'Caption' => 'caption',
					'Alt text' => 'alt_text',
					'Desctiption' => 'description'		
				);
				if(trim($mode) == 'Insert'){
					unset($wordpressfields['Post ID']);
					unset($wordpressfields['Media ID']);
				}
			}
			
		}
		$wordpress_value = MappingExtension::$extension_handler->convert_static_fields_to_array($wordpressfields);
		$response[]['core_fields'] = $wordpress_value ;
		return $response;
	}
}		

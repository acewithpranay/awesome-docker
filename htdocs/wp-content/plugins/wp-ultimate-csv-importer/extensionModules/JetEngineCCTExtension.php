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

class JetEngineCCTExtension extends ExtensionHandler{
    private static $instance = null;
 
    public static function getInstance() {
		if (JetEngineCCTExtension::$instance == null) {
			JetEngineCCTExtension::$instance = new JetEngineCCTExtension;
		}
		return JetEngineCCTExtension::$instance;
	}
	
	/**
	* Provides default mapping fields for Jet Engine Pro plugin
	* @param string $data - selected import type
	* @return array - mapping fields
	*/

    public function processExtension($data){	
		$import_type = $data;
		$response = [];
		$jet_engine_cct_fields = $this->JetEngineCCTFields($import_type);
		$response['jetenginecct_fields'] = $jet_engine_cct_fields;	
		//$jet_engine_cct_rf_fields = $this->JetEngineCCTRFFields($import_type);
		//$response['jetenginecct_rf_fields'] = $jet_engine_cct_rf_fields;
		return $response;		
	}

	/**
	* Retrieves Jet Engine mapping fields
	* @param string $import_type - selected import type
	* @return array - mapping fields
	*/
	public function JetEngineCCTFields($import_type) {	
		global $wpdb;
	
		$get_meta_fields = $wpdb->get_row(
			$wpdb->prepare("SELECT meta_fields, args FROM {$wpdb->prefix}jet_post_types WHERE slug = %s AND status = %s", 
			$import_type, 
			'content-type'
		));
	
		// Initialize variables
		$unserialized_meta = '';
		$has_single_page = false;
		$customFields = [];

		if ($get_meta_fields) {
			$unserialized_meta = maybe_unserialize($get_meta_fields->meta_fields);
			$arg_data = maybe_unserialize($get_meta_fields->args);
			if (!empty($arg_data) && !empty($arg_data['has_single'])) {
				$has_single_page = $arg_data['has_single'];
			}
		}
		if (is_array($unserialized_meta)) {
			foreach ($unserialized_meta as $jet_key => $jet_value) {
				$jet_field_label = $jet_value['title'];
				$jet_field_name = $jet_value['name'];
				$jet_field_type = $jet_value['type'];
				$fields_object_type = $jet_value['object_type'];

				if ($fields_object_type == 'field' && $jet_field_type != 'repeater' && $jet_field_type != 'html') {
					$customFields["JECCT"][$jet_key] = [
						'label' => $jet_field_label,
						'name'  => $jet_field_name
					];
				}
			}
			if ($has_single_page) {
				$single_page_fields = ['CCT Single Post ID' => 'cct_single_post_id','CCT Single Post Title' => 'cct_single_post_title','CCT Single Post Content' => 'cct_single_post_content'];
				foreach ($single_page_fields as $label => $name) {
					$customFields["JECCT"][] = [
						'label' => $label,
						'name'  => $name
					];
				}
			}
		}
	
		// Convert custom fields to array if available
		$jet_value = !empty($customFields) ? $this->convert_fields_to_array($customFields) : '';
	
		return $jet_value;
	}
	/**
	* Jet Engine extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/

	public function extensionSupportedImportType($import_type){
        
		if(is_plugin_active('jet-engine/jet-engine.php')){
			if($import_type == 'nav_menu_item'){
				return false;
			}
			$import_type = $this->import_name_as($import_type);
			if($import_type =='Posts' || $import_type =='Pages' || $import_type =='CustomPosts' || $import_type == 'jet-engine'||$import_type =='event' || $import_type =='location' || $import_type == 'event-recurring' || $import_type =='Users' || $import_type =='WooCommerce'  || $import_type =='WooCommerceCategories' || $import_type =='WooCommerceattribute' || $import_type =='WooCommercetags' || $import_type =='WPeCommerce' || $import_type =='Taxonomies' || $import_type =='Tags' || $import_type =='Categories' || $import_type == 'CustomerReviews' || $import_type ='Comments') {		
				return true;
			}
			if($import_type == 'ticket'){
				if(is_plugin_active('events-manager/events-manager.php')){
					return false;
				}else{
					return true;
				}
			}
			else{
				return false;
			}
		}
	}
}

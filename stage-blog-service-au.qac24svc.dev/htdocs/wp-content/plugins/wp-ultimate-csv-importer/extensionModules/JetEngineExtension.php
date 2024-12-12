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

class JetEngineExtension extends ExtensionHandler{
    private static $instance = null;
 
    public static function getInstance() {
		if (JetEngineExtension::$instance == null) {
			JetEngineExtension::$instance = new JetEngineExtension;
		}
		return JetEngineExtension::$instance;
	}
	
	/**
	* Provides default mapping fields for Jet Engine Pro plugin
	* @param string $data - selected import type
	* @return array - mapping fields
	*/

    public function processExtension($data){	
		$import_type = $data;
		$response = [];
		$jet_engine_fields = $this->JetEngineFields($import_type);
		$response['jetengine_fields'] = $jet_engine_fields;	
		$jet_engine_rf_fields = $this->JetEngineRFFields($import_type);
		if(!empty($jet_engine_rf_fields)){
			$response['jetengine_rf_fields'] = null;	
		}
		return $response;		
	}

	/**
	* Retrieves Jet Engine mapping fields
	* @param string $import_type - selected import type
	* @return array - mapping fields
	*/
	public function JetEngineFields($import_type) {	
	
		$import_type = $this->import_post_types($import_type);
		global $wpdb;	
		
		$get_meta_box_fields = $wpdb->get_results( $wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = %s",'jet_engine_meta_boxes'));
		if(!empty($get_meta_box_fields)){
			$unserialized_meta = maybe_unserialize($get_meta_box_fields[0]->option_value);
			if(isset($unserialized_meta)){
				$arraykeys = array_keys($unserialized_meta);
			}
			
			foreach($arraykeys as $val){
				$values = explode('-',$val);
				$v = $values[1];
			}
			
			$temp = 0;
			if(isset($v)){
				for($i=1 ; $i<=$v ; $i++){
					$unserialized_meta['meta-'.$i] = isset($unserialized_meta['meta-'.$i]) ? $unserialized_meta['meta-'.$i] : '';
					$fields = $unserialized_meta['meta-'.$i];
					
					if(!empty($fields)){
						$fields_object_type = $fields['args']['object_type'];
						if($fields_object_type == 'post'){
							$fields_allowed_post_type = $fields['args']['allowed_post_type'];
							foreach($fields_allowed_post_type as $key => $fields_allowed_post_type_value){
								if($fields_allowed_post_type_value == $import_type){
									foreach($fields['meta_fields'] as $jet_key => $jet_value){
										if($jet_value != 'repeater' && $jet_value != 'media' && $jet_value != 'gallery' && $jet_value != 'posts' && $jet_value != 'html' ){
											$jet_field_label = $jet_value['title'];
											$jet_field_name = $jet_value['name'];
										
											$customFields["JE"][ $temp ]['label'] = $jet_field_label;
											$customFields["JE"][ $temp ]['name']  = $jet_field_name;	
											
											$temp++;
										}
									}
								}
							}
						}
						if($fields_object_type == 'taxonomy'){
							//if ($import_type == 'category' || $import_type == 'post_tag'|| $import_type == 'product_cat' || $import_type == 'product_tag'){
								$allowed_tax = $fields['args']['allowed_tax'];
								foreach($allowed_tax as $allowed_tax_key => $allowed_tax_val){
									if($allowed_tax_val == $import_type){
										foreach($fields['meta_fields'] as $jet_key => $jet_value){
											if($jet_value != 'repeater' && $jet_value != 'media' && $jet_value != 'gallery' && $jet_value != 'posts' && $jet_value != 'html' ){
												$jet_field_label = $jet_value['title'];
												$jet_field_name = $jet_value['name'];
											
												$customFields["JE"][ $temp ]['label'] = $jet_field_label;
												$customFields["JE"][ $temp ]['name']  = $jet_field_name;	
											
												$temp++;
											}
										}	
									}
								}	
							//}
						}
						if($fields_object_type == 'user'){
							if($fields_object_type == $import_type){
								foreach($fields['meta_fields'] as $jet_key => $jet_value){
									if($jet_value != 'repeater' && $jet_value != 'media' && $jet_value != 'gallery' && $jet_value != 'posts' && $jet_value != 'html' ){
										$jet_field_label = $jet_value['title'];
										$jet_field_name = $jet_value['name'];
										
										$customFields["JE"][ $temp ]['label'] = $jet_field_label;
										$customFields["JE"][ $temp ]['name']  = $jet_field_name;	
									
										$temp++;
									}
								}
							}
						}
					}	
				}
			}	
		}
	
		if(isset($customFields)){
			$jet_value = $this->convert_fields_to_array($customFields);
		}
		else{
			$jet_value = '';
		}
		return $jet_value;		
	}
	public function JetEngineRFFields($import_type){
		
		$import_type = $this->import_post_types($import_type);
		global $wpdb;	
		$get_meta_fields = $wpdb->get_results( $wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = %s",'jet_engine_meta_boxes'));
		
		if(!empty($get_meta_fields)){
			$unserialized_meta = maybe_unserialize($get_meta_fields[0]->option_value);
			if(isset($unserialized_meta)){
				$arraykeys = array_keys($unserialized_meta);
			}
			
			foreach($arraykeys as $val){
				$values = explode('-',$val);
				$v = $values[1];
			}
			if(isset($v)){
				for($i=1 ; $i<=$v ; $i++){
					$unserialized_meta['meta-'.$i] = isset($unserialized_meta['meta-'.$i]) ? $unserialized_meta['meta-'.$i] : '';
					$fields = $unserialized_meta['meta-'.$i];
					if(!empty($fields)){
						//foreach($fields as $jet_key => $jet_value){
							$fields_object_type = $fields['args']['object_type'];
							if($fields_object_type == 'post'){
								$fields_allowed_post_type = $fields['args']['allowed_post_type'];
								foreach($fields_allowed_post_type as $key => $fields_allowed_post_type_value){
									if($fields_allowed_post_type_value == $import_type){
										foreach($fields['meta_fields'] as $jet_key => $jet_value){
											if($jet_value['type'] == 'repeater'){
												$jet_rep_fields = $jet_value['repeater-fields'];
												foreach($jet_rep_fields as $jet_rep_fkey => $jet_rep_fvalue){
													$jet_field_label[] = $jet_rep_fvalue['title'];
													$jet_field_name[]= $jet_rep_fvalue['name'];
													// $customFields["JERF"][ $jet_rep_fkey ]['label'] =$jet_field_label;
													// $customFields["JERF"][ $jet_rep_fkey ]['name']  = $jet_field_name;
												}
											}
										}
									}
								}
							}
							if($fields_object_type == 'taxonomy'){
								//if ($import_type == 'category' || $import_type == 'post_tag'){
									$allowed_tax = $fields['args']['allowed_tax'];
									foreach($allowed_tax as $allowed_tax_key => $allowed_tax_val){
										if($allowed_tax_val == $import_type){
											foreach($fields['meta_fields'] as $jet_key => $jet_value){
												if($jet_value['type'] == 'repeater'){
													$jet_rep_fields = $jet_value['repeater-fields'];
													foreach($jet_rep_fields as $jet_rep_fkey => $jet_rep_fvalue){
														$jet_field_label[] = $jet_rep_fvalue['title'];
														$jet_field_name[] = $jet_rep_fvalue['name'];
														//$customFields["JERF"][ $jet_rep_fkey ]['label'] =$jet_field_label;
														//$customFields["JERF"][ $jet_rep_fkey ]['name']  = $jet_field_name;
													}
												}
											}
										}
									}
								//}
							}
							if($fields_object_type == 'user'){
								if($fields_object_type == $import_type){
									foreach($fields['meta_fields'] as $jet_key => $jet_value){
										if($jet_value['type'] == 'repeater'){
											$jet_rep_fields = $jet_value['repeater-fields'];
											foreach($jet_rep_fields as $jet_rep_fkey => $jet_rep_fvalue){
												$jet_field_label[] = $jet_rep_fvalue['title'];
												$jet_field_name[] = $jet_rep_fvalue['name'];
												//$customFields["JERF"][ $jet_rep_fkey ]['label'] =$jet_field_label;
												//$customFields["JERF"][ $jet_rep_fkey ]['name']  = $jet_field_name;
											}
										}	
									}
								}
							}
						//}
					}
				}
			}
		}

		if(isset($jet_field_label)){
			foreach($jet_field_label as $jet_label_key => $jet_label_value){
				$customFields["JERF"][ $jet_label_key ]['label'] =$jet_label_value;
			}
		}

		if(isset($jet_field_name)){
			foreach($jet_field_name as $jet_name_key => $jet_name_value){
				$customFields["JERF"][ $jet_name_key ]['name']  = $jet_name_value;
			}   
		} 

		if(isset($customFields)){
			$jet_value = $this->convert_fields_to_array($customFields);
		}
		else{
			$jet_value = '';
		}
		//$jet_value = $this->convert_fields_to_array($customFields);
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
			if($import_type =='Posts' || $import_type =='Pages' || $import_type =='CustomPosts' || $import_type =='Users' || $import_type =='WooCommerce' || $import_type =='Taxonomies' || $import_type =='Tags' || $import_type =='Categories') {	
				return true;
			}
			else{
				return false;
			}
		}
	}

	function import_post_types($import_type, $importAs = null) {	
		$import_type = trim($import_type);
		$module = array('Posts' => 'post', 'Pages' => 'page','WooCommerce Product' => 'product','WooCommerce' => 'product', 'CustomPosts' => $importAs);
		foreach (get_taxonomies() as $key => $taxonomy) {
			$module[$taxonomy] = $taxonomy;
		}
		if(array_key_exists($import_type, $module)) {
			return $module[$import_type];
		}
		else {
			return $import_type;
		}
	}
}

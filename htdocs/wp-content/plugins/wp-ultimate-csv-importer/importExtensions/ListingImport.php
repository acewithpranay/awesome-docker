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

class ListingImport {
	private static $product_bundle_meta_instance = null;

	public static function getInstance() {

		if (ListingImport::$product_bundle_meta_instance == null) {
			ListingImport::$product_bundle_meta_instance = new ListingImport;
			return ListingImport::$product_bundle_meta_instance;
		}
		return ListingImport::$product_bundle_meta_instance;
	}

	function set_listing_values($header_array, $value_array, $maps , $post_id, $selected_type){
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();

		$post_values =$helpers_instance->get_header_values($maps , $header_array , $value_array);

		foreach ($post_values as $meta_key => $value) {
			print_r($post_values);
				update_post_meta($post_id, $meta_key, $value);

				if(is_plugin_active('geodirectory/geodirectory.php') ){
					
				
				$wpdb->update(
					$wpdb->prefix . 'geodir_gd_place_detail',
					array($meta_key => $value), 
					array('post_id' => $post_id), 
					array('%s'), 
					array('%d')  
				);

					//geodir_save_post($data, $post_id, 'gd_place');
				}

	
		}

	}
}

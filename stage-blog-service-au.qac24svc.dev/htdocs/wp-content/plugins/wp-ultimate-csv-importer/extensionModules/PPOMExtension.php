<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class PPOMExtension extends ExtensionHandler{
	private static $instance = null;

	public static function getInstance() {

		if (PPOMExtension::$instance == null) {
			PPOMExtension::$instance = new PPOMExtension;
		}
		return PPOMExtension::$instance;
	}

	/**
	 * Provides PPOM Meta fields for specific post type
	 * @param string $data - selected import type
	 * @return array - mapping fields
	 */
	public function processExtension($data){

		global $wpdb;
		$table_name = $wpdb->prefix . 'nm_personalized';

		// Fetch all the PPOM field setups from the `wp_nm_personalized` table
		$field_setups = $wpdb->get_results( "SELECT * FROM $table_name" );

		if ( !empty( $field_setups ) ) {

			foreach ( $field_setups as $setup ) {
				$meta_data = json_decode( $setup->the_meta, true ); 

				if ( is_array( $meta_data ) ) {

					foreach ( $meta_data as $key => $field ) {
						$pro_meta_fields[$field['title']] = $field['data_name'];
					}


				} 
			}

		}        

		$pro_meta_fields = $this->convert_static_fields_to_array($pro_meta_fields);
		$response['ppom_meta_fields'] = $pro_meta_fields;
		return $response;		
	}

	/**
	 * PPOM Meta extension supported import types
	 * @param string $import_type - selected import type
	 * @return boolean
	 */
	public function extensionSupportedImportType($import_type ){

		if(is_plugin_active('advanced-product-fields-for-woocommerce/advanced-product-fields-for-woocommerce.php') ) {
			if($import_type == 'nav_menu_item'){
				return false;
			}

			$import_type = $this->import_name_as($import_type);

			if($import_type == 'WooCommerceOrders') { 
				return true;
			}else{
				return false;
			}
		}
	}

}

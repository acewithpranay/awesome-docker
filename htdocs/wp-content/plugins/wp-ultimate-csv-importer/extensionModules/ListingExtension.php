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

class ListingExtension extends ExtensionHandler{
	private static $instance = null;

	public static function getInstance() {		
		if (ListingExtension::$instance == null) {
			ListingExtension::$instance = new ListingExtension;
		}
		return ListingExtension::$instance;
	}

	/**
	 * Provides Listing Meta fields for specific post type
	 * @param string $data - selected import type
	 * @return array - mapping fields
	 */
	public function processExtension($data){   
		global $wpdb;  
		$import_type = $data;
		$response = [];
		$import_type = $this->import_name_as($import_type);
		if(is_plugin_active('business-directory-plugin/business-directory-plugin.php') && $data == 'wpbdp_listing' ){
			if($import_type == 'CustomPosts'){

				$table_name = $wpdb->prefix . 'wpbdp_form_fields';

				$results = $wpdb->get_results( "SELECT * FROM $table_name" );

				if ( !empty( $results ) ) {
					foreach ( $results as $field ) {

						$pro_meta_fields[$field->label] = $field->shortname;

					}
				} 

			}
		}

		if(is_plugin_active('geodirectory/geodirectory.php') && $data == 'gd_place'  ){
			
		$pro_meta_fields = array(			
			'Address' => 'street',
			'Country' => 'country',
			'Region' => 'region',
			'City' => 'city',
			'Zoom'  => 'mapzoom',
			'Zip/PosT Code' => 'zip',
			'Latitude' => 'latitude',
			'longitude' => 'longitude',
			'Map View' => 'mapview');
			
		}

		
		if(is_plugin_active('advanced-classifieds-and-directory-pro/acadp.php') && $data == 'acadp_listings'){
	
		$pro_meta_fields = array(
			'Price' => 'price',
			'Views Count' => 'views',
			'Address' => 'address',
			'Zip Code' => 'zipcode',
			'Phone' => 'phone',
			'Email' => 'email',
			'Website' => 'website',
			'Image' => 'images',
			'Video' => 'video',
			'Latitude' => 'latitude',
			'Longitude' => 'longitude');
		}

		$pro_meta_fields = $this->convert_static_fields_to_array($pro_meta_fields);
		$response['listing_meta_fields'] = $pro_meta_fields;

		return $response;

	}

	/**
	 * Listing Extension supported import types
	 * @param string $import_type - selected import type
	 * @return boolean
	 */
	public function extensionSupportedImportType($import_type ){
		if(is_plugin_active('business-directory-plugin/business-directory-plugin.php') || is_plugin_active('geodirectory/geodirectory.php') || is_plugin_active('connections/connections.php') || is_plugin_active('wpdirectorykit/wpdirectorykit.php') || is_plugin_active('advanced-classifieds-and-directory-pro/acadp.php')){
			$import_type = $this->import_name_as($import_type);
			if( $import_type =='CustomPosts' ) {		
				return true;
			}
			else{
				return false;
			}
		}
	}

}

<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

 namespace Smackcoders\FCSV;

 if ( ! defined( 'ABSPATH' ) )
     exit; // Exit if accessed directly

class FeaturedMediaExtension extends ExtensionHandler{
	public static $instance = null;

    public static function getInstance() {		
		if (FeaturedMediaExtension::$instance == null) {
			FeaturedMediaExtension::$instance = new FeaturedMediaExtension;
		}
		return FeaturedMediaExtension::$instance;
    }

	/**
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
		$mode = isset($_POST['Mode']) ? sanitize_text_field($_POST['Mode']) : '';		
		$import_types = $data;
        $import_type = $this->import_name_as($import_types);
		$response = [];
        if( $import_type == "Posts" || $import_type == "Pages" || $import_type == "CustomPosts" || $import_type == "WooCommerce"){
            $wordpressfields = array(
                                'Title' => 'featured_image_title',
                                'Caption' => 'featured_image_caption',
                                'Alt text' => 'featured_image_alt_text',
                                'Description' => 'featured_image_description',
                                'File Name' =>    'featured_file_name'									
                                    );
            $wordpress_value = $this->convert_static_fields_to_array($wordpressfields);
            $response['featured_fields'] = $wordpress_value ;
        }
		return $response;	
    }

	/**
	* CFS extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type){	
        
        if( $import_type != "WooCommerce Orders" && $import_type != "WooCommerce Coupons" && $import_type != "WooCommerce Product Variations" && $import_type != "WooCommerce Refunds" && $import_type != "WooCommerce Attributes" && $import_type != "Comments" && $import_type != "nav_menu_item" && $import_type != "widgets" && $import_type != "elementor_library"){
            return true;
        }
	}
    
}
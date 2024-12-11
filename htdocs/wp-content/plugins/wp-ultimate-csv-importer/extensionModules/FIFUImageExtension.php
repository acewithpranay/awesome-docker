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

class FIFUExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {		
		if (FIFUExtension::$instance == null) {
			FIFUExtension::$instance = new FIFUExtension;
		}
		return FIFUExtension::$instance;
	}
	
	/**
	* Provides FIFU mapping fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
        $mode = isset($_POST['Mode']) ? sanitize_text_field($_POST['Mode']) : "";     
        $import_type = $data;
        $response = [];
		if(is_plugin_active('featured-image-from-url/featured-image-from-url.php')){
            if($import_type == 'Posts'){
                $fifu_meta_fields = array(
                    'Fifu image url' => 'fifu_image_url',
                    'Fifu image alt' => 'fifu_image_alt',                                
                );
        }elseif($import_type == 'Pages'){
            $fifu_meta_fields = array(
                'Fifu image url' => 'fifu_image_url',
                'Fifu image alt' => 'fifu_image_alt',                                
            );
        }else{
            $fifu_meta_fields = array(
                'Fifu image url' => 'fifu_image_url',
                'Fifu image alt' => 'fifu_image_alt',                                
            );                
        }

    }
    $fifu_meta_fields_line = $this->convert_static_fields_to_array($fifu_meta_fields);

    
        if($data == 'Posts'){
            $response['fifu_post_settings_fields'] = $fifu_meta_fields_line; 
        }elseif($data == 'Pages'){
            $response['fifu_page_settings_fields'] = $fifu_meta_fields_line; 
        }else{
            $response['fifu_custompost_settings_fields'] = $fifu_meta_fields_line; 
        }
      
		return $response;
}

	/**
	* FIFU extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type){
		if(is_plugin_active('featured-image-from-url/featured-image-from-url.php')){

            $import_type = $this->import_name_as($import_type);

			if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts') {
				return true;
			}
			else{
				return false;
			}   
        }
}
}

<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class PolylangExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {		
        if (PolylangExtension::$instance == null) {
            PolylangExtension::$instance = new PolylangExtension;
        }
        return PolylangExtension::$instance;
    }

    
	public function processExtension($data) {
        if(is_plugin_active('polylang/polylang.php') || is_plugin_active('polylang-pro/polylang.php')){
        $response = [];
        $import_type = $this->import_name_as($data);
        $poly_settings=get_option('polylang');
        if($import_type =='Tags' || $import_type =='Categories'){
        	$polylangFields = array(
			'LANGUAGE_CODE' => 'language_code',
			'TRANSLATED_TAXONOMY_TITLE' => 'translated_taxonomy_title');
        }
        else if($import_type == 'Media' ){
            if($poly_settings['media_support'] == 1){
                $polylangFields = array(
                    'LANGUAGE_CODE' => 'language_code');
            }
            else{
                return true;
            }
          
        }
        else{
        	$polylangFields = array(
			'Language Code' => 'language_code',
			'Translated Post Title' => 'translated_post_title');
        }
		$polylang_value = $this->convert_static_fields_to_array($polylangFields);
		$response['Polylang_settings_fields'] = $polylang_value ;
        return $response;
    }
	}

	/**
	 * Nextgen Gallery extension supported import types
	 * @param string $import_type - selected import type
	 * @return boolean
	 */
	public function extensionSupportedImportType($import_type){
      
        if(is_plugin_active('polylang/polylang.php') || is_plugin_active('polylang-pro/polylang.php')){
            $import_type = $this->import_name_as($import_type);
            if($import_type == 'Posts' || $import_type == 'WooCommerce' || $import_type =='Pages'|| $import_type =='CustomPosts' || $import_type =='Tags' || $import_type =='Categories') { 
                 return true;
            }else{
                 return false;
            }
        }
    }
		
}
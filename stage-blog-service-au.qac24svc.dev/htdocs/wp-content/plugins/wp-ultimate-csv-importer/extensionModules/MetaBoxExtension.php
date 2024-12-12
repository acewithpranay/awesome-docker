<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class MetaBoxExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {		
		if (MetaBoxExtension::$instance == null) {
			MetaBoxExtension::$instance = new MetaBoxExtension;
		}
		return MetaBoxExtension::$instance;
    }

	/**
	* Provides Metabox mapping fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
        global $wpdb;
        $response = []; 
        $import_type = $this->import_post_types($data);

        $metabox_fields = [];
        $listTaxonomy = get_taxonomies();
		if($import_type == 'user'){
			$get_metabox_fields = \rwmb_get_object_fields($import_type,'user');
		}elseif(in_array($import_type, $listTaxonomy)){
			$get_metabox_fields =  \rwmb_get_object_fields($import_type,'term');
		}else{
			$get_metabox_fields =  \rwmb_get_object_fields($import_type);
		} 

		if(!empty($get_metabox_fields)){
			foreach($get_metabox_fields as $meta_key => $meta_value){
				$customFields[$meta_value['name']] = $meta_key;
			}
			$mb_value = $this->convert_static_fields_to_array($customFields);
		}
		else{
			$mb_value = '';
		}

		$response['metabox_fields'] =  $mb_value;
		return $response;	
    }

	/**
	* Metabox extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type ){
		if(is_plugin_active('meta-box/meta-box.php')){
			if($import_type == 'nav_menu_item'){
				return false;
			}
			$import_type = $this->import_name_as($import_type);
           
			if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'event' || $import_type == 'event-recurring' || $import_type == 'Users' || $import_type == 'Taxonomies'  || $import_type =='Tags' || $import_type =='Categories') {
				return true;
			}
			else{
				return false;
			}
		}
	}
}
<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */ 

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class TotalpressExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {
		
		if (TotalpressExtension::$instance == null) {
			TotalpressExtension::$instance = new TotalpressExtension;
		}
		return TotalpressExtension::$instance;
    }

	/**
	* Provides SEOPress fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
	    $response = []; 
        global $wpdb;
		$customFields = $cfs_field = array();
		$getTPGroups = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_type = %s and post_status = %s", 'manage_cpt_field' , 'publish'),ARRAY_A);
		$groupIdArr = [];
		foreach ( $getTPGroups as $item => $groupRules ) {
			$groupIdArr[] .= $groupRules['ID'] . ',';
		}
			
		if($groupIdArr != '') {
			foreach($groupIdArr as $groupId){	
				$getTPFields= $wpdb->get_results( $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id IN (%s) and meta_key =%s ",$groupId,'fields'), ARRAY_A);		
			}
		}
		
		$temp = 0;
		if (!empty($getTPFields)) {
			foreach ($getTPFields as $key => $value) {
				$getTPField = @unserialize($value['meta_value']);
				
				foreach($getTPField as $fk => $fv){
					$customFields["TOTALPRESS"][$temp]['label'] = $fv['label'];
					$customFields["TOTALPRESS"][$temp]['name'] = $fv['key'];
					$temp++;
				}
			}
		}
		$tpValue = $this->convert_fields_to_array($customFields);
		$response['totalpress_fields'] =  $tpValue;
		return $response;	
    }

	/**
	* Yoast SEOPress extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type ){		
		if(is_plugin_active('custom-post-types/custom-post-types.php') ){
			if($import_type == 'nav_menu_item'){
				return false;
			}

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
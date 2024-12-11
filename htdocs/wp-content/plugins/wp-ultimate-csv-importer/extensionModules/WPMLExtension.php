<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class WPMLExtension extends ExtensionHandler{
	private static $instance = null;

	public static function getInstance() {
		if (WPMLExtension::$instance == null) {
			WPMLExtension::$instance = new WPMLExtension;
		}
		return WPMLExtension::$instance;
	}

	// public function processExtension($data) {
	// 	global $uci_wpmlfunction_instance;
	// 	$result = $uci_wpmlfunction_instance->processExtensionFunction($data);
	// 	return $result;
	// }
	public function processExtension($data) {
	    $response = [];
        $import_type = $this->import_name_as($data);
        if($import_type == 'Posts' || $import_type =='Pages'){
        	$wpmlFields = array(
			'LANGUAGE_CODE' => 'language_code',
			'TRANSLATED_POST_TITLE' => 'translated_post_title');
		}
		if(!empty($wpmlFields)){
			$wpml_value = $this->convert_static_fields_to_array($wpmlFields);
			$response['wpml_fields'] = $wpml_value ;
			return $response;
		}else{
			return $response;
		}

	}

	/**
	 * WPML extension supported import types
	 * @param string $import_type - selected import type
	 * @return boolean
	 */
	public function extensionSupportedImportType($import_type){
		global $sitepress;
		global $wpdb;
		if(is_plugin_active('wpml-import/plugin.php')){
			return false;
		}
		if($sitepress != null) {

			if($import_type == 'nav_menu_item'){
				return false;
			}

			$get_custom_posts_wpml_sync_options = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'icl_sitepress_settings' ");
			$unser_custom_posts_wpml_sync_options = unserialize($get_custom_posts_wpml_sync_options);
			$get_available_posttypes = array_intersect($unser_custom_posts_wpml_sync_options['custom_posts_sync_option'], $unser_custom_posts_wpml_sync_options['taxonomies_sync_option']);
			$get_available_taxos = array_intersect($unser_custom_posts_wpml_sync_options['taxonomies_sync_option'], $unser_custom_posts_wpml_sync_options['custom_posts_sync_option']);

			$get_all_availabilities = array_merge($get_available_posttypes, $get_available_taxos);

			$import_types = $this->import_name_as($import_type);

			$post_type_array = array(
				'Posts' => 'post',
				'Pages' => 'page',
				//'WooCommerce Product' => 'product',
				//'WooCommerce' => 'product'
			);

			//if($import_types == 'Posts' || $import_types == 'Pages' || $import_types == 'WooCommerce' || $import_types =='Taxonomies' || $import_types =='Tags' || $import_types =='Categories' || $import_types =='CustomPosts' ) {
			if($import_types == 'Posts' || $import_types == 'Pages' ) {
				
				if(array_key_exists($import_type, $post_type_array)){
					$import_type = $post_type_array[$import_type];
				}

				if(array_key_exists($import_type, $get_all_availabilities)){
					return true;
				}
				else{
					return false;
				}
			}
			else{
				return false;
			}
		}
	}
}
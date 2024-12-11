<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class PodsExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {	
		if (PodsExtension::$instance == null) {
			PodsExtension::$instance = new PodsExtension;
		}
		return PodsExtension::$instance;
    }

	/**
	* Provides Pods mapping fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
		global $wpdb;
		$import_type = $data;
		$import_type = $this->import_type_as($import_type);
		$response = [];
		$podsFields = array();
		$import_type = $this->import_post_types($import_type);
		$post_id = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_name= %s and post_type = %s", $import_type, '_pods_pod'));
		if(empty($post_id) && $import_type == 'comments'){
            $post_id = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_name= %s and post_type = %s", 'comment', '_pods_pod'));
		}
		if(empty($post_id) && $import_type == 'Media'){
            $post_id = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_name= %s and post_type = %s", 'media', '_pods_pod'));
		}

		if(!empty($post_id)) {
			$lastId = $post_id[0]->ID;
			$get_pods_fields = $wpdb->get_results( $wpdb->prepare( "SELECT post_title, post_name FROM {$wpdb->prefix}posts where post_parent = %d AND post_type = %s", $lastId, '_pods_field' ) );
			if ( ! empty( $get_pods_fields ) ) :
				foreach ( $get_pods_fields as $pods_field ) {
					$podsFields["PODS"][ $pods_field->post_name ]['label'] = $pods_field->post_title;
					$podsFields["PODS"][ $pods_field->post_name ]['name']  = $pods_field->post_name;
				}
			endif;
		}
		$pods_value = $this->convert_fields_to_array($podsFields);
		$response['pods_fields'] = $pods_value;
		return $response;
			
	}

	/**
	* Pods extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
	public function extensionSupportedImportType($import_type){
		if(is_plugin_active('pods/init.php')){
			if($import_type == 'nav_menu_item'){
				return false;
			}
			$import_type = $this->import_name_as($import_type);
			if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'Taxonomies' || $import_type == 'Categories' || $import_type == 'Tags' || $import_type == 'event' || $import_type == 'event-recurring' || $import_type == 'location' || $import_type == 'Users' || $import_type == 'WooCommerce' || $import_type == 'WPeCommerce' || $import_type == 'Comments') {	
				return true;
			}
			if($import_type == 'ticket'){
				if(is_plugin_active('events-manager/events-manager.php')){
					return false;
				}else{
					return true;
				}
			}
			else{
				return false;
			}
		}
	}

	function import_post_types($import_type, $importAs = null) {	
		$import_type = trim($import_type);
		$module = array('Posts' => 'post', 'Pages' => 'page', 'Users' => 'user', 'Comments' => 'comments', 'Taxonomies' => $importAs, 'CustomerReviews' =>'wpcr3_review', 'Categories' => 'categories', 'Tags' => 'tags', 'WooCommerce' => 'product', 'WPeCommerce' => 'wpsc-product','WPeCommerceCoupons' => 'wpsc-product','WooCommerceVariations' => 'product', 'WooCommerceOrders' => 'product', 'WooCommerceCoupons' => 'product', 'WooCommerceRefunds' => 'product', 'CustomPosts' => $importAs);
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
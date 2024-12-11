<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class ACFExtension extends ExtensionHandler{
    private static $instance = null;

    public static function getInstance() {
		
		if (ACFExtension::$instance == null) {
			ACFExtension::$instance = new ACFExtension;
		}
		return ACFExtension::$instance;
	}
	
	/**
	* Provides default mapping fields for ACF Free plugin
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
	public function processExtension($data){
		$import_type = $data;		
		$response = [];
		$import_type = $this->import_type_as($import_type);
		$acf_pro_fields =  $this->ACFFields($import_type , 'ACF');
		$acf_group_fields=$this->ACFFields($import_type  , 'GF');
		if(!empty($acf_pro_fields)){
			$response['acf_fields'] = $acf_pro_fields;
		}
		if(!empty($acf_group_fields)){
			$response['acf_group_fields'] = null;
		}
		return $response;
	}

	/**
	* Retrieves ACF mapping fields
	* @param string $import_type - selected import type
	* @param string $group - ACF or GF
	* @return array - mapping fields
	*/
	public function ACFFields($import_type ,$group) {	
		global $wpdb;
		$group_id_arr = $customFields = $rep_customFields = array();	
		$get_acf_groups = $wpdb->get_results( $wpdb->prepare("SELECT ID, post_content from {$wpdb->prefix}posts where post_status != 'trash' and post_type = %s" , 'acf-field-group'));
		// Get available ACF group id
		foreach ( $get_acf_groups as $item => $group_rules ) {
			$rule = maybe_unserialize($group_rules->post_content);
			if(!empty($rule)) {
				if ($import_type != 'Users') {
					foreach($rule['location'] as $key => $value) {
						if($value[0]['operator'] == '==' && $value[0]['value'] == $this->import_post_types($import_type)){
							$group_id_arr[] = $group_rules->ID; #. ',';
						}
						elseif($value[0]['operator'] == '==' && $value[0]['value'] == 'all' && $value[0]['param'] == 'taxonomy' && in_array($this->import_post_types($import_type) , get_taxonomies())){
							$group_id_arr[] = $group_rules->ID;
						}
					}
				} else {
					foreach($rule['location'] as $key => $value) {
						if( $value[0]['operator'] == '==' && $value[0]['param'] == 'user_role' || $value[0]['param'] == 'current_user'){
							$group_id_arr[] = $group_rules->ID;
						}
					}
				}
			}
		}
		if ( !empty($group_id_arr) ) {
			foreach($group_id_arr as $groupId) {
				//$get_acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_content, post_excerpt, post_name FROM {$wpdb->prefix}posts where post_status != %s AND post_parent in (%s)",'trash' , array($groupId) ) );	
				$get_acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_content, post_excerpt, post_name FROM {$wpdb->prefix}posts where post_status != 'trash' AND post_parent in (%s)", array($groupId) ) );			
			
				if ( ! empty( $get_acf_fields ) ) {	
					$group_field_arr = array();						
					foreach ( $get_acf_fields as $acf_pro_fields ) {
						$get_field_content = unserialize( $acf_pro_fields->post_content );	
						if ( $get_field_content['type'] == 'group' ) {
							$group_field_arr[] = $acf_pro_fields->ID . ",";		
							foreach($group_field_arr as $group_field){		
							//$get_sub_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_content, post_excerpt, post_name FROM {$wpdb->prefix}posts where post_status != %s AND post_parent in (%s)", 'trash',array($group_field) ) );	
							$get_sub_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_content, post_excerpt, post_name FROM {$wpdb->prefix}posts where post_status != 'trash' AND post_parent in (%s)",array($group_field) ) );			
							foreach ( $get_sub_fields as $get_sub_key ) {
									$get_sub_field_content = unserialize( $get_sub_key->post_content );	
										
									if ( $get_sub_field_content['type'] == 'group' ) {
										$group_field_arr[] = $get_sub_key->ID . ",";
									}
								}
							}
									
						} 					
						 elseif ( $acf_pro_fields->post_excerpt != null || $acf_pro_fields->post_excerpt != '' ) {
							if($get_field_content['type'] !== 'group' && $get_field_content['type'] !== 'message' && $get_field_content['type'] !== 'tab' && $get_field_content['type'] !== 'image' && $get_field_content['type'] !== 'file' && $get_field_content['type'] !== 'wysiwyg' && $get_field_content['type'] !== 'oembed' && $get_field_content['type'] !== 'link' && $get_field_content['type'] !== 'page_link'){
								if($get_field_content['type'] == 'select'){
									if($get_field_content['multiple'] == 0){
										$customFields["ACF"][ $acf_pro_fields->post_name ]['label'] = $acf_pro_fields->post_title;
										$customFields["ACF"][ $acf_pro_fields->post_name ]['name']  = $acf_pro_fields->post_excerpt;	
									}
								}
								else{
									$customFields["ACF"][ $acf_pro_fields->post_name ]['label'] = $acf_pro_fields->post_title;
									$customFields["ACF"][ $acf_pro_fields->post_name ]['name']  = $acf_pro_fields->post_excerpt;	
								}
								
							}
						}
					}
				}
				
				$group_fields = $group_field_arr ;		
				$group_field_placeholders = array_fill(0, count($group_fields), '%s');	
				
				$placeholdersForGroupFields = implode(', ', $group_field_placeholders);	
			
				if ( ! empty( $group_field_arr ) ) {
					$query1 = "SELECT ID, post_title, post_content, post_excerpt, post_name FROM {$wpdb->prefix}posts where post_status != 'trash' AND post_parent in ($placeholdersForGroupFields)";
					
					$get_acf_group_fields = $wpdb->get_results( $wpdb->prepare( $query1, $group_fields ) );	
					
				}
				
				if ( ! empty( $get_acf_group_fields ) ) {
					foreach ( $get_acf_group_fields as $acf_pro_group_fields ) {
						$get_sub_field_content = unserialize( $acf_pro_group_fields->post_content );
							$rep_customFields[ $acf_pro_group_fields->post_title ] = $acf_pro_group_fields->post_excerpt;
							$check_exist_key = "ACF: " . $acf_pro_group_fields->post_title;	
							if ( array_key_exists( $check_exist_key, $customFields ) ) {
								unset( $customFields[ $check_exist_key ] );
							}	
							if($get_sub_field_content['type'] !== 'group'){
								$customFields["GF"][ $acf_pro_group_fields->post_name ]['label'] = $acf_pro_group_fields->post_title;
								$customFields["GF"][ $acf_pro_group_fields->post_name ]['name']  = $acf_pro_group_fields->post_excerpt;
							}
					}
				}
				
			}
		}
		$requested_group_fields = array();
	
		if(!empty($customFields[$group]))
			$requested_group_fields[$group] = $customFields[$group];
			$acf_pro_value = $this->convert_fields_to_array($requested_group_fields);
		
			return $acf_pro_value;		
	}
	/**
	* ACF extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
	public function extensionSupportedImportType($import_type){
		if(is_plugin_active('advanced-custom-fields/acf.php')){
			if($import_type == 'nav_menu_item'){
				return false;
			}

			$import_type = $this->import_name_as($import_type);
			if($import_type =='Posts' || $import_type =='Pages' || $import_type =='CustomPosts' || $import_type =='Users' || $import_type =='WooCommerce' || $import_type =='Taxonomies' || $import_type =='Tags' || $import_type =='Categories') {	
				return true;
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
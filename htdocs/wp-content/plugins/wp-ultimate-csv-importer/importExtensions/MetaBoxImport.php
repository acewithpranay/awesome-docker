<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class MetaBoxImport {
	private static $metabox_instance = null, $media_instance;

	public static function getInstance() {

		if (MetaBoxImport::$metabox_instance == null) {
			MetaBoxImport::$metabox_instance = new MetaBoxImport;
			MetaBoxImport::$media_instance = new MediaHandling();
			return MetaBoxImport::$metabox_instance;
		}
		return MetaBoxImport::$metabox_instance;
	}

	function set_metabox_values($line_number,$header_array ,$value_array , $map, $post_id , $type,$hash_key){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();	
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);

		$this->metabox_import_function($line_number,$post_values, $post_id , $header_array , $value_array, $type,$hash_key);
	}

	public function metabox_import_function ($line_number,$data_array, $pID, $header_array, $value_array, $type,$hash_key) {

		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$media_instance = MediaHandling::getInstance();
		$extension_object = new ExtensionHandler;
		$import_as = $extension_object->import_post_types($type );

		$listTaxonomy = get_taxonomies();
		$get_metabox_fields = \rwmb_get_object_fields( $import_as ); 

		foreach($data_array as $data_key => $data_value){
			if(str_contains($data_key,"checkbox_list_")){
				$checkbox_list_fields = explode(',', $data_value);
				$existing_values = get_post_meta($pID, $data_key, false);

				if (empty($existing_values)) {
					foreach($checkbox_list_fields as $data_val){
						add_user_meta($pID, $data_key, $data_val);
					}
				} else {
					delete_user_meta($pID, $data_key);

					foreach($checkbox_list_fields as $data_val){
						add_user_meta($pID, $data_key, $data_val);
					}
				}
			}


			$field_type = $get_metabox_fields[$data_key]['type'];
			$check_for_multiple = isset($get_metabox_fields[$data_key]['multiple']) ? $get_metabox_fields[$data_key]['multiple'] : '';

			if($field_type == 'text_list' || $field_type == 'select' || $field_type == 'select_advanced'){
				$get_text_list_fields = explode(',', $data_value);
				foreach($get_text_list_fields as $text_list_fields){
					if($check_for_multiple){
						add_post_meta($pID, $data_key, $text_list_fields);
					}
					else{
						update_post_meta($pID, $data_key, $text_list_fields);
					}
				}
			}
			elseif($field_type == 'checkbox_list'){
				$get_checkbox_list_fields = explode(',', $data_value);
				$existing_values = get_post_meta($pID, $data_key, false);
				if (empty($existing_values)) {
					foreach ($get_checkbox_list_fields as $checkbox_list_fields) {
						add_post_meta($pID, $data_key, $checkbox_list_fields);
					}
				} else {
					delete_post_meta($pID, $data_key);

					foreach ($get_checkbox_list_fields as $checkbox_list_fields) {
						add_post_meta($pID, $data_key, $checkbox_list_fields);
					}
				}
			}
			elseif($field_type == 'fieldset_text'){
				$get_fieldset_text_fields = explode(',', $data_value);
				$get_fieldset_options = $get_metabox_fields[$data_key]['options'];

				$temp = 0;
				$fieldset_array = [];
				foreach($get_fieldset_options as $fieldset_key => $fieldset_options){
					$fieldset_array[$fieldset_key] = $get_fieldset_text_fields[$temp];
					$temp++;
				}

				update_post_meta($pID, $data_key, $fieldset_array);
			}
			elseif($field_type == 'image' || $field_type == 'file' || $field_type == 'file_advanced' || $field_type == 'image_advanced'){
				$get_uploads_fields = explode(',', $data_value);
				$get_fields_count = count($get_uploads_fields);
				$indexs = 0;
				foreach($get_uploads_fields as $uploads_fields){
					MetaBoxImport::$media_instance->store_image_ids($i=1); 
					$attachmentId = MetaBoxImport::$media_instance->image_meta_table_entry($line_number ,'', $pID ,$data_key, $uploads_fields, $hash_key,'metabox',$type,'','',$header_array, $value_array,'','',$indexs);
					if($get_fields_count > 1){
						add_post_meta($pID, $data_key, $attachmentId);	
					}
					else{
						update_post_meta($pID, $data_key, $attachmentId);	
					}
					$indexs++;
				}	
			}
			elseif($field_type == 'video'){
				$media_fd = explode(',',$data_value);
				$media_arr = array();
				foreach($media_fd as $data){
					if(is_numeric($data)){
						$media_arr[] = $data;
					}
					else {
						$attachmentId = MetaBoxImport::$media_instance->media_handling($data, $pID);
						if($attachmentId)
							$media_arr[] = $data;
					}
				}	
				$media_arr = implode(',',$media_arr);
				update_post_meta($pID, $data_key, $media_arr);			
			}
			elseif($field_type == 'file_input'){
				$attachmentId = MetaBoxImport::$media_instance->media_handling($data_value, $pID);

				$get_file_url = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND ID = $attachmentId");
				update_post_meta($pID, $data_key, $get_file_url);
			}
			elseif($field_type == 'password'){
				$data_value = wp_hash_password($data_value);
				update_post_meta($pID, $data_key, $data_value);
			}
			elseif($field_type == 'post' || $field_type == 'user' || $field_type == 'taxonomy'){
				if(is_numeric($data_value)){
					update_post_meta($pID, $data_key, $data_value);
				}
				else{
					if($field_type == 'post'){
						$get_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$data_value' AND post_status != 'trash' ");
					}
					elseif($field_type == 'user'){
						$get_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$data_value' ");
					}
					elseif($field_type == 'taxonomy'){
						//$get_post_id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$data_value' ");
						$term_fd = explode('|',$data_value);
						foreach($term_fd as $value){						
							$taxonomy = $get_metabox_fields[$data_key]['taxonomy']['0'];
							$get_post_id = $wpdb->get_var("SELECT tax.term_taxonomy_id FROM {$wpdb->prefix}terms t INNER JOIN {$wpdb->prefix}term_taxonomy tax ON t.term_id=tax.term_id WHERE t.name='$value' AND tax.taxonomy='$taxonomy'");
							$wpdb->get_results("INSERT into {$wpdb->prefix}term_relationships (`object_id`,`term_taxonomy_id`) VALUES($pID,$get_post_id)");

						}
					}
					update_post_meta($pID, $data_key, $get_post_id);
				}	
			}
			else{

				if(in_array($type, $listTaxonomy)){ //term module
				
					foreach($data_array as $data_key => $data_value){
						update_term_meta($pID,$data_key,$data_value);
				    }

				update_post_meta($pID, $data_key, $data_value);
				}
			}
		}
	}
}

<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class TaxonomiesImport {
    private static $taxonomies_instance = null;

    public static function getInstance() {
        
        if (TaxonomiesImport::$taxonomies_instance == null) {
            TaxonomiesImport::$taxonomies_instance = new TaxonomiesImport;
            return TaxonomiesImport::$taxonomies_instance;
        }
        return TaxonomiesImport::$taxonomies_instance;
    }
    public function taxonomies_import_function ($data_array, $mode, $importType , $unmatched_row, $check , $unikey_value , $unikey_name , $line_number ,$header_array ,$value_array) {
		$returnArr = array();
		$mode_of_affect = 'Inserted';
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		$media_instance = MediaHandling::getInstance();
		global $core_instance;

		$log_table_name = $wpdb->prefix ."import_detail_log";
		$events_table = $wpdb->prefix."em_meta" ;

		$updated_row_counts = $helpers_instance->update_count($unikey_value , $unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];
		
		$terms_table = $wpdb->term_taxonomy;
        //$taxonomy = $importAs;
        $taxonomy = $importType;
		
		$term_children_options = get_option("$taxonomy" . "_children");
		$_name = isset($data_array['name']) ? $data_array['name'] : '';
		$_slug = isset($data_array['slug']) ? $data_array['slug'] : '';
		$_desc = isset($data_array['description']) ? $data_array['description'] : '';
		$_image = isset($data_array['image']) ? $data_array['image'] : '';
		$_parent = isset($data_array['parent']) ? $data_array['parent'] : '';
		$_display_type = isset($data_array['display_type']) ? $data_array['display_type'] : '';
		$_color = isset($data_array['color']) ? $data_array['color'] : '';
		$_top_content = isset($data_array['top_content']) ? $data_array['top_content'] : '';
		$_bottom_content = isset($data_array['bottom_content']) ? $data_array['bottom_content'] : '';

		$get_category_list = array();
		// if (strpos($_name, ',') !== false) {
		// 	$get_category_list = explode(',', $_name);
		// }
		 if (strpos($_name, '>') !== false) {
			$get_category_list = explode('>', $_name);
		} else {
			$get_category_list[] = trim($_name);
		}

		$parent_term_id = 0;
		$termID = '';
	
		if (count($get_category_list) == 1) {
			$_name = trim($get_category_list[0]);
			if($_parent){
				$get_parent = term_exists("$_parent", "$taxonomy");
				$parent_term_id = $get_parent['term_id'];
			}
			else{
				// $termid_value = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms WHERE slug = '$_slug'");
				$termid_value = $wpdb->get_results($wpdb->prepare("SELECT term.term_id FROM {$wpdb->prefix}terms AS term INNER JOIN {$wpdb->prefix}term_taxonomy AS tax ON term.term_id = tax.term_id WHERE term.slug = %s AND tax.taxonomy = %s", $_slug, $taxonomy));
				if(isset($termid_value[0]->term_id)){
					$termid_val = $termid_value[0]->term_id;
					$term_parent_value = $wpdb->get_results("SELECT parent FROM {$wpdb->prefix}term_taxonomy WHERE term_id = '$termid_val'");
					$parent_term_id = $term_parent_value[0]->parent;
				}
			}
		} else {
			$count = count($get_category_list);
			$_name = trim($get_category_list[$count - 1]);
			$checkParent = trim($get_category_list[$count - 2]);
			$parent_term = term_exists("$checkParent", "$taxonomy");
			$parent_term_id = $parent_term['term_id'];
		}
		if($check == 'termid'){
			$termID = $data_array['TERMID'];
		}
		if($check == 'slug'){
			$get_termid = get_term_by( "slug" ,"$_slug" , "$taxonomy");
			$termID = $get_termid->term_id;
		}	
		if($_display_type){
			$_display_type = $_display_type;
		}else{
			// $term_id_value = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms WHERE slug = '$_slug'");
			$term_id_value =$wpdb->get_results($wpdb->prepare("SELECT term.term_id FROM {$wpdb->prefix}terms AS term INNER JOIN {$wpdb->prefix}term_taxonomy AS tax ON term.term_id = tax.term_id WHERE term.slug = %s AND tax.taxonomy = %s", $_slug, $taxonomy));
			if(isset($term_id_value[0]->term_id)){
				$term_id_val = $term_id_value[0]->term_id;
				// $term_display_type_value = $wpdb->get_results("SELECT display_type FROM {$wpdb->prefix}termmeta WHERE term_id = '$term_id_val'");
				// $_display_type = $term_display_type_value[0]->display_type;
				$term_display_type_value = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}termmeta WHERE term_id = '$term_id_val' AND meta_key = 'display_type' ");
				
				if(!empty($term_display_type_value)){
					$_display_type = $term_display_type_value[0]->meta_value;
				}
			}
		}
		if($mode == 'Insert'){
			if(!empty($termID)){

				$core_instance->detailed_log[$line_number]['Message'] = "Skipped, Due to duplicate Term found!.";
				$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
				return array('MODE' => $mode, 'ERROR_MSG' => 'The term already exists!');

			}else{
				
					$taxoID = wp_insert_term("$_name", "$taxonomy", array('description' => $_desc, 'slug' => $_slug));
					if(is_wp_error($taxoID)){
						$core_instance->detailed_log[$line_number]['Message'] = "Can't insert this " . $taxonomy . ". " . $taxoID->get_error_message();
						$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
						$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
						$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
					}else{

						$termID= $taxoID['term_id'];
			        	$date = date("Y-m-d H:i:s");

						if(isset($_image)){
							$media_instance->store_image_ids($i=1);
							$imageid = $media_instance->media_handling($_image , $termID ,$data_array ,'','','',$header_array ,$value_array);
							//$imageid = $media_instance->image_meta_table_entry('', $termID, 'thumbnail_id', $_image, $hash_key, 'term', 'term',$templatekey);
							
							if($importType == 'product_cat'){
								add_term_meta($termID , 'thumbnail_id' , $imageid); 
							}
						}
						if(isset($_display_type)){
							add_term_meta($termID , 'display_type' , $_display_type);
						}


						if(isset($parent_term_id)){
							$update = $wpdb->get_results("UPDATE $terms_table SET `parent` = $parent_term_id WHERE `term_id` = $termID ");
						}	
						$returnArr = array('ID' => $termID, 'MODE' => $mode_of_affect);
					
						$core_instance->detailed_log[$line_number]['Message'] = 'Inserted ' . $taxonomy . ' ID: ' . $termID;
						$core_instance->detailed_log[$line_number]['state'] = 'Inserted';
						$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
						$core_instance->detailed_log[$line_number]['id'] = $termID;
						$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey_value'");
					}
			}
			if($unmatched_row == 'true'){
				global $wpdb;
				$post_entries_table = $wpdb->prefix ."post_entries_table";
				$file_table_name = $wpdb->prefix."smackcsv_file_events";
				$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE $unikey_name = '$unikey_value'");	
				$file_name = $get_id[0]->file_name;
				
				$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$termID}','{$type}', '{$file_name}','Inserted')");
			}

		 } 
       
		if(!is_wp_error($termID)) {
			update_option("$taxonomy" . "_children", $term_children_options);
			delete_option($taxonomy . "_children");
		}
		return $returnArr;
    }
}
    
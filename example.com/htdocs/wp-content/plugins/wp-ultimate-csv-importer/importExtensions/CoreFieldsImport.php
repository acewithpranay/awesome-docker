<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

use Smackcoders\FCSV\MediaImport;
use Smackcoders\WCSV\WooCommerceCoreImport;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class CoreFieldsImport {
	private static $core_instance = null,$media_instance;
	public $detailed_log;
	public $media_log,$failed_media_data;
	public $generated_content;
	public $openAI_response=array();
	public static function getInstance() {

		if (CoreFieldsImport::$core_instance == null) {
			CoreFieldsImport::$core_instance = new CoreFieldsImport;
			CoreFieldsImport::$media_instance = new MediaHandling;
			return CoreFieldsImport::$core_instance;
		}
		return CoreFieldsImport::$core_instance;
	}

	function set_core_values($header_array ,$value_array , $map , $type , $mode , $line_number , $check , $hash_key, $unmatched_row, $gmode, $templatekey, $wpml_array = null,$media_meta=null,$media_type=null,$order_meta=null){
		global $wpdb;
		global $uci_woocomm_instance,$woocommerce_core_instance;
		global $userimp_class;
		global $sitepress;
		$post_id = null;

		$helpers_instance = ImportHelpers::getInstance();
		CoreFieldsImport::$media_instance->header_array = $header_array;
		CoreFieldsImport::$media_instance->value_array = $value_array;
		$log_table_name = $wpdb->prefix ."import_detail_log";
		$media_handle = get_option('smack_image_options');
		$unikey_name = 'hash_key';
		$unikey_value = $hash_key;

		if($gmode == 'CLI'){ //Exchange the hashkey value with template key
			$unikey_name = 'templatekey';
			$unikey_value = ($templatekey != null) ? $templatekey : '';
		}	

		$updated_row_counts = $helpers_instance->update_count($unikey_value,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$skipped_count = $updated_row_counts['skipped'];

		$taxonomies = get_taxonomies();
		if (in_array($type, $taxonomies)) {
			$get_import_type = 'term';
		}elseif ($type == 'Users') {
			$get_import_type = 'user';
		}elseif ($type == 'Comments') {
			$get_import_type = 'comment';
		}elseif ($type == 'Media') {
			$get_import_type = 'Media';
		} 
		 else {	
			$get_import_type = 'post';
		}
		if (in_array($type, $taxonomies)) {
			$import_type = $type;
			if($import_type == 'category' || $import_type == 'product_category' || $import_type == 'product_cat' || $import_type == 'wpsc_product_category' || $import_type == 'event-categories'):
				$type = 'Categories';
			elseif($import_type == 'product_tag' || $import_type == 'event-tags' || $import_type == 'post_tag'):
				$type = 'Tags';
			else:
			$type = 'Taxonomies';
		endif;
		}
		if($type == 'elementor_library'){
			$elementor_import=new ElementorImport;
			$elementor_import->set_elementor_values($header_array ,$value_array , $map, $post_id , $type, $mode, $line_number , $hash_key);
			wp_die();
		}
		if(!empty($media_meta)){
			if($media_handle['media_settings']['media_handle_option'] == 'true'){
				$post_values = $helpers_instance->get_header_values($media_meta , $header_array , $value_array);
				$media_handle['media_settings']['title'] = isset($post_values['featured_image_title']) ? $post_values['featured_image_title'] : '';
    			$media_handle['media_settings']['caption'] = isset($post_values['featured_image_caption']) ? $post_values['featured_image_caption'] : '';
    			$media_handle['media_settings']['alttext'] = isset($post_values['featured_image_alt_text']) ? $post_values['featured_image_alt_text'] : '';
    			$media_handle['media_settings']['description'] = isset($post_values['featured_image_description']) ? $post_values['featured_image_description'] : '';
				$media_handle['media_settings']['file_name'] = isset($post_values['featured_file_name']) ? $post_values['featured_file_name'] : '';
			
				update_option('smack_image_options', $media_handle);
				$media_handle = get_option('smack_image_options');
			}
		}
		
		if(($type == 'WooCommerce Product') || ($type == 'WooCommerce Coupons') || ($type == 'WooCommerce Orders') || ($type == 'WooCommerce Reviews') || ($type == 'WooCommerce Product Variations')|| ($type == 'Categories') || ($type == 'Tags') || ($type == 'Taxonomies') || ($type == 'Comments') || ($type == 'Users') || ($type == 'Customer Reviews') || ($type == 'lp_order') || ($type == 'nav_menu_item') || ($type == 'widgets') || ($type == 'Media')){

			$comments_instance = CommentsImport::getInstance();
			$customer_reviews_instance = CustomerReviewsImport::getInstance();
			$learnpress_instance = LearnPressImport::getInstance();
			$taxonomies_instance = TaxonomiesImport::getInstance();
			$woocommerce_core_instance = WooCommerceCoreImport::getInstance();
			$media_core_instance = MediaImport::getInstance();
			$post_values = [];
			$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
			$wpml_values = $helpers_instance->get_header_values($wpml_array , $header_array , $value_array);
			if($type == 'WooCommerce Product'){
				$result = $uci_woocomm_instance->woocommerce_product_import($post_values , $mode , $check , $unikey_value , $unikey_name , $hash_key, $line_number, $unmatched_row, $wpml_values);
			}
			if($type == 'Media'){
				$result = $media_core_instance->media_fields_import($post_values , $mode , $type , $media_type ,$unikey_value ,$unikey_name, $line_number,$hash_key,$header_array ,$value_array);
			}
			if($type == 'WooCommerce Orders'){
				$order_meta_data = $helpers_instance->get_header_values($order_meta,$header_array,$value_array);
				$result = $uci_woocomm_instance->woocommerce_orders_import($post_values , $mode , $check , $unikey_value ,$unikey_name, $line_number,$order_meta_data,'');
			}
			if($type == 'WooCommerce Product Variations'){
				$result = $uci_woocomm_instance->woocommerce_variations_import($post_values , $mode , $check ,$unikey_value ,  $unikey_name, $line_number, $variation_count =null);
			}
			if($type == 'WooCommerce Coupons'){
				$result = $uci_woocomm_instance->woocommerce_coupons_import($post_values , $mode , $check , $unikey_value , $unikey_name, $line_number);
			}
			if($type == 'WooCommerce Refunds'){
				$result = $woocommerce_core_instance->woocommerce_refunds_import($post_values , $mode , $check , $unikey_value , $unikey_name, $line_number);
			}
			if(($type == 'Categories') || ($type == 'Tags') || ($type == 'Taxonomies')){
				$result = $taxonomies_instance->taxonomies_import_function($post_values , $mode , $import_type , $unmatched_row, $check , $unikey_value , $unikey_name ,$line_number ,$header_array ,$value_array);
			}
			if($type == 'Users'){
				$result = $userimp_class->users_import_function($post_values , $mode ,$unikey_value , $line_number);
			}
			if($type == 'Comments' || $type == 'WooCommerce Reviews'){
				$result = $comments_instance->comments_import_function($post_values , $mode ,$unikey_value , $unikey_name , $line_number, $type);
			}
			if($type == 'Customer Reviews'){
				$result = $customer_reviews_instance->customer_reviews_import($post_values , $mode , $check ,$unikey_value , $line_number);
			}
			if($type == 'lp_order'){
				$result = $learnpress_instance->learnpress_orders_import($post_values , $mode ,$unikey_value , $unikey_name , $line_number);
			}
			if($type == 'nav_menu_item'){
				$comments_instance->menu_import_function($post_values , $mode ,$unikey_value , $unikey_name, $line_number);
			}
			if($type == 'widgets'){
				$comments_instance->widget_import_function($post_values , $mode ,$unikey_value , $unikey_name , $line_number);
			}
			$last_import_id = isset($result['ID']) ? $result['ID'] : '';
			$post_id = isset($result['ID']) ? $result['ID'] :'';
			if($gmode != 'CLI')
			$helpers_instance->get_post_ids($post_id ,$hash_key);

			if((isset($post_values['featured_image'])) && !is_plugin_active('featured-image-from-url/featured-image-from-url.php')) {	
				if (strpos($post_values['featured_image'], '|') !== false) {
					$featured_img = explode('|', $post_values['featured_image']);
					$featured_image=$featured_img[0];					
				}
				else if (strpos($post_values['featured_image'], ',') !== false) {
					$feature_img = explode(',', $post_values['featured_image']);
					$featured_image=$feature_img[0];
				}
				else{
					$featured_image=$post_values['featured_image'];
				}
				if ( preg_match_all( '/\b[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $featured_image, $matchedlist, PREG_PATTERN_ORDER ) ) {	
					$image_type = 'Featured'; 
					CoreFieldsImport::$media_instance->store_image_ids($i=1); 
					$attach_id = CoreFieldsImport::$media_instance->image_meta_table_entry($line_number,$post_values,$post_id ,'',$featured_image, $hash_key ,$image_type,$type,'','',$header_array,$value_array);
					//$attach_id = CoreFieldsImport::$media_instance->media_handling( $featured_image , $post_id ,$post_values,$type,$image_type,$hash_key,$templatekey,$header_array,$value_array);	
				}
			}
			$this->detailed_log[$line_number]['Message'] = isset($this->detailed_log[$line_number]['Message']) ? $this->detailed_log[$line_number]['Message'] : '';
			if (preg_match("/(Can't|Skipped|Duplicate)/", $this->detailed_log[$line_number]['Message']) === 0) { 	
				if ( $type == 'WooCommerce Product') {
					if(isset($result['post_type']) && ($result['post_type'] =='product_variation')){
						$this->detailed_log[$line_number]['webLink'] = get_permalink( $post_id );
						$this->detailed_log[$line_number]['id'] = $post_id ;
						$this->detailed_log[$line_number]['post_type'] = get_post_type($post_id);
						$this->detailed_log[$line_number]['post_title'] = get_the_title($post_id);
					}
					else{
						$this->detailed_log[$line_number]['webLink'] = get_permalink( $post_id );
						$this->detailed_log[$line_number]['id'] = $post_id ;
						$this->detailed_log[$line_number]['adminLink'] = get_edit_post_link( $post_id, true );
						$this->detailed_log[$line_number]['post_type'] = get_post_type($post_id);
						$this->detailed_log[$line_number]['post_title'] = get_the_title($post_id);
					}
				}
				elseif($type == 'WooCommerce Coupons'){
					$this->detailed_log[$line_number]['adminLink'] = get_edit_post_link( $post_id, true );
					$this->detailed_log[$line_number]['post_type'] = get_post_type($post_id);
					$this->detailed_log[$line_number]['post_title'] = get_the_title($post_id);
				}
				elseif( $type == 'Media'){
					if (!empty($post_id)) {
						$file_path = get_attached_file($post_id);
						$this->detailed_log[$line_number]['FileName'] = basename($file_path);;
						$this->detailed_log[$line_number]['attach_ID'] = $post_id;
						$this->detailed_log[$line_number]['adminLink'] = get_edit_post_link( $post_id , true );
						$image_url = wp_get_attachment_url($post_id);
						$this->detailed_log[$line_number]['post_type'] = 'Media';
						$this->detailed_log[$line_number]['webLink'] = $image_url;
						return $post_id;
					}
				}
				elseif( $type == 'Users'){
					if (!empty($post_id)) {
					$this->detailed_log[$line_number]['webLink'] = get_author_posts_url( $post_id );
					$this->detailed_log[$line_number]['post_type'] = 'users';
					$this->detailed_log[$line_number]['id'] = $post_id;
					$this->detailed_log[$line_number]['adminLink'] = get_edit_user_link( $post_id , true );
					}
				}
				elseif($type == 'Tags' || $type == 'Categories' || $type == 'post_tag' || $type =='Post_category'|| $type == 'Taxonomies'){
					if (!empty($post_id)) {
						$this->detailed_log[$line_number]['adminLink'] = get_edit_term_link( $post_id, $import_type );
						$this->detailed_log[$line_number]['post_type'] = $type;
						$term_link = get_term_link($post_id, $import_type);
						if (!is_wp_error($term_link)) {
							$this->detailed_log[$line_number]['webLink'] = $term_link;
						}
					}
				}
				elseif($type == 'lp_order'){
					if (!empty($post_id)) {
						$this->detailed_log[$line_number]['adminLink'] = get_edit_post_link( $post_id, true );
						$this->detailed_log[$line_number]['post_type'] = get_post_type($post_id);
						$this->detailed_log[$line_number]['post_title'] = get_the_title($post_id);
						$this->detailed_log[$line_number]['id'] = $post_id ;
					}
				}
				elseif($type=='WooCommerce Orders'){
					$this->detailed_log[$line_number]['adminLink'] = $this->get_order_url($post_id) ;
					$this->detailed_log[$line_number]['post_type'] = get_post_type($post_id);
					$order = wc_get_order($post_id);
					if ($order) {
						$order_key = $order->get_order_key();
						$order_id = $order->get_id();
						$customer_order_view_link = wc_get_endpoint_url('view-order', $order_id, wc_get_page_permalink('myaccount')) . '?key=' . $order_key;
						$this->detailed_log[$line_number]['webLink'] = $customer_order_view_link;
					}
				}
				elseif($type == 'WooCommerce Product Variations' ){
					if (!empty($post_id)) {
					$post_values['post_title']=isset($post_values['post_title'])?$post_values['post_title']:'';
					$this->detailed_log[$line_number]['webLink'] = get_permalink( $post_id );
					$this->detailed_log[$line_number]['id'] = $post_id ;
					$this->detailed_log[$line_number]['post_type'] = get_post_type($post_id);
					$this->detailed_log[$line_number]['post_title'] = get_the_title($post_id);
					}
				}
				elseif($type == 'nav_menu_item' && isset($post_values['post_title'])){
					if (!empty($post_id)) {
						//$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $post_id, true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";
						$this->detailed_log[1]['Message'] = "Imported Successfully.";
						$menu_item_object_id = get_post_meta($post_id, '_menu_item_object_id', true);
						$this->detailed_log[$line_number]['id'] = $post_id ;
						$this->detailed_log[$line_number]['webLink'] = isset($menu_item_object_id) ? get_permalink($menu_item_object_id) : null;
						$this->detailed_log[$line_number]['adminLink'] = isset($menu_item_object_id) ?  get_edit_post_link($menu_item_object_id, true) : null;
					}
				}
				elseif($type == 'Comments' || $type == 'WooCommerce Reviews'   ){
					if (!empty($post_id)) {
						$this->detailed_log[$line_number]['webLink'] = get_comment_link( $post_id );
						$this->detailed_log[$line_number]['adminLink'] = get_edit_comment_link( $post_id );
						$this->detailed_log[$line_number]['id'] = $post_id ;
						$this->detailed_log[$line_number]['post_type'] = 'Comment' ;
						if($type == 'WooCommerce Reviews'){
							$this->detailed_log[$line_number]['post_type'] = 'Reviews' ;
							$this->detailed_log[$line_number]['adminLink'] = admin_url('comment.php?action=editcomment&c=' . $post_id);					
						}
						$comment = get_comment($post_id);
						$comment_post_id = isset($comment->comment_post_ID) ? $comment->comment_post_ID : null;
						$post_type = isset($comment_post_id) ? get_post_type($comment_post_id) : null;
						// $this->detailed_log[$line_number]['post_type'] = isset($post_type) ? $post_type : 'Comment';
					}					
				}
				if(isset($post_values['post_status'])){

					$this->detailed_log[$line_number]['Status'] = $post_values['post_status'];
				}	
			}
			return $post_id;

		}
		global $wpdb;
		$optional_type = '';
		if(is_plugin_active('jet-engine/jet-engine.php')){
			$get_slug_name = $wpdb->get_results("SELECT slug FROM {$wpdb->prefix}jet_post_types WHERE status = 'content-type'");
			foreach($get_slug_name as $key=>$get_slug){
				$value=$get_slug->slug;
				$optionaltype=$value;						
				if($optionaltype == $type){
					$optional_type=$optionaltype;
				}
			}
		}
		if($optional_type == $type){
			if($gmode != 'CLI'){
			$current_user = wp_get_current_user();									
			$author_id = $current_user->data->ID;
			}
			else { // else part is only used for set the author id
				$post_values = [];				
				$post_values = $this->import_core_fields($post_values);
				$author_id = isset($post_values['post_author']) ? $post_values['post_author'] : "";
			}
			
			if($mode == 'Insert'){
				$post_values = [];
				$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array, $hash_key);
				
				$post_values = $this->import_core_fields($post_values);
				
				$table_name = 'jet_cct_'.$type;												
				$value_status =  empty($post_values['cct_status']) ? "publish" : $post_values['cct_status'] ;
				if($author_id)
					$wpdb->get_results("INSERT INTO {$wpdb->prefix}$table_name(cct_status,cct_author_id) values('$value_status',$author_id)");       			
				else 
					$wpdb->get_results("INSERT INTO {$wpdb->prefix}$table_name(cct_status) values('$value_status')");       			
				$get_result =  $wpdb->get_results("SELECT _ID FROM {$wpdb->prefix}$table_name WHERE  cct_status = '$value_status' order by _ID DESC ");			
				$id = $get_result[0];				
				$post_id = $id->_ID;
		
				$page = 'jet-cct-'.$type;
				$dir=site_url().'/wp-admin';				
				$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey_value'");
				$cct_post_title = isset($post_values['post_title']) ? $post_values['post_title'] : '';

				$this->detailed_log[$line_number]['Message'] = 'Inserted Custom Content Type '  . ' ID: ' . $post_id ;
				//$this->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='$dir/admin.php?page=$page&cct_action=edit&item_id=$post_id' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $cct_post_title ) ) . "'rel='permalink'>Admin View</a>";	
				$this->detailed_log[$line_number]['id'] = $post_id;
				$this->detailed_log[$line_number]['state'] = 'Inserted';
				$this->detailed_log[$line_number]['adminLink'] = "$dir/admin.php?page=$page&cct_action=edit&item_id=$post_id";
				$this->detailed_log[$line_number]['webLink'] = get_permalink($post_id);
				$this->detailed_log[$line_number]['status'] = $value_status;
				if($unmatched_row == 'true'){
					global $wpdb;
					$type ='cct';
					$post_entries_table = $wpdb->prefix ."ultimate_post_entries";
					$file_table_name = $wpdb->prefix."smackcsv_file_events";
					$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `hash_key` = '$hash_key'");	
					$file_name = $get_id[0]->file_name;
					$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$post_id}','{$type}', '{$file_name}','Inserted')");
				}			
				return $post_id;
			}
		}
		else{
			$current_user = wp_get_current_user();
		    $current_user_role = $current_user->roles[0];
			if($current_user_role == 'administrator'){
			$post_values = [];
			$get_result = null;
			$post_values['post_content'] = '';
			$map = $this->filterNumKeys($map);
			$map=$this->replaceValues($map);
			
			$trim_content = array(
				'->static' => '', 
				'->math' => '', 
				'->cus1' => '',
				'->openAI' => '', 
			);

			foreach($map as $header_keys => $value){
				if( strpos($header_keys, '->cus2') !== false) {
					if(!empty($value)){
						$helpers_instance->write_to_customfile($value, $header_array, $value_array);
						unset($map[$header_keys]);
					}
				}
				else{
					$header_trim = strtr($header_keys, $trim_content);
					if($header_trim != $header_keys){
						unset($map[$header_keys]);
					}
					$map[$header_trim] = $value;
				}
			}
		
			foreach($map as $key => $value){
				$csv_value= trim($map[$key]);
				$extension_object = new ExtensionHandler;
				$import_type = $extension_object->import_type_as($type);
				$import_as = $extension_object->import_post_types($import_type );
				if(!empty($csv_value)){

					//$pattern = "/({([a-z A-Z 0-9 | , _ -]+)(.*?)(}))/";
					$pattern1 = '/{([^}]*)}/';
					$pattern2 = '/\[([^\]]*)\]/';

					if(preg_match_all($pattern1, $csv_value, $matches, PREG_PATTERN_ORDER)){	
						
						//check for inbuilt or custom function call -> enclosed in []
						if(preg_match_all($pattern2, $csv_value, $matches2)){
							$matched_element = $matches2[1][0];
							
							foreach($matches[1] as $value){
								$get_value = $helpers_instance->replace_header_with_values($value, $header_array, $value_array);
								$values = '{'.$value.'}';
								$get_value = "'".$get_value."'";
								$matched_element = str_replace($values, $get_value, $matched_element);
							}
							$csv_element = $helpers_instance->evalPhp($matched_element);
						}
						else{

							$csv_element = $csv_value;
							//foreach($matches[2] as $value){
							foreach($matches[1] as $value){
								$get_key = array_search($value , $header_array);
								if(isset($value_array[$get_key])){
									$csv_value_element = $value_array[$get_key];	
									$value = '{'.$value.'}';
									$csv_element = str_replace($value, $csv_value_element, $csv_element);
								}
							}

							$math = 'MATH';
							if (strpos($csv_element, $math) !== false) {	
								$equation = str_replace('MATH', '', $csv_element);
								$csv_element = $helpers_instance->evalMath($equation);
							}
						}
						$wp_element= trim($key);
						if(!empty($csv_element) && !empty($wp_element)){
							$post_values[$wp_element] = $csv_element;
							$post_values['post_type'] = $import_as;
							//$post_values = $this->import_core_fields($post_values);
						}
					}
					// for custom function without headers in it
					elseif(preg_match_all($pattern2, $csv_value, $matches2)){
						$matched_element = $matches2[1][0];

						$wp_element= trim($key);
						$csv_element1 = $helpers_instance->evalPhp($matched_element);
						$post_values[$wp_element] = $csv_element1;
					}
					elseif(!in_array($csv_value , $header_array)){
						$wp_element= trim($key);
						$post_values[$wp_element] = $csv_value;
						$post_values['post_type'] = $import_as;
						//$post_values = $this->import_core_fields($post_values,$mode);
					}

					else{

						$get_key= array_search($csv_value , $header_array);
						if(isset($value_array[$get_key])){
							$csv_element = $value_array[$get_key];	
							$wp_element= trim($key);
							$extension_object = new ExtensionHandler;
							$import_type = $extension_object->import_type_as($type);
							$import_as = $extension_object->import_post_types($import_type );
							if(!empty($csv_element) && !empty($wp_element)){
								$post_values[$wp_element] = $csv_element;
								$post_values['post_type'] = $import_as;
							//	$post_values = $this->import_core_fields($post_values);
								//if(!is_numeric($post_values['post_parent'])&&!empty($post_values['post_parent'])){
								if( (isset($post_values['post_parent'])) && (!is_numeric($post_values['post_parent'])) && (!empty($post_values['post_parent']))){
									$p_type=$post_values['post_type'];
									$parent_title=$post_values['post_parent'];
									$parent_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '$parent_title' and post_status !='trash' and post_type='$p_type'" );
									$post_values['post_parent']=$parent_id;
								}
							}
						}
					}
				}
			}
			$post_values = $this->import_core_fields($post_values);
			if($check == 'ID'){	
				if(isset($post_values['ID'])){
					$ID = $post_values['ID'];	
					$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE ID = '$ID' AND post_type = '$import_as' AND post_status != 'trash' order by ID DESC ");
				}			
			}
			if($check == 'post_title'){
				if(isset($post_values['post_title'])){
					$title = $post_values['post_title'];
					$title = $wpdb->_real_escape($title);
					
					if($sitepress != null && is_plugin_active('wpml-ultimate-importer/wpml-ultimate-importer.php')){
						$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$title' AND post_type = '$import_as' AND post_status != 'trash' order by ID DESC ");		
						foreach($get_result as $wpml_result){
							$wpml_id[] = $wpml_result->ID;
						}	
						$template_table_name = $wpdb->prefix . "ultimate_csv_importer_mappingtemplate";
						$background_values = $wpdb->get_results("SELECT mapping FROM $template_table_name WHERE `eventKey` = '$hash_key' ");
						foreach ($background_values as $values) {
							$mapped_fields_values = $values->mapping;
						}
						$map_wpml = unserialize($mapped_fields_values);
						
						$wpml_values = $helpers_instance->get_header_values($map_wpml['WPML'], $header_array , $value_array);
						$get_results =array();
						$w = 0;
						foreach($wpml_id as $w_id){
							$languagecode =  $wpdb->get_var("SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_id = '$w_id'");		
							if($wpml_values['language_code'] == $languagecode){
								$get_results[$w]['ID']= $w_id;
	
								$w++;
							}
						}
						foreach($get_results as $g_result){
							$getresult[] = (object) $g_result;
						}
					}
					else{
						$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$title' AND post_type = '$import_as' AND post_status != 'trash' order by ID DESC ");		
					}
				}		
			}
			if($check == 'post_name'){
				if(isset($post_values['post_name'])){
					$name = $post_values['post_name'];
					$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_name = '$name' AND post_type = '$import_as' AND post_status != 'trash' order by ID DESC ");
				}
	
			}
			if($check == 'post_content'){
				if(isset($post_values['post_content'])){
					$content = $post_values['post_content'];
					$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_content = '$content' AND post_type = '$import_as' AND post_status != 'trash' order by ID DESC ");
				}
	
			}

			if($this->generated_content){
				$generated_content = $post_values['post_content'];
				if($generated_content == 401 ||$generated_content == 429 ||$generated_content == 500 ||$generated_content == 503 ||$generated_content == 400){
					$post_values['post_content'] = '';
				}
			}
			if($this->generated_content){
				$generated_short_description = $post_values['post_excerpt'];
				if($generated_short_description == 401 ||$generated_short_description == 429 ||$generated_short_description == 500 ||$generated_short_description == 503 ||$generated_short_description == 400){
					$post_values['post_excerpt'] = '';
				}
			}

			if($mode == 'Insert'){
				$orig_img_src = [];
				if (is_array($get_result) && !empty($get_result)) {
					$fields = $wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
					$this->detailed_log[$line_number]['Message'] =  "Skipped, Due to duplicate found!.";
					$this->detailed_log[$line_number]['state'] = 'Skipped';
				}
				else{
					if($post_values['post_status']!='delete'){
						if(is_plugin_active('multilanguage/multilanguage.php')) {
							$post_id = $this->multiLang($post_values);
						}
						else if($sitepress != null && is_plugin_active('wpml-ultimate-importer/wpml-ultimate-importer.php')){
							$post_values['post_content']=isset($post_values['post_content'])?$post_values['post_content']:'';
							$post_values['post_content'] = html_entity_decode($post_values['post_content']);
							$active_languages = $wpdb->get_results("SELECT tag FROM {$wpdb->prefix}icl_languages where active = 1");
							foreach($active_languages as $lang){
								$active [] =$lang->tag;
							}
							$template_table_name = $wpdb->prefix . "ultimate_csv_importer_mappingtemplate";
							$background_values = $wpdb->get_results("SELECT mapping FROM $template_table_name WHERE `eventKey` = '$hash_key' ");
							foreach ($background_values as $values) {
								$mapped_fields_values = $values->mapping;
							}
							$map_wpml = unserialize($mapped_fields_values);
							
							$wpml_values = $helpers_instance->get_header_values($map_wpml['WPML'], $header_array , $value_array);
							if(in_array($wpml_values['language_code'],$active)){
								$post_id = wp_insert_post($post_values);
								$status = $post_values['post_status'];
								$update=$wpdb->get_results("UPDATE {$wpdb->prefix}posts set post_status = '$status' where id = $post_id");
							}
							else{
								$wpml_message = "The given language code not configured in WPML";
							}
						}
						else{
							$post_values['post_content']=isset($post_values['post_content'])?$post_values['post_content']:'';
							$post_values['post_content'] = html_entity_decode($post_values['post_content']);													
							$post_id = wp_insert_post($post_values);
							$status = $post_values['post_status'];
							$update=$wpdb->get_results("UPDATE {$wpdb->prefix}posts set post_status = '$status' where id = $post_id");
						}

						if(!empty($post_values['wp_page_template']) && $type == 'Pages'){
							update_post_meta($post_id, '_wp_page_template', $post_values['wp_page_template']);
						}
					}
					if($unmatched_row == 'true'){
						global $wpdb;
						$post_entries_table = $wpdb->prefix ."ultimate_post_entries";
						$file_table_name = $wpdb->prefix."smackcsv_file_events";
						$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `hash_key` = '$hash_key'");	
						$file_name = $get_id[0]->file_name;
						$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$post_id}','{$type}', '{$file_name}','Inserted')");
					}
					if(isset($post_values['post_format'])){
						$format=str_replace("post-format-","",$post_values['post_format']);
						set_post_format($post_id ,$format );
					}
					if(is_plugin_active('post-expirator/post-expirator.php')) {
						$this->postExpirator($post_id,$post_values);
					}
					

					$media_handle = get_option('smack_image_options');
					if($media_handle['media_settings']['media_handle_option'] == 'true' 
					&& isset($media_handle['media_settings']['enable_postcontent_image'])
					&& $media_handle['media_settings']['enable_postcontent_image'] == 'true'){
						if (preg_match("/<img/", $post_values['post_content'])) {
							// Wrap post content in a <p> tag if needed
							$content = "<p>" . $post_values['post_content'] . "</p>";
							$doc = new \DOMDocument();
						
							// Ensure proper encoding
							if (function_exists('mb_convert_encoding')) {
								@$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
							} else {
								@$doc->loadHTML($content);
							}
						
							// Find all image elements with src attributes
							$xpath = new \DOMXPath($doc);
							$searchNode = $xpath->query('//img[@src]');
						
							if (!empty($searchNode)) {
								$media_dir = wp_get_upload_dir(); // Get the media directory
								$attach_ids = [];
								$index = 0;
						
								foreach ($searchNode as $searchNodes) {
									$img_src = $searchNodes->getAttribute('src'); // Get external image URL
						
									// Download the external image to the media library
									$attach_id = CoreFieldsImport::$media_instance->image_meta_table_entry(
										$line_number, $post_values, $post_id, '', $img_src, $hash_key, 
										'inline', $get_import_type, '', '', $header_array, $value_array, '', '', $index
									);
									if ($attach_id) {
										$local_img_url = wp_get_attachment_url($attach_id);
										$searchNodes->setAttribute('src', $local_img_url);
										$index++;
									}
								}
						
								// Save the updated content
								$post_content = $doc->saveHTML();
								// Update post content with local image URLs
								$post_values['post_content'] = $post_content;
								$update_content = [
									'ID' => $post_id,
									'post_content' => $post_content
								];
								wp_update_post($update_content); // Update the post in the database
							}
						}
						
					}
					if($media_handle['media_settings']['media_handle_option'] == 'true' && !empty($post_values['post_content']) && $media_handle['media_settings']['enable_postcontent_image'] == 'false' && preg_match("/<img/", $post_values['post_content'])) {
						$dom = new \DOMDocument();
                        @$dom->loadHTML($post_values['post_content']);
                        $xpath = new \DOMXPath($dom);
                        $searchNode = $xpath->query('//img[@src]');
						foreach ( $searchNode as $searchNodes ) {
							$orig_img_src[] = $searchNodes->getAttribute( 'src' );
							if(!empty($orig_img_src)){
								CoreFieldsImport::$media_instance->store_image_ids($i=1);
							}
						}	
							$media_dir = wp_get_upload_dir();
							$names = $media_dir['url'];
					}


					$fields = $wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey_value'");
					if(is_wp_error($post_id) || $post_id == '') {
						if(is_wp_error($post_id)) {
							$this->detailed_log[$line_number]['Message'] = "Can't insert this " . $post_values['post_type'] . ". " . $post_id->get_error_message();
							$this->detailed_log[$line_number]['state'] = 'Skipped';
						}
						else {
							$wpml_message  = isset($wpml_message )?$wpml_message:'';
							if($sitepress != null && is_plugin_active('wpml-ultimate-importer/wpml-ultimate-importer.php')){
								$this->detailed_log[$line_number]['Message'] =  "Can't insert this " . $post_values['post_type'].'. '.$wpml_message;
								$this->detailed_log[$line_number]['state'] = 'Skipped';
							}
							else{
								$this->detailed_log[$line_number]['Message'] =  "Can't insert this " . $post_values['post_type'];
								$this->detailed_log[$line_number]['state'] = 'Skipped';
							}
						}
						$fields = $wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
					}	
					else{
						$post_values['specific_author'] = isset($post_values['specific_author'])?$post_values['specific_author']:'';

						$content=$this->openAI_response;
						if(!empty($content)){
							if($generated_content == 401) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create Content. Invalid API key provided. Please check your API key.";	
							}
							else if($generated_content == 429) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create Content. Rate limit reached for requests or You exceeded your current quota.";	
							}
							else if($generated_content == 400) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create Content. Please check your Inputs.";	
							}
							else if($generated_content == 500) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create Content. The server had an error while processing your request.";	
							}
							else if($generated_content == 503) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create Content. The engine is currently overloaded, please try again later.";	
							}
							else if($generated_short_description == 401) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create short description. Invalid API key provided. Please check your API key.";	
							}
							else if($generated_short_description == 429) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create short description. Rate limit reached for requests or You exceeded your current quota.";	
							}
							else if($generated_short_description == 400) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create short description. Please check your Inputs.";	
							}
							else if($generated_short_description == 500) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create short description. The server had an error while processing your request.";	
							}
							else if($generated_short_description == 503) {
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author']. 	"<b style='color: red;'> Notice : </b>Cannot create short description. The engine is currently overloaded, please try again later.";	
							}
							else{
								$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author'];
								$author = explode('</b>',$post_values['specific_author']);
								$this->detailed_log[$line_number]['id'] = $post_id;
								$this->detailed_log[$line_number]['state'] = 'Inserted';
								$this->detailed_log[$line_number]['author'] = end($author);
							}
						}
						else{
							$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author'];
							$author = explode('</b>',$post_values['specific_author']);
							$this->detailed_log[$line_number]['id'] = $post_id;
							$this->detailed_log[$line_number]['state'] = 'Inserted';
							$this->detailed_log[$line_number]['author'] = end($author);
						}
					}
					if( $post_values['post_type'] == 'page'){
						$status = $post_values['post_status'];
						$wpdb->get_results("UPDATE {$wpdb->prefix}posts set post_status = '$status' where id = $post_id");
					}
					if( $post_values['post_type'] == 'page'){
						$status = $post_values['post_status'];
						$wpdb->get_results("UPDATE {$wpdb->prefix}posts set post_status = '$status' where id = $post_id");
					}
				}
			}

			if($mode == 'Update'){
				if (is_array($get_result) && !empty($get_result)) {
					$post_id = $get_result[0]->ID;	
					$post_values['ID'] = $post_id;
					wp_update_post($post_values);

					if(isset($post_values['post_format'])){
						$format=str_replace("post-format-","",$post_values['post_format']);
						set_post_format($post_id , $format);
					}	

					if($unmatched_row == 'true'){
						global $wpdb;
						$post_entries_table = $wpdb->prefix ."ultimate_post_entries";
						$file_table_name = $wpdb->prefix."smackcsv_file_events";
						$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `hash_key` = '$hash_key'");	
						$file_name = $get_id[0]->file_name;
						$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$post_id}','{$type}', '{$file_name}','Updated')");
					}

					$fields = $wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey_value'");
					$this->detailed_log[$line_number]['Message'] = 'Updated' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author'];
				}else{

					unset($post_values['ID']);
					$post_id = wp_insert_post($post_values);
					if(isset($post_values['post_format'])){
						$format=str_replace("post-format-","",$post_values['post_format']);
						set_post_format($post_id , $format);
					}
					$fields = $wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey_value'");
					if(is_wp_error($post_id) || $post_id == '') {
						if(is_wp_error($post_id)) {
							$this->detailed_log[$line_number]['Message'] = "Can't insert this " . $post_values['post_type'] . ". " . $post_id->get_error_message();
							$this->detailed_log[$line_number]['state'] = 'Skipped';
						}
						else {
							$this->detailed_log[$line_number]['Message'] =  "Can't insert this " . $post_values['post_type'];
							$this->detailed_log[$line_number]['state'] = 'Skipped';
						}
						$fields = $wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
					}
					else{
						$this->detailed_log[$line_number]['Message'] = 'Inserted ' . $post_values['post_type'] . ' ID: ' . $post_id . ', ' . $post_values['specific_author'];
					}
				}
			}

			if(preg_match("(Can't|Skipped|Duplicate)", $this->detailed_log[$line_number]['Message']) === 0) {  
				if ( $type == 'Posts' || $type == 'CustomPosts' || $type == 'Pages') {
					if ( ! isset( $post_values['post_title'] ) ) {
						$post_values['post_title'] = '';
					}
					$this->detailed_log[$line_number]['webLink'] = get_permalink( $post_id );
					$this->detailed_log[$line_number]['adminLink'] = get_edit_post_link( $post_id, true );
					$this->detailed_log[$line_number]['post_type'] = get_post_type($post_id);
					$this->detailed_log[$line_number]['post_title'] = get_the_title($post_id);
					
				}
				else{
					$post_value_title = isset($post_values['post_title']) ? $post_values['post_title'] : '';
					if($type == 'llms_coupon'){
						$this->detailed_log[$line_number]['adminLink'] = get_edit_post_link( $post_id, true );
						$this->detailed_log[$line_number]['post_type'] = get_post_type($post_id);
						$this->detailed_log[$line_number]['post_title'] = get_the_title($post_id);
					}
					else{
						$this->detailed_log[$line_number]['webLink'] = get_permalink( $post_id );
						$this->detailed_log[$line_number]['adminLink'] = get_edit_post_link( $post_id, true );
						$this->detailed_log[$line_number]['post_type'] = get_post_type($post_id);
						$this->detailed_log[$line_number]['post_title'] = get_the_title($post_id);
					}
				}
				if(isset($post_values['post_status'])){
					$this->detailed_log[$line_number]['Status'] = $post_values['post_status'];
				}
			}
			if((isset($post_values['featured_image'])) && !is_plugin_active('featured-image-from-url/featured-image-from-url.php')) {
				if (strpos($post_values['featured_image'], '|') !== false) {
					$featured_img = explode('|', $post_values['featured_image']);
					$featured_image=$featured_img[0];					
				}
				else if (strpos($post_values['featured_image'], ',') !== false) {
					$feature_img = explode(',', $post_values['featured_image']);
					$featured_image=$feature_img[0];
				}
				else{
					$featured_image=$post_values['featured_image'];
				}

				if ( preg_match_all( '/\b[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $featured_image, $matchedlist, PREG_PATTERN_ORDER ) ) {			
					if($media_handle['media_settings']['media_handle_option'] == 'true'){
						if(!empty($post_values['featured_image'])){
							CoreFieldsImport::$media_instance->store_image_ids($i=1);
						}
						if($media_handle['media_settings']['use_ExistingImage'] == 'true'){
							$image_type = 'Featured';		  
							$f_image = $post_values['featured_image'];
							$f_path = CoreFieldsImport::$media_instance->get_filename_path($post_values['featured_image']);
							$fimg_name = isset($f_path['fimg_name']) ? $f_path['fimg_name'] : '';
							// $check_featured_image = $wpdb->get_results("SELECT $unikey_name FROM {$wpdb->prefix}ultimate_csv_importer_media_report WHERE $unikey_name = '$unikey_value'  AND image_type = 'Featured' "); 
							// if(empty($check_featured_image)){				
							// 	$image_media_table = $wpdb->prefix . "ultimate_csv_importer_media_report";
							// 	$wpdb->get_results("INSERT INTO $image_media_table (`hash_key`,`templatekey`,`module`,`image_type`,`status`) VALUES ( '{$hash_key}','{$templatekey}','{$type}','{$image_type}','Completed') ");
							// }
							$wp_content_url = content_url();
								if(strpos($f_image, $wp_content_url) !== FALSE){
									$attachment_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND guid = '$f_image' ", ARRAY_A);
								}
								else{
									$attachment_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND guid LIKE '%$fimg_name%'", ARRAY_A);
								}
								if(!empty($attachment_id[0]['ID'])){
									$table_name = $wpdb->prefix . 'smackcsv_file_events';
									$file_name = $wpdb->get_var("SELECT file_name FROM $table_name WHERE hash_key = '$hash_key'");
									$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";                                                                   
									$attach_id = $attachment_id[0]['ID'];
									$check_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE ID ='{$attach_id}' AND post_title ='image-failed' AND post_type = 'attachment'", ARRAY_A);
									if(!empty($check_id)){
										$failed_ids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id='{$post_id}' AND media_id = '{$attach_id}' AND image_shortcode = 'Featured_image_'");
										if(!empty($failed_ids) && $failed_ids[0]->post_id != $post_id){
											$attach_id = $failed_ids[0]->media_id;
											CoreFieldsImport::$media_instance->store_failed_image_ids($attach_id);
											CoreFieldsImport::$media_instance->failed_media_data($line_number,$post_id,$failed_ids[0]->post_title,$failed_ids[0]->media_id, $failed_ids[0]->original_image,);				
										}elseif(empty($failed_ids) ){
											CoreFieldsImport::$media_instance->store_failed_image_ids($attach_id);
											CoreFieldsImport::$media_instance->failed_media_data($line_number,$post_id,$post_values['post_title'],$attach_id,$post_values['featured_image']);
										}elseif(!empty($failed_ids) && $failed_ids[0]->post_id == $post_id){
											CoreFieldsImport::$media_instance->store_failed_image_ids($failed_ids[0]->media_id);
											CoreFieldsImport::$media_instance->failed_media_data($line_number,$failed_ids[0]->post_id,$failed_ids[0]->post_title,$failed_ids[0]->media_id,$failed_ids[0]->original_image); 
										}
										set_post_thumbnail($post_id, $attach_id);
									}else{
										set_post_thumbnail($post_id, $attach_id);
									}             
								}
								else{
									$post_values['featured_image'] = $featured_image;
									$image_type = 'Featured';
									$attach_id = CoreFieldsImport::$media_instance->image_meta_table_entry($line_number,$post_values,$post_id ,'',$post_values['featured_image'], $hash_key ,$image_type,$type,'','',$header_array,$value_array);
									set_post_thumbnail( $post_id, $attach_id );
								}
						}
						else{
						$image_type = 'Featured';
						$attach_id = CoreFieldsImport::$media_instance->image_meta_table_entry($line_number,$post_values,$post_id ,'',$post_values['featured_image'], $hash_key ,$image_type,$type,'','',$header_array,$value_array);
						set_post_thumbnail( $post_id, $attach_id );
						}
					}
				}
				if(preg_match("(Can't|Skipped|Duplicate)", $this->detailed_log[$line_number]['Message']) === 0) {  
					if ( $type == 'Media') {
						
						$this->detailed_log[$line_number]['adminLink'] = get_edit_post_link( $attach_id, true );
						$image_url = wp_get_attachment_url($attach_id);
						$this->detailed_log[$line_number]['webLink'] = $image_url;
					}
				}
			}
		}
			return $post_id;
		}
	}

	public function multiLang($post_values){
		global $wpdb;
		if (strpos($post_values['post_title'], '|') !== false) {
			$exploded_title = explode('|', $post_values['post_title']);
			$post_values['post_title'] = $exploded_title[0];
			$lang_title = $exploded_title[1];

		}
		if (strpos($post_values['post_content'], '|') !== false) {
			$exploded_content = explode('|', $post_values['post_content']);
			$post_values['post_content'] = $exploded_content[0];
			$lang_content = $exploded_content[1];
		}
		if (strpos($post_values['post_excerpt'], '|') !== false) {
			$exploded_excerpt = explode('|', $post_values['post_excerpt']);
			$post_values['post_excerpt'] = $exploded_excerpt[0];
			$lang_excerpt = $exploded_excerpt[1];
		}
		$lang_code = $post_values['lang_code'];
		$post_id = wp_insert_post($post_values);
		$wpdb->get_results("INSERT INTO {$wpdb->prefix}mltlngg_translate (post_ID , post_content , post_excerpt, post_title,`language`) VALUES ( $post_id, '{$lang_content}', '{$lang_excerpt}' , '{$lang_title}', '{$lang_code}')");
		return $post_id;
	}

	function replaceValues($array) {
		foreach ($array as $innerKey => $innerValue) {
			if (strpos($innerKey, '->openAI') !== false) {
				$newKey = str_replace('->openAI', '', $innerKey);
				$array[$newKey] = $newKey;
				unset($array[$innerKey]);
			}
		}
		return $array;
	}

	function filterNumKeys($array) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = $this->filterNumKeys($value);
			} else {
				if (strpos($key, '->num') !== false) {
					unset($array[$key]);
				}
			}
		}
		return $array;
	}
	public function get_order_url($post_id){
		$dir=site_url().'/wp-admin/admin.php?page=wc-orders&action=edit&id='.$post_id;
		return  $dir;
	}
	public function postExpirator($post_id,$post_values){
		if(!empty($post_values['post_expirator_status'])){
			$post_values['post_expirator_status'] = array('expireType' => $post_values['post_expirator_status'],'id' => $post_id);
		}
		else{
			$post_values['post_expirator_status'] = array('expireType' => 'draft' ,'id' => $post_id);
		}

		if(!empty($post_values['post_expirator'])){
			update_post_meta($post_id, '_expiration-date-status', 'saved');
			$estimate_date = $post_values['post_expirator'];
			$estimator_date = get_gmt_from_date("$estimate_date",'U');
			update_post_meta($post_id, '_expiration-date', $estimator_date);
			update_post_meta($post_id, '_expiration-date-options', $post_values['post_expirator_status']);			
		}	
	}

	function image_handling($id){
		global $wpdb;	
		$post_values = [];
		$get_result =  $wpdb->get_results("SELECT post_content FROM {$wpdb->prefix}posts where ID = $id",ARRAY_A);   	
		$post_values['post_content']=htmlspecialchars_decode($get_result[0]['post_content']);
		$get_result =  $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager where post_id = $id",ARRAY_A);   
		foreach($get_result as $result){
			$orig_img_src[] = $result['original_image'];
		}	

		$get_results =  $wpdb->get_results("SELECT image_shortcode FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager where post_id = $id",ARRAY_A);

		foreach ($get_results as $results){
			$origs_img_src[] = $results['image_shortcode'];
		}

		$image_type = 'Inline' ;

		foreach($orig_img_src as $src){
			$attach_id[] = CoreFieldsImport::$media_instance->media_handling($src , $id ,$post_values,'',$image_type,'');	
		}
		if(is_array($attach_id)){
			foreach($attach_id as $att_key => $att_val){
				$get_guid[] = $wpdb->get_results("SELECT `guid` FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' and ID =  $att_val ",ARRAY_A);
				foreach($origs_img_src as $img_src){
					$result  = str_replace($img_src , ' ' , $post_values['post_content']);
				}
			}
		}
		$image_name = $result;
		$doc = new \DOMDocument();
		if(function_exists('mb_convert_encoding')) {
			@$doc->loadHTML( mb_convert_encoding( $image_name, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		}else{
			@$doc->loadHTML( $image_name);
		}
		$img_tags = $doc->getElementsByTagName('img');
		$i=0;
		foreach ($img_tags as $t )
		{
			$savepath = $get_guid[$i][0]['guid'];	
			$t->setAttribute('src',$savepath);
			$i++;
		}
		$result = $doc->saveHTML();
		$update_content['ID']           = $id;
		$update_content['post_content'] = $result;
		wp_update_post( $update_content );
		return $id;
	}


	function import_core_fields($data_array){
		$helpers_instance = ImportHelpers::getInstance();

		if(empty( $data_array['post_date'] )) {
			$data_array['post_date'] = current_time('Y-m-d H:i:s');
		} else {
			if(strtotime( $data_array['post_date'] )) {
				if (strpos($data_array['post_date'], '.') !== false) {
					$data_array['post_date'] = str_replace('.', '-', $data_array['post_date']);
				}
				$data_array['post_date'] = date( 'Y-m-d H:i:s', strtotime( $data_array['post_date'] ) );
			} else {
				$data_array['post_date'] = current_time('Y-m-d H:i:s');
			}
		}

		if(!isset($data_array['post_author'])) {
			$data_array['post_author'] = 1;
		} else {
			if(isset( $data_array['post_author'] )) {
				$user_records = $helpers_instance->get_from_user_details( $data_array['post_author'] );
				$data_array['post_author'] = $user_records['user_id'];
				$data_array['specific_author'] = $user_records['message'];
			}
		}
		if ( !empty($data_array['post_status']) ) {

			$data_array = $helpers_instance->assign_post_status( $data_array );
		}else{
			$data_array['post_status'] = 'publish';
		}
		return $data_array;
	}

}

<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\WCSV;
use Smackcoders\FCSV\CoreFieldsImport;
use Smackcoders\FCSV\MediaHandling;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class WooCommerceMetaImport {
	private static $woocommerce_meta_instance = null,$media_instance;

	public static function getInstance() {		
		if (WooCommerceMetaImport::$woocommerce_meta_instance == null) {
			WooCommerceMetaImport::$woocommerce_meta_instance = new WooCommerceMetaImport;
			WooCommerceMetaImport::$media_instance = new MediaHandling;
			return WooCommerceMetaImport::$woocommerce_meta_instance;
		}
		return WooCommerceMetaImport::$woocommerce_meta_instance;
	}

	public function woocommerce_meta_import_function ($data_array, $image_meta, $pID, $variation_id ,$import_type , $line_number , $mode, $header_array , $value_array , $core_array, $hash_key,$gmode,$templatekey) {		
		global $wpdb;
		$core_instance = CoreFieldsImport::getInstance();
		$variation_instance = CoreFieldsImport::getInstance();
		global $core_instance;

		$metaData = array();
		$variationData = array();
		$order_item = array();

		if(($import_type == 'WooCommerce Product') && isset($core_array['PRODUCTSKU'])){
			$metaData['_sku'] = $core_array['PRODUCTSKU'];
			$core_instance->detailed_log[$line_number][' SKU'] = $core_array['PRODUCTSKU'];
			
		}
		if(($import_type == 'WooCommerce Product Variations') && isset($core_array['VARIATIONSKU'])){
			$metaData['_sku'] = $core_array['VARIATIONSKU'];
			$core_instance->detailed_log[$line_number][' SKU'] = $core_array['VARIATIONSKU'];
		}
		
		foreach ($data_array as $ekey => $eval) {
			switch ($ekey) {	
			case 'stock_qty' :
				$metaData['_stock'] = $data_array[$ekey];
				if(!empty($metaData['_stock']) && is_numeric($metaData['_stock'])){
					$stock_status = '';
					if ($metaData['_stock'] >= 1) {
						$stock_status = 'instock';
						$wpdb->get_results("UPDATE {$wpdb->prefix}wc_product_meta_lookup SET stock_status = 'instock'  WHERE product_id = $pID");
					}
					$metaData['_stock_status'] = $stock_status;
					$metaData['_manage_stock'] = 'yes';
				}
				else{
					$metaData['_stock_status'] = 'outofstock';
					$metaData['_manage_stock'] = 'no';
					$wpdb->get_results("UPDATE {$wpdb->prefix}wc_product_meta_lookup SET stock_status = 'outofstock'  WHERE product_id = $pID");
		
				}
				break;
			case 'visibility' :
				$plugininfo = get_plugin_data( WP_PLUGIN_DIR .'/'.'woocommerce/woocommerce.php');
				$versionOfWoocom = $plugininfo['Version'];
				$visibility = '';
				if ($data_array[$ekey] == 1) {
					$visibility = 'visible';
				}
				if ($data_array[$ekey] == 2) {
					$visibility = 'catalog';
				}
				if ($data_array[$ekey] == 3) {
					$visibility = 'search';
				}
				if ($data_array[$ekey] == 4) {
					$visibility = 'hidden';
				}
				if(empty($data_array[$ekey])){
					$visibility = 'visible';
				}
				if($versionOfWoocom >= 3){
					if ($product = wc_get_product($pID)) {
						$product->set_catalog_visibility($visibility);
						$product->save();
					}
				}
				else
					$metaData['_visibility'] = $visibility;
				break;

			case 'stock_status' :

					if(is_numeric($data_array[$ekey])){
						$stock_status = '';
						if ($data_array[$ekey] == 1) {
							$stock_status = 'instock';
							$metaData['_manage_stock'] = 'yes';
						}
						elseif ($data_array[$ekey] == 2) {
							$stock_status = 'outofstock';
							$metaData['_manage_stock'] = 'no';
						}
						$wpdb->get_results("UPDATE {$wpdb->prefix}wc_product_meta_lookup SET stock_status = '$stock_status'  WHERE product_id = $pID");
						$metaData['_stock_status'] = $stock_status;
						
					}
					else{
						$metaData['_stock_status'] = $data_array[$ekey];
					}
	
					break;
			case 'downloadable' :
				$metaData['_downloadable'] = $data_array[$ekey];
				break;
			case 'virtual' :
				$metaData['_virtual'] = $data_array[$ekey];
				break;
			case 'product_image_gallery' :
	
				$plugin = 'product';
				
				if(!empty($data_array[$ekey])){
					if (strpos($data_array[$ekey], ',') !== false) {
						$get_all_gallery_images = explode(',', $data_array[$ekey]);
					} elseif (strpos($data_array[$ekey], '|') !== false) {
						$get_all_gallery_images = explode('|', $data_array[$ekey]);
					}
					else{
						$get_all_gallery_images[] = $data_array[$ekey];
					}
		
					$gallery_image_ids = '';
					foreach($get_all_gallery_images as $gallery_image) {
						WooCommerceMetaImport::$media_instance->store_image_ids($i=1);
						if(is_numeric($gallery_image)) {
							$attachids[]=$gallery_image;
							$gallery_image_ids .= $gallery_image . ',';
						} else {
							$attachmentId = WooCommerceMetaImport::$media_instance->image_meta_table_entry($image_meta, $pID, 'product_image_gallery', $gallery_image, $hash_key, 'product', 'post',$templatekey,$gmode);	
							$atachids[]=$attachmentId;
							$gallery_image_ids .= $attachmentId . ',';
						}
					}

					//update_image_meta
					$get_existing_image_option = get_option('smack_schedule_image_exists_product_'.$pID);
					if($get_existing_image_option){
						WooCommerceMetaImport::$media_instance->acfimageMetaImports($atachids, $image_meta, $plugin);
						delete_option('smack_schedule_image_exists_product_'.$pID);
					}

					//WooCommerceMetaImport::$media_instance->acfimageMetaImports($attachids,$image_meta,$plugin);
					$product_image_gallery[$ekey] = $gallery_image_ids;
				}
				
				break;
			case 'regular_price' :
				$metaData['_regular_price'] = $data_array[$ekey];
				if(empty($metaData['_regular_price'])){
					global $wpdb;
					$price = $wpdb->get_var("SELECT meta_value FROM ".$wpdb->prefix."postmeta WHERE meta_key = '_price' AND post_id = $pID");
					$metaData['_price'] = $price;
					if($import_type == 'WooCommerce Product Variations'){
						if($metaData['_price']){
							$metaData['_price'] = $metaData['_price'];
						}
						else{
							$metaData['_price'] = $data_array[$ekey];
						}
						
					}

					if($import_type == 'WooCommerce Product'){
						if($metaData['_price']){
							$metaData['_price'] = $metaData['_price'];
						}
						else{
							$metaData['_price'] = $data_array[$ekey];
						}
					}
			    }
				else{
					global $wpdb;
					$saleprice = $wpdb->get_var("SELECT meta_value FROM ".$wpdb->prefix."postmeta WHERE meta_key = '_sale_price' AND post_id = $pID");
					
					if($import_type == 'WooCommerce Product Variations'){
						if($saleprice){
							$metaData['_price'] = $saleprice;
						}
						else{
							$metaData['_price'] = $data_array[$ekey];
						}
						
					}
					if($import_type == 'WooCommerce Product'){
						if($saleprice){
							$metaData['_price'] = $saleprice;
						}
						else{
							$metaData['_price'] = $data_array[$ekey];
						}
					
					}
	
				}
				break;
			case 'sale_price' :	  
				if($mode == 'Update'){
					if(empty($data_array[$ekey])){
						$metaData['_price']=$metaData['_regular_price'];	
						delete_post_meta(  $pID, '_sale_price' );
					}
					else{
						$metaData['_price'] =$data_array[$ekey];
						$metaData['_sale_price'] = $data_array[$ekey];
					}
				}
				else{
					$metaData['_price'] =$data_array[$ekey];
					$metaData['_sale_price'] = $data_array[$ekey];
				}
				break;

			case 'pb_regular_price' :
				$metaData['_wc_pb_base_regular_price'] = $data_array[$ekey];
				break;

			case 'pb_sale_price' :
				$metaData['_wc_pb_base_sale_price'] = $data_array[$ekey];
				break;

			case 'tax_status' :
				$tax_status = '';
				if ($data_array[$ekey] == 1) {
					$tax_status = 'taxable';
				}
				if ($data_array[$ekey] == 2) {
					$tax_status = 'shipping';
				}
				if ($data_array[$ekey] == 3) {
					$tax_status = 'none';
				}
				$metaData['_tax_status'] = $tax_status;
				break;
			case 'tax_class' :
			   $metaData['_tax_class']=$data_array[$ekey];
		       break;
			case 'purchase_note' :
				$metaData['_purchase_note'] = $data_array[$ekey];
				break;
			case 'featured_product' :
				if ($product = wc_get_product($pID)) {
					$product->set_featured( $data_array[$ekey] );
					$product->save();
				}
				break;

			case 'product_bundle_items':
				if ($data_array[$ekey]) {
					$bundle_product_ids = explode('|', $data_array[$ekey]);
					$bundle_product_table = $wpdb->prefix . 'woocommerce_bundled_items'; 
					foreach($bundle_product_ids as $product_id){
						if(is_numeric($product_id)){
							$product_id = $product_id;
						}
						else{
							$product_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'product' AND post_title = '$product_id' order by  ID Desc ");
						}
						$wpdb->insert($bundle_product_table, array('product_id' => $product_id, 'bundle_id' => $pID));
					}
					break;
				}
			break;
			case '_wc_pb_virtual_bundle':
				$metaData['_wc_pb_virtual_bundle']=$data_array[$ekey];
				break;
			case '_wc_pb_virtual_bundle':
				$metaData['_wc_pb_virtual_bundle']=$data_array[$ekey];
				break;
			case '_wc_pb_virtual_bundle':
				$metaData['_wc_pb_virtual_bundle']=$data_array[$ekey];
				break;
			case 'layout' :
				$layout_status = '';
				if ($data_array[$ekey] == 'Standard') {
					$layout_status = 'default';
				}
				if ($data_array[$ekey] == 'Tabular') {
					$layout_status = 'tabular';
				}
				if ($data_array[$ekey] == 'Grid') {
					$layout_status = 'grid';
				}
				$metaData['_wc_pb_layout_style'] = $layout_status;
				break;

			case 'form_location' :
				$form_location_status = '';
				if ($data_array[$ekey] == 'Default') {
					$form_location_status = 'default';
				}
				if ($data_array[$ekey] == 'Before Tabs') {
					$form_location_status = 'after_summary';
				}
				
				$metaData['_wc_pb_add_to_cart_form_location'] = $form_location_status;
				break;
			
			case 'item_grouping' :
				$item_grouping_status = '';
				if ($data_array[$ekey] == 'Grouped') {
					$item_grouping_status = 'parent';
				}
				if ($data_array[$ekey] == 'Flat') {
					$item_grouping_status = 'noindent';
				}
				if ($data_array[$ekey] == 'None') {
					$item_grouping_status = 'none';
				}
				$metaData['_wc_pb_group_mode'] = $item_grouping_status;
				break;
				
			case 'edit_in_cart' :
				$edit_cart_status = '';
				if ($data_array[$ekey] == 'Yes') {
					$edit_cart_status = 'yes';
				}
				if ($data_array[$ekey] == 'No') {
					$edit_cart_status = 'no';
				}
				
				$metaData['_wc_pb_edit_in_cart'] = $edit_cart_status;
				break;

			case 'optional':
			case 'priced_individually' :
			case 'override_title' :
			case 'override_description' :
			case 'hide_thumbnail' :
				if ($data_array[$ekey]) {
					$bundle_product_ids = explode('|', $data_array[$ekey]);
					$bundle_product_table = $wpdb->prefix . 'woocommerce_bundled_itemmeta'; 
					
					$bundle_meta = $wpdb->prepare("SELECT bundled_item_id FROM {$wpdb->prefix}woocommerce_bundled_items where bundle_id = %d", $pID);
					$bundle_meta_result = $wpdb->get_results($bundle_meta);
					
					for($i = 0; $i < count($bundle_meta_result); $i++){
						$bundle_meta_id = $bundle_meta_result[$i]->bundled_item_id;
						$bundle_meta_value = isset($bundle_product_ids[$i])?$bundle_product_ids[$i]:'';
						if ($bundle_meta_value == 'Yes') {
							$bundle_meta_values = 'yes';
						}
						if ($bundle_meta_value == 'No') {
							$bundle_meta_values = 'no';
						}
						$wpdb->insert($bundle_product_table, array('bundled_item_id' => $bundle_meta_id ,'meta_key' => $ekey,'meta_value' => $bundle_meta_values));
					}
					break;
				}	
			break;

			case 'single_product_visibility' :
			case 'cart_visibility' :
			case 'order_visibility' :
			case 'single_product_price_visibility' :
			case 'cart_price_visibility' :
			case 'order_price_visibility' :

				if ($data_array[$ekey]) {
					$bundle_product_ids = explode('|', $data_array[$ekey]);
					$bundle_product_table = $wpdb->prefix . 'woocommerce_bundled_itemmeta'; 
					
					$bundle_meta = $wpdb->prepare("SELECT bundled_item_id FROM {$wpdb->prefix}woocommerce_bundled_items where bundle_id = %d", $pID);
					$bundle_meta_result = $wpdb->get_results($bundle_meta);
					
					for($i = 0; $i < count($bundle_meta_result); $i++){
						$bundle_meta_id = $bundle_meta_result[$i]->bundled_item_id;
						$bundle_meta_value = isset($bundle_product_ids[$i])?$bundle_product_ids[$i]:'';
						if ($bundle_meta_value == 'Yes') {
							$bundle_meta_values = 'visible';
						}
						if ($bundle_meta_value == 'No') {
							$bundle_meta_values = 'hidden';
						}
						$wpdb->insert($bundle_product_table, array('bundled_item_id' => $bundle_meta_id ,'meta_key' => $ekey,'meta_value' => $bundle_meta_values));
					}
					break;
				}	
			break;

			case 'quantity_min' :
			case 'quantity_max' :
			case 'discount' :
	
				if ($data_array[$ekey]) {
					$bundle_product_ids = explode('|', $data_array[$ekey]);
					$bundle_product_table = $wpdb->prefix . 'woocommerce_bundled_itemmeta'; 
					
					$bundle_meta = $wpdb->prepare("SELECT bundled_item_id FROM {$wpdb->prefix}woocommerce_bundled_items where bundle_id = %d", $pID);
					$bundle_meta_result = $wpdb->get_results($bundle_meta);
					
					for($i = 0; $i < count($bundle_meta_result); $i++){
						$bundle_meta_id = $bundle_meta_result[$i]->bundled_item_id;
						$bundle_meta_value = isset($bundle_product_ids[$i])?$bundle_product_ids[$i]:'';
						
						$wpdb->insert($bundle_product_table, array('bundled_item_id' => $bundle_meta_id ,'meta_key' => $ekey,'meta_value' => $bundle_meta_value));	
					}
					break;
				}	
			break;

			case 'override_title_value' :
	
				if ($data_array[$ekey]) {
					$bundle_product_ids = explode('|', $data_array[$ekey]);
					$bundle_product_table = $wpdb->prefix . 'woocommerce_bundled_itemmeta'; 
					
					$bundle_meta = $wpdb->prepare("SELECT bundled_item_id FROM {$wpdb->prefix}woocommerce_bundled_items where bundle_id = %d", $pID);
					$bundle_meta_result = $wpdb->get_results($bundle_meta);
					
					for($i = 0; $i < count($bundle_meta_result); $i++){
						$bundle_meta_id = $bundle_meta_result[$i]->bundled_item_id;
						$bundle_meta_value = isset($bundle_product_ids[$i])?$bundle_product_ids[$i]:'';
					
						$wpdb->insert($bundle_product_table, array('bundled_item_id' => $bundle_meta_id ,'meta_key' => 'title','meta_value' => $bundle_meta_value));
					}
					break;
				}	
			break;

			case 'override_description_value' :
	
				if ($data_array[$ekey]) {
					$bundle_product_ids = explode('|', $data_array[$ekey]);
					$bundle_product_table = $wpdb->prefix . 'woocommerce_bundled_itemmeta'; 
					
					$bundle_meta = $wpdb->prepare("SELECT bundled_item_id FROM {$wpdb->prefix}woocommerce_bundled_items where bundle_id = %d", $pID);
					$bundle_meta_result = $wpdb->get_results($bundle_meta);
					
					for($i = 0; $i < count($bundle_meta_result); $i++){
						$bundle_meta_id = $bundle_meta_result[$i]->bundled_item_id;
						$bundle_meta_value = isset($bundle_product_ids[$i])?$bundle_product_ids[$i]:'';
					
						$wpdb->insert($bundle_product_table, array('bundled_item_id' => $bundle_meta_id ,'meta_key' => 'description','meta_value' => $bundle_meta_value));			
					}
					break;
				}	
			break;


			case 'variation_description':
				$metaData['_variation_description'] = $data_array[$ekey];
				break;
			case 'weight' :
				$metaData['_weight'] = $data_array[$ekey];
				break;
			case 'length' :
				$metaData['_length'] = $data_array[$ekey];
				break;
			case 'width' :
				$metaData['_width'] = $data_array[$ekey];
				break;
			case 'height' :
				$metaData['_height'] = $data_array[$ekey];
				break;
			case 'product_attribute_name' :
				$attribute_names[$ekey] = $data_array[$ekey];
				break;
			case 'product_attribute_value' :
				$attribute_values[$ekey] = $data_array[$ekey];
				break;
			case 'product_attribute_visible' :
				$attribute_visible[$ekey] = $data_array[$ekey];
				break;
			case 'product_attribute_variation' :
				$attribute_variation[$ekey] = $data_array[$ekey];
				break;
			case 'product_attribute_position' :
				$attribute_position[$ekey] = $data_array[$ekey];
				break;
			case 'product_attribute_taxonomy' :
				$attribute_taxonomy[$ekey] = $data_array[$ekey];
				break;
			case 'sale_price_dates_from' :
				$metaData['_sale_price_dates_from'] = $data_array[$ekey];
				break;
			case 'sale_price_dates_to' :
				$metaData['_sale_price_dates_to'] = $data_array[$ekey];
				break;
			case 'backorders' :
				$backorders = '';
				if ($data_array[$ekey] == 1) {
					$backorders = 'no';
				}
				if ($data_array[$ekey] == 2) {
					$backorders = 'notify';
				}
				if ($data_array[$ekey] == 3) {
					$backorders = 'yes';
				}
				$metaData['_backorders'] = $backorders;
				break;
			case 'manage_stock' :
				$metaData['_manage_stock'] = $data_array[$ekey];
				break;
			case 'low_stock_threshold' :
				$metaData['_low_stock_amount'] = $data_array[$ekey];
				break;
			case 'file_paths' :
				$metaData['_file_paths'] = $data_array[$ekey];
				break;
			case 'download_limit' :
				$metaData['_download_limit'] = $data_array[$ekey];
				break;
			case 'comment_status' :
				$status = $data_array[$ekey];
				$wpdb->get_results("UPDATE {$wpdb->prefix}posts SET comment_status = '$status' WHERE id = '$pID' ");
				break;
			case 'menu_order' :
				$menu_order = $data_array[$ekey];
				$wpdb->get_results("UPDATE {$wpdb->prefix}posts SET menu_order = '$menu_order' WHERE id = '$pID' ");
				break;	
			case 'download_expiry' :
				$metaData['_download_expiry'] = $data_array[$ekey];
				break;
			case 'download_type' :
				$metaData['_download_type'] = $data_array[$ekey];
				break;
			case 'product_url' :
				$metaData['_product_url'] = $data_array[$ekey];
				break;
			case 'button_text' :
				$metaData['_button_text'] = $data_array[$ekey];
				break;
			case 'product_type' :

				$product_type = 'simple';
				if ($data_array[$ekey] == 1) {
					$product_type = 'simple';
				}
				if ($data_array[$ekey] == 2) {
					$product_type = 'grouped';
				}
				if ($data_array[$ekey] == 3) {
					$product_type = 'external';
				}
				if ($data_array[$ekey] == 4) {
					$product_type = 'variable';
				}
				if ($data_array[$ekey] == 5) {
					$product_type = 'subscription';
				}
				if ($data_array[$ekey] == 6) {
					$product_type = 'variable-subscription';
				}
				if ($data_array[$ekey] == 7) {
					$product_type = 'bundle';
				}
				$core_instance->detailed_log[$line_number][' Type of Product'] = $product_type;

				wp_set_object_terms($pID, $product_type, 'product_type');
				break;
			case 'product_shipping_class' :
			case 'variation_shipping_class' :

				$class_name = $data_array[$ekey];
				$class = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms where name = '{$class_name}' ");
				if(!empty($class)) {
				$class_id = $class[0]->term_id;

				if ($product = wc_get_product($pID)) {
					$product->set_shipping_class_id($class_id);
					$product->save();
				}
			}
				break;
			case 'sold_individually' :
				$metaData['_sold_individually'] = $data_array[$ekey];
				break;

			case 'default_attributes' :
				if ($data_array[$ekey]) {
					$dattribute = explode(',',$data_array[$ekey]);
					foreach($dattribute as $dattrkey){
						$def_attribute = explode('|',$dattrkey);

						$def_attr_label = $def_attribute[0];
						$attri_name = $wpdb->get_results( "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies where attribute_label = '$def_attr_label' ");
						$def_attribute_lower = 'pa_' . $attri_name[0]->attribute_name;

						$default_attribute_value = $def_attribute[1];
						$def_attri_value = $wpdb->get_var("SELECT slug FROM {$wpdb->prefix}terms WHERE name = '$default_attribute_value' ");

						$defAttribute[$def_attribute_lower] = $def_attri_value;	
					}
				}
				break;

			case 'custom_attributes' :
				if ($data_array[$ekey]) {					
					$cust_att_value=$data_array[$ekey];
					$excerpt = [];
					$vartitle = [];
					$cusattribute = explode(',',$cust_att_value);	
					foreach($cusattribute as $cusattrkey){
						$cus_attribute = explode('|',$cusattrkey);

						$cus_attribute_label = $cus_attribute[0];
						$attri_name = $wpdb->get_results( "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies where attribute_label = '$cus_attribute_label' ");
						$custom_attributes_name='attribute_pa_'.$attri_name[0]->attribute_name;
						$cus_attribute_lower = 'pa_' . $attri_name[0]->attribute_name;

						$cus_attribute_value = $cus_attribute[1];
						$cus_attri_value = $wpdb->get_var("SELECT slug FROM {$wpdb->prefix}terms WHERE name = '$cus_attribute_value' ");
						$result = update_post_meta($pID, $custom_attributes_name, $cus_attri_value);												
						if($result){
							$excerpt[] =  $attri_name[0]->attribute_name . ': ' . $cus_attri_value;
							$vartitle[] = $cus_attri_value;							
						}
						if(empty($cus_attri_value)){
							$cus_attri_value = '';
						}
						$cusAttribute[$cus_attribute_lower] = $cus_attri_value;
					}
					//Update the post table after variation meta						
					$product_id = $wpdb->get_var("select post_parent from {$wpdb->prefix}posts where id = $pID"); 							
					if($product_id && !empty($vartitle) && !empty($excerpt)){
						$excerpt = implode(", ",$excerpt);
						$vartitle = implode(", ",$vartitle);
						$title = $wpdb->get_var("select post_title from {$wpdb->prefix}posts where id = $product_id");						
						$title = $title . " - " . $vartitle;
						$wpdb->update( $wpdb->prefix . 'posts' , 
						array( 
							'post_title' => $title,
							'post_excerpt' => $excerpt
						) , 
						array( 'id' => $pID,	//variable id
						) 
					);
					}																					
				}
				break;
			case 'product_tag' :
				$tags[$ekey] = $data_array[$ekey];
				$core_instance->detailed_log[$line_number][' Tags'] = $data_array[$ekey];
				break;
			case 'product_category' :
				$categories[$ekey] = $data_array[$ekey];
				$core_instance->detailed_log[$line_number][' Categories'] = $data_array[$ekey];
				break;
			case 'downloadable_files' :
				$downloadable_files = '';
				if ($data_array[$ekey]) {
					$exp_key = array();
					$exploded_file_data = explode('|', $data_array[$ekey]);
					foreach($exploded_file_data as $file_datas){
						$exploded_separate = explode(',', $file_datas);

						$convert_value = $exploded_separate[1];
						$file_name = hash_hmac('md5', "$convert_value" , 'secret');

						$exp_key[$file_name]['id'] = $file_name;
						$exp_key[$file_name]['name'] = $exploded_separate[0];
						$exp_key[$file_name]['file'] = $exploded_separate[1];
						$downloadable_files = $exp_key;
					}
				}
				$metaData['_downloadable_files'] = $downloadable_files;
				break;
			case 'crosssell_ids' :
				$crosssellids = '';
				if ($data_array[$ekey]) {
					$exploded_crosssell_ids = explode(',', $data_array[$ekey]);
					$crosssellids = $exploded_crosssell_ids;
				}
				if (is_array($crosssellids) || is_object($crosssellids)){
					foreach($crosssellids as $crosssell_ids){
						if(is_numeric($crosssell_ids)){
							$product_id = $crosssell_ids;	
						}
						else{
							$product_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'product' AND post_title = '$crosssell_ids' order by ID Desc ");
						}
						$crosssell_id[]=$product_id;	
					}
				
				$metaData['_crosssell_ids'] = $crosssell_id;
				}
				break;
			case 'upsell_ids' :
				$upcellids = '';
				if ($data_array[$ekey]) {
					$exploded_upsell_ids = explode(',', $data_array[$ekey]);
					$upcellids = $exploded_upsell_ids;
				}
				if (is_array($upcellids) || is_object($upcellids)){
					foreach($upcellids as $upsell_ids){
						if(is_numeric($upsell_ids)){
							$product_id = $upsell_ids;	
						}
						else{
							$product_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'product' AND post_title = '$upsell_ids' order by ID Desc ");
						}
						$upsell_id[]=$product_id;	
					}
					
				$metaData['_upsell_ids'] = $upsell_id;
				}
				break;

			case 'thumbnail_id' :
				if (is_numeric($data_array[$ekey])) {
					$metaData['_thumbnail_id'] = $data_array[$ekey];
				}else{

				}
				break;
               
				//WooCommerce Chained Products Fields
			case 'chained_product_detail' :
				$arr = array();
				$cpid_key = array();
				if ($data_array[$ekey]) {
					$chainedid = explode('|', $data_array[$ekey]);
					
					foreach ($chainedid as $unitid ) {
						$id = $unitid;
						
						$chainedunit = explode(',',$unitid);
						if(is_numeric($chainedunit[0])){
							$chainid = trim($chainedunit[0]);
							$unit = ltrim($chainedunit[1],' ');
							$query_result = $wpdb->get_results($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$chainedunit[0]));
							$product_name = $query_result[0]->post_title;
							$cpid_key[$chainid]['unit'] = $unit;
							$cpid_key[$chainid]['product_name'] = $product_name;
							$cpid_key[$chainid]['priced_individually'] = 'no';
							$arr[] = $chainedunit[0];
						}
						else{
							
							$query_result = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_title = %s",$chainedunit[0]));
							$product_id = $query_result[0]->ID;
							$unit = ltrim($chainedunit[1],' ');
						
							$cpid_key[$product_id]['unit'] = $unit;
							$cpid_key[$product_id]['product_name'] = $chainedunit[0];
							$cpid_key[$product_id]['priced_individually'] = 'no';
							$arr[] = $product_id;
						}
						
					}
					$chained_product_detail = $cpid_key;
				} else {
					$chained_product_detail = '';
				}
				$metaData['_chained_product_detail'] = $chained_product_detail;
				$metaData['_chained_product_ids'] = $arr;
				break;
				// $arr = array();
				// $cpid_key = array();
				// if ($data_array[$ekey]) {
				// 	$chainedid = explode('|', $data_array[$ekey]);
				// 	foreach ($chainedid as $unitid ) {
				// 		$id = $unitid;
				// 		$query_result = $wpdb->get_results($wpdb->prepare("select post_title from {$wpdb->prefix}posts where ID = %d",$id));
				// 		$product_name = $query_result[0]->post_title;
				// 		$cpid_key[$id]['unit'] = $unitid;
				// 		$cpid_key[$id]['product_name'] = $product_name;
				// 		$cpid_key[$id]['priced_individually'] = 'no';
				// 		$arr[] = $unitid;
				// 	}
				// 	$chained_product_detail = $cpid_key;
				// } else {
				// 	$chained_product_detail = '';
				// }
				// $metaData['_chained_product_detail'] = $chained_product_detail;
				// $metaData['_chained_product_ids'] = $arr;
				// break;
			case 'chained_product_manage_stock' :
				$metaData['_chained_product_manage_stock'] = $data_array[$ekey];
				break;
				//WooCommerce Product Retailers Fields
			case 'wc_product_retailers_retailer_only_purchase' :
				$metaData['_wc_product_retailers_retailer_only_purchase'] = $data_array[$ekey];
				break;
			case 'wc_product_retailers_use_buttons' :
				$metaData['_wc_product_retailers_use_buttons'] = $data_array[$ekey];
				break;
			case 'wc_product_retailers_product_button_text' :
				$metaData['_wc_product_retailers_product_button_text'] = $data_array[$ekey];
				break;
			case 'wc_product_retailers_catalog_button_text' :
				$metaData['_wc_product_retailers_catalog_button_text'] = $data_array[$ekey];
				break;
			case 'wc_product_retailers_id' :
				$retailer_id[$ekey] = $data_array[$ekey];
				break;
			case 'wc_product_retailers_price' :
				$retailer_price[$ekey] = $data_array[$ekey];
				break;
			case 'wc_product_retailers_url' :
				$retailer_url[$ekey] = $data_array[$ekey];
				break;
				//WooCommerce Product Add-ons Fields
			case 'product_addons_exclude_global' :
				$metaData['_product_addons_exclude_global'] = $data_array[$ekey];
				break;
			case 'product_addons_group_name' :
				$product_addons[$ekey] = $data_array[$ekey];
				break;
			case 'product_addons_group_description' :
				$product_addons[$ekey] = $data_array[$ekey];
				break;
			case 'product_addons_type' :
				$product_addons[$ekey] = $data_array[$ekey];
				break;
			case 'product_addons_position' :
				$product_addons[$ekey] = $data_array[$ekey];
				break;
			case 'product_addons_required' :
				$product_addons[$ekey] = $data_array[$ekey];
				break;
			case 'product_addons_label_name' :
				$product_addons[$ekey] = $data_array[$ekey];
				break;
			case 'product_addons_price' :
				$product_addons[$ekey] = $data_array[$ekey];
				break;
			case 'product_addons_minimum' :
				$product_addons[$ekey] = $data_array[$ekey];
				break;
			case 'product_addons_maximum' :
				$product_addons[$ekey] = $data_array[$ekey];
				break;
				//WooCommerce Warranty Requests Fields
			case 'warranty_label' :
				$metaData['_warranty_label'] = $data_array[$ekey];
				break;
			case 'warranty_type' :
				$warranty[$ekey] = $data_array[$ekey];
				break;
			case 'warranty_length' :
				$warranty[$ekey] = $data_array[$ekey];
				break;
			case 'warranty_value' :
				$warranty[$ekey] = $data_array[$ekey];
				break;
			case 'warranty_duration' :
				$warranty[$ekey] = $data_array[$ekey];
				break;
			case 'warranty_addons_amount' :
				$warranty[$ekey] = $data_array[$ekey];
				break;
			case 'warranty_addons_value' :
				$warranty[$ekey] = $data_array[$ekey];
				break;
			case 'warranty_addons_duration' :
				$warranty[$ekey] = $data_array[$ekey];
				break;
			case 'no_warranty_option' :
				$warranty[$ekey] = $data_array[$ekey];
				break;
				//WooCommerce Pre-Orders Fields
			case 'preorders_enabled' :
				$metaData['_wc_pre_orders_enabled'] = $data_array[$ekey];
				break;
			case 'preorders_availability_datetime' :
				if ($data_array[$ekey]) {
					$datetime_value = strtotime($data_array[$ekey]);
				}
				else {
					$datetime_value = '';
				}
				$metaData['_wc_pre_orders_availability_datetime'] = $datetime_value;
				break;
			case 'preorders_fee' :
				$metaData['_wc_pre_orders_fee'] = $data_array[$ekey];
				break;
			case 'preorders_when_to_charge' :
				$metaData['_wc_pre_orders_when_to_charge'] = $data_array[$ekey];
				break;
				//woocommerce_coupons starting
			case 'discount_type' :
				$metaData['discount_type'] = $data_array[$ekey];
				break;
			case 'coupon_amount' :
				$metaData['coupon_amount'] = $data_array[$ekey];
				break;
			case 'individual_use' :
				$metaData['individual_use'] = $data_array[$ekey];
				break;
			case 'exclude_product_ids' :
				$metaData['exclude_product_ids'] = $data_array[$ekey];
				break;
			case 'product_ids' :
				$metaData['product_ids'] = $data_array[$ekey];
				break;
			case 'usage_limit' :
				$metaData['usage_limit'] = $data_array[$ekey];
				break;
			case 'usage_limit_per_user' :
				$metaData['usage_limit_per_user'] = $data_array[$ekey];
				break;
			case 'limit_usage_to_x_items' :
				$metaData['limit_usage_to_x_items'] = $data_array[$ekey];
				break;
			case 'expiry_date' :
				$metaData['date_expires'] = strtotime($data_array[$ekey]);
				break;
			case 'free_shipping' :
				$metaData['free_shipping'] = $data_array[$ekey];
				break;
			case 'exclude_sale_items' :
				$metaData['exclude_sale_items'] = $data_array[$ekey];
				break;
			case 'wildcard_value' :
				if(strpos($data_array[$ekey] , '|') !== false){
					$data= explode('|',$data_array[$ekey]);
					if(is_array($data)){
						foreach($data as $keys => $datameta){
							$metaData['wildcard_rules_'.$keys.'_value'] = $datameta;
						}
					}
				}
				else{
					$metaData['wildcard_rules_0_value'] = $data_array[$ekey];
				}

				break;
			case 'wildcard_type'  :
				if(strpos($data_array[$ekey] , '|') !== false){
					$data= explode('|',$data_array[$ekey]);
					if(is_array($data)){
						foreach($data as $key => $datameta){						
							$metaData['wildcard_rules_'.$key.'_type']=$datameta;							
						}
					}
					$metaData['wildcard_rules'] = count($data);
				}
				else{
					$metaData['wildcard_rules_0_type'] = $data_array[$ekey];
					$metaData['wildcard_rules'] = 1;
				}

				break;

			case 'minimum_amount' :
				$metaData['minimum_amount'] = $data_array[$ekey];
				break;
			case 'maximum_amount' :
				$metaData['maximum_amount'] = $data_array[$ekey];
				break;
			case 'customer_email' :
				$customer_email[$ekey] = $data_array[$ekey];
				break;
			case 'exclude_product_categories' :
				$exclude_product[$ekey] = $data_array[$ekey];
				break;
			case 'product_categories' :
				$product_cate[$ekey] = $data_array[$ekey];
				break;
				//woocommerce_orders starting
			case 'payment_method_title' :
				$metaData['_payment_method_title'] = $data_array[$ekey];
				break;
			case 'payment_method' :
				$metaData['_payment_method'] = $data_array[$ekey];
				break;
			case 'transaction_id' :
				$metaData['_transaction_id'] = $data_array[$ekey];
				break;
			case 'billing_first_name' :
				$metaData['_billing_first_name'] = $data_array[$ekey];
				break;
			case 'billing_last_name' :
				$metaData['_billing_last_name'] = $data_array[$ekey];
				break;
			case 'billing_company' :
				$metaData['_billing_company'] = $data_array[$ekey];
				break;
			case 'billing_address_1' :
				$metaData['_billing_address_1'] = $data_array[$ekey];
				break;
			case 'billing_address_2' :
				$metaData['_billing_address_2'] = $data_array[$ekey];
				break;
			case 'billing_city' :
				$metaData['_billing_city'] = $data_array[$ekey];
				break;
			case 'billing_postcode' :
				$metaData['_billing_postcode'] = $data_array[$ekey];
				break;
			case 'billing_state' :
				$metaData['_billing_state'] = $data_array[$ekey];
				break;
			case 'billing_country' :
				$metaData['_billing_country'] = $data_array[$ekey];
				break;
			case 'billing_phone' :
				$metaData['_billing_phone'] = $data_array[$ekey];
				break;
			case 'billing_email' :
				$metaData['_billing_email'] = $data_array[$ekey];
				break;
			case 'shipping_first_name' :
				$metaData['_shipping_first_name'] = $data_array[$ekey];
				break;
			case 'shipping_last_name' :
				$metaData['_shipping_last_name'] = $data_array[$ekey];
				break;
			case 'shipping_company' :
				$metaData['_shipping_company'] = $data_array[$ekey];
				break;
			case 'shipping_address_1' :
				$metaData['_shipping_address_1'] = $data_array[$ekey];
				break;
			case 'shipping_address_2' :
				$metaData['_shipping_address_2'] = $data_array[$ekey];
				break;
			case 'shipping_city' :
				$metaData['_shipping_city'] = $data_array[$ekey];
				break;
			case 'shipping_postcode' :
				$metaData['_shipping_postcode'] = $data_array[$ekey];
				break;
			case 'shipping_state' :
				$metaData['_shipping_state'] = $data_array[$ekey];
				break;
			case 'shipping_country' :
				$metaData['_shipping_country'] = $data_array[$ekey];
				break;
			case 'customer_user' :
				$metaData['_customer_user'] = $data_array[$ekey];
				break;
			case 'order_currency' :
				$metaData['_order_currency'] = $data_array[$ekey];
				break;
			case 'item_name' :
				$orderItem['order_item_name'] = explode(',', $data_array[$ekey]);
				break;
			case 'item_type' :
				$orderItem['order_item_type'] = explode(',', $data_array[$ekey]);
				break;
			case 'item_product_id' :
				$Item_metaDatas['_product_id'] = explode(',', $data_array[$ekey]);
				break;
			case 'item_variation_id' :
				$Item_metaDatas['_variation_id'] = explode(',', $data_array[$ekey]);
				break;
			case 'item_line_subtotal' :
				$Item_metaDatas['_line_subtotal'] = explode(',', $data_array[$ekey]);
				break;
			case 'item_line_subtotal_tax' :
				$Item_metaDatas['_line_subtotal_tax'] = explode(',', $data_array[$ekey]);
				break;
			case 'item_line_total' :
				$Item_metaDatas['_line_total'] = explode(',', $data_array[$ekey]);
				break;
			case 'item_line_tax' :
				$Item_metaDatas['_line_tax'] = explode(',', $data_array[$ekey]);
				break;
			case 'item_line_tax_data' :
				$Item_metaDatas['_line_tax_data'] = explode('|', $data_array[$ekey]);
				break;
			case 'item_tax_class' :
				$Item_metaDatas['_tax_class'] = explode(',', $data_array[$ekey]);
				break;
			case 'item_qty' :
				$Item_metaDatas['_qty'] = explode(',', $data_array[$ekey]);
				break;
			case 'fee_name' :
				$orderFee['order_item_name'] = explode(',', $data_array[$ekey]);
				break;
			case 'fee_type' :
				$orderFee['order_item_type'] = explode(',', $data_array[$ekey]);
				break;
			case 'fee_tax_class' :
				$Fee_metaDatas['_tax_class'] = explode(',', $data_array[$ekey]);
				break;
			case 'fee_line_total' :
				$Fee_metaDatas['_line_total'] = explode(',', $data_array[$ekey]);
				break;
			case 'fee_line_tax' :
				$Fee_metaDatas['_line_tax'] = explode(',', $data_array[$ekey]);
				break;
			case 'fee_line_tax_data' :
				$Fee_metaDatas['_line_tax_data'] = explode('|', $data_array[$ekey]);
				break;
			case 'fee_line_subtotal' :
				$Fee_metaDatas['_line_subtotal'] = explode(',', $data_array[$ekey]);
				break;
			case 'fee_line_subtotal_tax' :
				$Fee_metaDatas['_line_subtotal_tax'] = explode(',', $data_array[$ekey]);
				break;
			case 'shipment_name' :
				$Shipment_name['order_item_name'] = explode(',', $data_array[$ekey]);
				break;
			case 'shipment_method_id' :
				$Shipment_metaDatas['method_id'] = explode(',', $data_array[$ekey]);
				break;
			case 'shipment_cost' :
				$Shipment_metaDatas['cost'] = explode(',', $data_array[$ekey]);
				break;
			case 'shipment_taxes' :
				$Shipment_metaDatas['taxes'] = explode('|', $data_array[$ekey]);
				break;
				//woocommerce_redunds starting
			case 'refund_amount' :
				$metaData['_refund_amount'] = $data_array[$ekey];
				break;
			case 'order_shipping_tax' :
				$metaData['_order_shipping_tax'] = $data_array[$ekey];
				break;
			case 'order_tax' :
				$metaData['_order_tax'] = $data_array[$ekey];
				$taxData['tax_amount']  = $data_array[$ekey];
				break;
			case 'order_shipping' :
				$metaData['_order_shipping'] = $data_array[$ekey];
				break;
			case 'cart_discount' :
				$metaData['_cart_discount'] = $data_array[$ekey];
				break;
			case 'cart_discount_tax' :
				$metaData['_cart_discount_tax'] = $data_array[$ekey];
				break;
			case 'order_total' :
				$metaData['_order_total'] = $data_array[$ekey];
				break;
			default:
				if(empty($variation_id)){
				$metaData[$ekey] = $data_array[$ekey];
				}
				$metaData['_subscription_payment_sync_date'] = 'a:2:{s:3:"day";i:0;s:5:"month";i:0;}';
				break;
			}
		}
		if(isset($orderItem) && is_array($orderItem)){	
			foreach ($orderItem['order_item_name'] as $key => $value) {
				$value_order_item[$key]['order_item_name'] = $orderItem['order_item_name'][$key];
				$value_order_item[$key]['order_item_type'] = $orderItem['order_item_type'][$key];
			}

			foreach ($value_order_item as $key => $value) {
				if($value['order_item_type'] == 'tax'){
					$tax_name = $value['order_item_name'];
					$tax =explode('-1',$tax_name);
					$tax_label = $tax_name;
					$taxy =explode('-',$tax[0]);
					$count =count($taxy);
					$count --;
					global $wpdb;
					$res = $wpdb->get_row("SELECT tax_rate_id from {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_name = '$taxy[$count]'");
					$res1 = json_decode(json_encode($res),true);
					$items =$res1['tax_rate_id'];
				}
			}

			foreach ($orderItem['order_item_name'] as $key => $value) {
				foreach ($Item_metaDatas as $key1 => $value1) {
					$arr = [];
					if($key1 == '_line_tax_data'){
						$count =count($Item_metaDatas[$key1]);
						$l = $Item_metaDatas[$key1];
						$Item_metaDatas[$key1][$key]=isset($Item_metaDatas[$key1][$key])?$Item_metaDatas[$key1][$key]:'';
						$vals = $Item_metaDatas[$key1][$key];
					//	$val = explode(',',$vals);
						$val = explode(',',$vals);
						$items=isset($items)?$items:'';
						$arr['total'][$items] = $val[0];
						$val[1]=isset($val[1])?$val[1]:'';
						$arr['subtotal'][$items] = $val[1];
						$Item_metaDatas[$key1][$key] = $arr;
						$value_order_item_meta[$key][$key1] = $Item_metaDatas[$key1][$key];	
						
					}
					else{
						$Item_metaDatas[$key1][$key]=isset($Item_metaDatas[$key1][$key])?$Item_metaDatas[$key1][$key]:'';
						$value_order_item_meta[$key][$key1] = $Item_metaDatas[$key1][$key];
					}
					
				}
			}
			if($mode == 'Insert'){
				foreach ($value_order_item as $key => $value) {
					$oid = wc_add_order_item($pID, $value);
					
					foreach ($value_order_item_meta[$key] as $itemkey => $itemvalue) {
						if($value['order_item_type'] == 'tax'){
							$tax_name = $value['order_item_name'];
							$tax =explode('-1',$tax_name);
							$taxy =explode('-',$tax[0]);
							$count =count($taxy);
							$count --;
							$tax_label = $tax_name;
							global $wpdb;
							$res = $wpdb->get_row("SELECT tax_rate_id,tax_rate,tax_rate_compound from {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_name = '$taxy[$count]'");
							$res1 = json_decode(json_encode($res),true);
						
							foreach($taxData as $taxkey => $taxval){
								wc_add_order_item_meta($oid, $taxkey, $taxval);
	
							}
							wc_add_order_item_meta($oid, 'rate_id', $res1['tax_rate_id']);
							wc_add_order_item_meta($oid, 'label', $taxy[$count]);
							wc_add_order_item_meta($oid, 'compound',$res1['compound'] );
							wc_add_order_item_meta($oid, 'rate_percent', $res1['tax_rate']);
						}
						else{
							if(!empty($itemvalue)){
						     	wc_add_order_item_meta($oid, $itemkey, $itemvalue);

							}
						}
					}
				}		
			}
			if($mode == 'Update'){
				$order_line = wc_get_order($pID);
				$order_line_ids = array_keys($order_line->get_items());
				$i = 0;
				$order_line_ids_arr = array();
				foreach ($value_order_item as $key => $value) {
					if(empty($order_line_ids[$i])){
						$oid = wc_add_order_item($pID, $value);
						foreach ($value_order_item_meta[$key] as $itemkey => $itemvalue) {
							wc_add_order_item_meta($oid, $itemkey, $itemvalue);
						}
					}else{
						wc_update_order_item($order_line_ids[$i], $value);
						foreach ($value_order_item_meta[$key] as $itemkey => $itemvalue) {
							wc_update_order_item_meta($order_line_ids[$i], $itemkey, $itemvalue);
						}
					}
					$order_line_ids_arr[] = $order_line_ids[$i];
					$i++;
				}
				if(count($order_line->get_items()) > count($value_order_item)){
					foreach($order_line->get_items() as $order_lines_ids => $order_lines_values){
						if(!in_array($order_lines_ids ,$order_line_ids_arr )){
							wc_delete_order_item($order_lines_ids);
							wc_delete_order_item_meta( $order_lines_ids, 'line_item');
						}
					}
				}		
			}
		}

		if(isset($orderFee) && is_array($orderFee)){
			foreach ($orderFee['order_item_name'] as $key => $value) {
				$value_order_fee[$key]['order_item_name'] = $orderFee['order_item_name'][$key];
				$value_order_fee[$key]['order_item_type'] = $orderFee['order_item_type'][$key];
			}
			foreach ($orderFee['order_item_name'] as $key => $value) {	
				foreach ($Fee_metaDatas as $key1 => $value1) {
					$value_order_fee_meta[$key][$key1] = $Fee_metaDatas[$key1][$key];
				}
			}

			if($mode == 'Insert'){
				foreach ($value_order_fee as $key => $value) {
					$oid = wc_add_order_item($pID, $value);	
					foreach ($value_order_fee_meta[$key] as $feekey => $feevalue) {	
						wc_add_order_item_meta($oid, $feekey, $feevalue);
					}
				}
			}
			if($mode == 'Update'){
				$order_fee = wc_get_order($pID);
				$order_fee_ids = array_keys($order_fee->get_items('fee'));
				$i = 0;
				$order_fee_ids_arr = array();
				foreach ($value_order_fee as $key => $value) {
					if(empty($order_fee_ids[$i])){
						$oid = wc_add_order_item($pID, $value);	
						foreach ($value_order_fee_meta[$key] as $feekey => $feevalue) {	
							wc_add_order_item_meta($oid, $feekey, $feevalue);
						}
					}else{
						wc_update_order_item($order_fee_ids[$i], $value);
						foreach ($value_order_fee_meta[$key] as $feekey => $feevalue) {
							wc_update_order_item_meta($order_fee_ids[$i], $feekey, $feevalue);
						}
					}
					$order_fee_ids_arr[] = $order_fee_ids[$i];
					$i++;
				}	
				if(count($order_fee->get_items('fee')) > count($value_order_fee)){
					foreach($order_fee->get_items('fee') as $fees_ids => $fees_ids_value){
						if(!in_array($fees_ids , $order_fee_ids_arr )){
							wc_delete_order_item($fees_ids);
							wc_delete_order_item_meta( $fees_ids, 'fee');
						}
					}
				}
			}
		}

		if(isset($Shipment_name) && is_array($Shipment_name)){
			$value_shipment[] = [];
			foreach ($Shipment_name['order_item_name'] as $key => $value) {
				if(!empty($value)){
					$value_shipment[$key]['order_item_name'] = $Shipment_name['order_item_name'][$key];
					$value_shipment[$key]['order_item_type'] = 'shipping';
				}
			}
			foreach ($Shipment_name['order_item_name'] as $key => $value) {
				foreach ($Shipment_metaDatas as $key1 => $value1) {
					if(!empty($value1)){
						$value_shipment_meta[$key][$key1] = $Shipment_metaDatas[$key1][$key];
					}
				}
			}
			if($mode == 'Insert' && !empty($value_shipment)){
				
				foreach ($value_shipment as $key => $value) {
					$oid = wc_add_order_item($pID, $value);
					foreach ($value_shipment_meta[$key] as $shipkey => $shipvalue) {
						wc_add_order_item_meta($oid, $shipkey, $shipvalue);
					}
				}
			}
			if($mode == 'Update' && !empty($value_shipment)){
				$order_ship = wc_get_order($pID);
				$order_ship_ids = array_keys($order_ship->get_items('shipping'));
				$i = 0;
				$order_ship_ids_arr = array();
				foreach ($value_shipment as $key => $value) {	
					if(empty($order_ship_ids[$i])){
						$oid = wc_add_order_item($pID, $value);
						foreach ($value_shipment_meta[$key] as $shipkey => $shipvalue) {
							wc_add_order_item_meta($oid, $shipkey, $shipvalue);
						}
					}else{
						wc_update_order_item($order_ship_ids[$i], $value);
						foreach ($value_shipment_meta[$key] as $shipkey => $shipvalue) {
							wc_update_order_item_meta($order_ship_ids[$i], $shipkey, $shipvalue);
						}
					}	
					$order_ship_ids_arr[] = $order_ship_ids[$i];
					$i++;
				}
				if(is_countable($order_ship->get_items('shipping')) && count($order_ship->get_items('shipping')) > is_countable($value_shipment)&&count($value_shipment)){
					foreach($order_ship->get_items('shipping') as $ship_ids => $ship_ids_value){
						if(!in_array($ship_ids ,$order_ship_ids_arr )){
							wc_delete_order_item($ship_ids);
							wc_delete_order_item_meta( $ship_ids, 'shipping');
						}
					}
				}
			}
		}

		if (!empty($customer_email)) {
			$exploded_email = explode(',', $customer_email['customer_email']);
			foreach ($exploded_email as $cus_email) {
				$metaData['customer_email'][] = $cus_email;
			}
		}
		if(!empty($exclude_product)) {
			$exploded_exclude = explode(',', $exclude_product['exclude_product_categories']);
			foreach ($exploded_exclude as $exp_cat) {
				$metaData['exclude_product_categories'][] = $exp_cat;
			}
		}
		if(!empty($product_cate)) {
			$exploded_cate = explode(',', $product_cate['product_categories']);
			foreach ($exploded_cate as $pro_cat) {
				$metaData['product_categories'][] = $pro_cat;
			}
		}
		if (!empty($product_image_gallery)) {
			$exploded_gallery_images = explode('|', $product_image_gallery['product_image_gallery']);
			$image_gallery = '';
			foreach ($exploded_gallery_images as $images) {
				$image_gallery .= $images . ',';
			}
			$Gallery = substr($image_gallery, 0, -1);
			$productImageGallery = $Gallery;
			if ($productImageGallery) {
				$metaData['_product_image_gallery'] = $productImageGallery;
			}
		}

		if (!empty($attribute_names)) {
			$exploded_att_names = explode('|', $attribute_names['product_attribute_name']);
			$get_csvpro_settings = get_option('sm_uci_pro_settings');	
			foreach ($exploded_att_names as $attr_name) {
				$attri_name = $wpdb->get_results( "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies where attribute_label = '$attr_name' ");
				
				if(empty($attri_name)  && ($get_csvpro_settings['woocomattr'] == 'false')){
					$attr_value = wc_sanitize_taxonomy_name($attr_name);

					$wpdb->query("insert into {$wpdb->prefix}woocommerce_attribute_taxonomies(attribute_label,attribute_name,attribute_type,attribute_orderby,attribute_public) values('".$attr_name."','".$attr_value."','select','menu_order','0')");
					$attri_name = $wpdb->get_results( "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies where attribute_label = '$attr_name' ");
					
					$attribute['name'][] = $attri_name[0]->attribute_name;
				}
				elseif(empty($attri_name) && ($get_csvpro_settings['woocomattr'] == 'true')){
					$attribute['name'][] = wc_attribute_taxonomy_name($attr_name);
				}
				else{
					$attribute['name'][] = $attri_name[0]->attribute_name;
				}	
			}
		}

		if (!empty($attribute_values)) {
			$exploded_att_values = explode(',', $attribute_values['product_attribute_value']);
			for($i=0 ; $i< count($exploded_att_values) ; $i++) {	
				$attribute['value'][] = $exploded_att_values[$i];

				$term_val = explode('|',$exploded_att_values[$i]);
				foreach ($term_val as $term_value) {
					$attribute_slug = 'pa_'. $attribute['name'][$i];
					$slug_val = $wpdb->get_results( "SELECT slug FROM {$wpdb->prefix}terms where name = '$term_value' ");
					$slug_value = isset($slug_val[0]->slug) ? $slug_val[0]->slug : '';
					if(empty($slug_value)){
						$slug_value = wc_sanitize_taxonomy_name($term_value);
						$wpdb->get_results("INSERT into {$wpdb->prefix}terms(name, slug) values ('{$term_value}', '{$slug_value}')");					
					}

					$term_id = $wpdb->get_var("SELECT term_id from {$wpdb->prefix}terms where name = '$term_value' and slug = '$slug_value'");
					$taxonomy_value = $wpdb->get_var("SELECT taxonomy from {$wpdb->prefix}term_taxonomy where term_id = '$term_id'");
						
					if(empty($taxonomy_value)){
					 	$wpdb->get_results("INSERT into {$wpdb->prefix}term_taxonomy (term_id, taxonomy) values ('{$term_id}', '{$attribute_slug}')");
					}
				}
			}
		}

		if (!empty($attribute_visible)) {
			$exploded_att_visible = explode('|', $attribute_visible['product_attribute_visible']);
			foreach ($exploded_att_visible as $attr_visible) {
				$attribute['is_visible'][] = $attr_visible;
			}
		}
		if (!empty($attribute_variation)) {
			$exploded_att_variation = explode('|', $attribute_variation['product_attribute_variation']);
			foreach ($exploded_att_variation as $attr_variation) {
				$attribute['is_variation'][] = $attr_variation;
			}
		}
		if (!empty($attribute_position)) {
			$exploded_att_position = explode('|', $attribute_position['product_attribute_position']);
			foreach ($exploded_att_position as $attr_position) {
				$attribute['position'][] = $attr_position;
			}
		}
		if(!empty($attribute_taxonomy)) {
			$exploded_att_taxonomy = explode('|', $attribute_taxonomy['product_attribute_taxonomy']);
			foreach ($exploded_att_taxonomy as $attr_taxonomy) {
				$attribute['is_taxonomy'][] = $attr_taxonomy;
			}
		}

		//WooCommerce Product Retailers Fields
		if (!empty($retailer_id)) {
			$exploded_ret_id = explode('|', $retailer_id['wc_product_retailers_id']);
			foreach ($exploded_ret_id as $ret_id) {
				$product_retailer['id'][] = $ret_id;
			}
		}
		if (!empty($retailer_price)) {
			$exploded_ret_price = explode('|', $retailer_price['wc_product_retailers_price']);
			foreach ($exploded_ret_price as $ret_price) {
				$product_retailer['product_price'][] = $ret_price;
			}
		}
		if (!empty($retailer_url)) {
			$exploded_ret_url = explode('|', $retailer_url['wc_product_retailers_url']);
			foreach ($exploded_ret_url as $ret_url) {
				$product_retailer['product_url'][] = $ret_url;
			}
		}
		if (!empty($product_retailer)) {
			$retailers_detail = array();
			$count_value = count($product_retailer['id']);
			for ($at = 0; $at < $count_value; $at++) {
				if (isset($product_retailer['id']) && isset($product_retailer['id'][$at])) {
					$retailers_detail[$product_retailer['id'][$at]]['id'] = $product_retailer['id'][$at];
				}
				if (isset($product_retailer['product_price']) && isset($product_retailer['product_price'][$at])) {
					$retailers_detail[$product_retailer['id'][$at]]['product_price'] = $product_retailer['product_price'][$at];
				}
				if (isset($product_retailer['product_url']) && isset($product_retailer['product_url'][$at])) {
					$retailers_detail[$product_retailer['id'][$at]]['product_url'] = $product_retailer['product_url'][$at];
				}
			}
		}
		if (!empty($retailers_detail)) {
			$metaData['_wc_product_retailers'] = $retailers_detail;
		}

		//WooCommerce Product Add-ons
		if (!empty($product_addons)) {
			$exploded_lab_name = explode('|', $product_addons['product_addons_label_name']);
			$count_lab_name = count($exploded_lab_name);
			for ($i = 0; $i < $count_lab_name; $i++) {
				$exploded_label_name = explode(',', $exploded_lab_name[$i]);
				foreach ($exploded_label_name as $lname) {
					$addons_option['label'][$i][] = $lname;
				}
			}
			$explode_lab_price = explode('|', $product_addons['product_addons_price']);
			$count_lab_price = count($explode_lab_price);
			for ($i = 0; $i < $count_lab_price; $i++) {
				$exploded_price = explode(',', $explode_lab_price[$i]);
				foreach ($exploded_price as $lprice) {

					$addons_option['price'][$i][] = $lprice;
				}
			}
			$expl_min = explode('|', $product_addons['product_addons_minimum']);
			$count_min = count($expl_min);
			for ($i = 0; $i < $count_min; $i++) {
				$exploded_min = explode(',', $expl_min[$i]);
				foreach ($exploded_min as $min) {
					$addons_option['min'][$i][] = $min;
				}
			}
			$expl_mac = explode('|',$product_addons['product_addons_maximum']);
			$count_max = count($expl_mac);
			for($i = 0; $i < $count_max; $i++){
				$exploded_max = explode(',', $expl_mac[$i]);
				foreach ($exploded_max as $max) {
					$addons_option['max'][] = $max;
				}
			}
			if(!empty($addons_option)) {
				$options_array = array();
				$cv = count($addons_option['label']);
				for ($a = 0; $a < $cv; $a++) {
					if (isset($addons_option['label']) && isset($addons_option['label'][$a])){
						$options_array[$a]['label'] =$addons_option['label'][$a];
					}
					if (isset($addons_option['price']) && isset($addons_option['price'][$a])){
						$options_array[$a]['price'] =$addons_option['price'][$a];
					}
					if (isset($addons_option['min']) && isset($addons_option['min'][$a])) {
						$options_array[$a]['min'] =$addons_option['min'][$a];
					}
					if (isset($addons_option['max']) && isset($addons_option['max'][$a])) {
						$options_array[$a]['max'] =$addons_option['max'][$a];
					}
				}
			}
			$exploded_group_name = explode('|', $product_addons['product_addons_group_name']);
			foreach ($exploded_group_name as $gname) {
				$addons['name'][] = $gname;
			}
			$exploded_group_description = explode('|', $product_addons['product_addons_group_description']);
			foreach ($exploded_group_description as $gdes) {
				$addons['description'][] = $gdes;
			}
			$exploded_position = explode('|', $product_addons['product_addons_position']);
			foreach ($exploded_position as $pos) {
				$addons['position'][] = $pos;
			}
			$exploded_type = explode('|', $product_addons['product_addons_type']);
			foreach ($exploded_type as $type) {
				$addons['type'][] = $type;
			}
			$exploded_required = explode('|', $product_addons['product_addons_required']);
			foreach ($exploded_required as $req) {
				$addons['required'][] = $req;
			}
			if(!empty($addons)) {
				$addons_array = array();
				$cnt = count($addons['name']);
				for ($b = 0; $b < $cnt; $b++) {
					if (isset($addons['name']) && isset($addons['name'][$b])) {
						$addons_array[$addons['name'][$b]]['name'] = $addons['name'][$b];
					}
					if (isset($addons['description']) && isset($addons['description'][$b])) {
						$addons_array[$addons['name'][$b]]['description'] = $addons['description'][$b];
					}
					if (isset($addons['type']) && isset($addons['type'][$b])) {
						$addons_array[$addons['name'][$b]]['type'] = $addons['type'][$b];
					}
					if (isset($addons['position']) && isset($addons['position'][$b])) {
						$addons_array[$addons['name'][$b]]['position'] = $addons['position'][$b];
					}
					if (isset($addons_option['label']) && isset($addons_option['label'][$b])){
						for ($i = 0; $i < count($addons_option['label'][$b]); $i++) {
							$addons_array[$addons['name'][$b]]['options'][$i]['label'] = $addons_option['label'][$b][$i];
						}
					}
					if (isset($addons_option['price']) && isset($addons_option['price'][$b])){
						for ($i = 0; $i < count($addons_option['price'][$b]); $i++) {
							$addons_array[$addons['name'][$b]]['options'][$i]['price'] = $addons_option['price'][$b][$i];
						}
					}
					if (isset($addons_option['min']) && isset($addons_option['min'][$b])) {
						for ($i = 0; $i < count($addons_option['min'][$b]); $i++) {
							$addons_array[$addons['name'][$b]]['options'][$i]['min'] = $addons_option['min'][$b][$i];
						}
					}
					if (isset($addons_option['max']) && isset($addons_option['max'][$b])) {
						for ($i = 0; $i < count($addons_option['max'][$b]); $i++) {
							$addons_array[$addons['name'][$b]]['options'][$i]['max'] = $addons_option['max'][$b][$i];
						}
					}
					if (isset($addons['required']) && isset($addons['required'][$b])) {
						$addons_array[$addons['name'][$b]]['required'] =$addons['required'][$b];
					}
				}
			}
			if(!empty($addons_array)) {
				$metaData['_product_addons'] = $addons_array;
			}
		}
		//WooCommerce Warranty Requests
		if (!empty($warranty)) {
			if ($warranty['warranty_type'] == 'included_warranty') {
				$warranty_result['type'] = $warranty['warranty_type'];
				$warranty_result['length'] = $warranty['warranty_length'];
				$warranty_result['value'] = $warranty['warranty_value'];
				$warranty_result['duration'] = $warranty['warranty_duration'];
				$metaData['_warranty'] = $warranty_result;
			}else if ( $warranty['warranty_type'] == 'addon_warranty' ) {
				if($warranty['warranty_addons_amount'] != '') {
					$addon_amt = explode('|', $warranty['warranty_addons_amount']);
					foreach ($addon_amt as $amt) {
						$warranty_addons['amount'][] = $amt;
					}
				}
				if($warranty['warranty_addons_value'] != '') {
					$addon_val = explode('|', $warranty['warranty_addons_value']);
					foreach ($addon_val as $val) {
						$warranty_addons['value'][] = $val;
					}
				}
				if($warranty['warranty_addons_duration'] != '') {
					$addon_dur = explode('|', $warranty['warranty_addons_duration']);
					foreach ($addon_dur as $dur) {
						$warranty_addons['duration'][] = $dur;
					}
				}
				if (!empty($warranty_addons)) {
					$warranty_addons_detail = array();
					$addon_count = count($warranty_addons['amount']);
					for ($ad = 0; $ad < $addon_count; $ad++) {
						if (isset($warranty_addons['amount']) && isset($warranty_addons['amount'][$ad])) {
							$warranty_addons_detail[$warranty_addons['amount'][$ad]]['amount'] = $warranty_addons['amount'][$ad];
						}
						if (isset($warranty_addons['value']) && isset($warranty_addons['value'][$ad])) {
							$warranty_addons_detail[$warranty_addons['amount'][$ad]]['value'] = $warranty_addons['value'][$ad];
						}
						if (isset($warranty_addons['duration']) && isset($warranty_addons['duration'][$ad])) {
							$warranty_addons_detail[$warranty_addons['amount'][$ad]]['duration'] = $warranty_addons['duration'][$ad];
						}
					}
				}
				if (!empty($warranty_addons_detail)) {
					$warranty_result['type'] = $warranty['warranty_type'];
					$warranty_result['addons'] = $warranty_addons_detail;
					$warranty_result['no_warranty_option'] = $warranty['no_warranty_option'];
					$metaData['_warranty'] = $warranty_result;
				}
			}else {
				$metaData['_warranty'] = '';
			}
		}
		//attributes
		if (!empty($attribute)) {			
			$product_attributes = $exploded_attribute_value = array();
			if(isset($attribute['name'])) {
				$attr_count = count($attribute['name']);
			}
			if(isset($attr_count)){
				for ($att = 0; $att < $attr_count; $att++) {
					$get_csvpro_settings = get_option('sm_uci_pro_settings');

					$woo_attribute_name = $attribute['name'][$att];
					$check_for_existing_attributes = $wpdb->get_var("SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '$woo_attribute_name' ");
					
					if(empty($check_for_existing_attributes)){
						$attrlabel = $attribute['name'][$att];
					}
					else{
						$attrlabel ='pa_'. $attribute['name'][$att];
					}
					
					if (isset($attribute['name']) && isset($attribute['name'][$att])) {
						$product_attributes[$attrlabel]['name'] = $attrlabel;
					}
					if (isset($attribute['value']) && isset($attribute['value'][$att])) {
						$product_attributes[$attrlabel]['value'] = $attribute['value'][$att];
					} else {
						$product_attributes[$attrlabel]['value'] = '';
					}
					if (isset($attribute['position']) && isset($attribute['position'][$att])){
						$product_attributes[$attrlabel]['position'] = $attribute['position'][$att];
					} else {
						$product_attributes[$attrlabel]['position'] = 0;
					}
					if (isset($attribute['is_visible']) && isset($attribute['is_visible'][$att])) {
						$visible=$attribute['is_visible'][$att];
						$product_attributes[$attrlabel]['is_visible'] = intval($visible);
					} else
					{
						$product_attributes[$attrlabel]['is_visible'] = '';
					}

					if (isset($attribute['is_variation']) && isset($attribute['is_variation'][$att])) {
						$variation=$attribute['is_variation'][$att];

						$product_attributes[$attrlabel]['is_variation'] = intval($variation);
					}
					else
					{
						$product_attributes[$attrlabel]['is_variation'] = '';
					}

					if (isset($attribute['is_taxonomy']) && isset($attribute['is_taxonomy'][$att])) {
						$taxonomy=$attribute['is_taxonomy'][$att];
						$product_attributes[$attrlabel]['is_taxonomy'] =intval($taxonomy);
					}
					else
					{
						$product_attributes[$attrlabel]['is_taxonomy'] = 0;
					}

					if($import_type == 'WooCommerce Product Variations'){
						$product_attributes[$attrlabel]['is_taxonomy'] = 1;
					}
					// product attributes
					if (!empty($product_attributes)) {
						if($import_type == 'WooCommerce Product Variations'){
							$product_detail = $wpdb->get_col($wpdb->prepare("select post_parent from {$wpdb->prefix}posts where ID = %d", $pID));
							if(!empty($product_detail))
								$productID = $product_detail[0];
							else
								$productID = '';

							if($productID != '') {
								$explode_attr = explode('|' , $product_attributes[$attrlabel]['value']);
								$reg_attribute_id = wp_set_object_terms($productID, $explode_attr , $product_attributes[$attrlabel]['name']);
								$product_attributes[$attrlabel]['value'] = '';
								update_post_meta($productID, '_product_attributes', $product_attributes);
							}

							if(isset($metaData['_price'])){
								update_post_meta($productID, '_price', $metaData['_price']);
							}

						}
						else{
							$metaData['_product_attributes'] = $product_attributes;
						}
					
						//default attribute for variations
						if(!empty($defAttribute)){
							if($import_type == 'WooCommerce Product Variations'){
								$product_detail = $wpdb->get_col($wpdb->prepare("select post_parent from {$wpdb->prefix}posts where ID = %d",$pID));
								if(!empty($product_detail))
									$productID = $product_detail[0];
								else
									$productID = '';
								if($productID != ''){
									update_post_meta($productID,'_default_attributes',$defAttribute);
								}
							}
						}
						//custom attribute for variations

						if(empty($check_for_existing_attributes)){
							if(!empty($cusAttribute)){
								foreach($cusAttribute as $cusAttkey => $cusAttval){
									$metaData['attribute_'.$cusAttkey] = $cusAttval;
								}
							}	
						}
						else{
							if(!empty($cusAttribute)){
								foreach($cusAttribute as $cusAttkey => $cusAttval){
									$product_detail = $wpdb->get_col($wpdb->prepare("select post_parent from {$wpdb->prefix}posts where ID = %d",$pID));
									if(!empty($product_detail))
										$productID = $product_detail[0];
									else
										$productID = '';
									if($productID != ''){
										$wpdb->insert($wpdb->prefix.'term_relationships',
											array('object_id' => $productID,
												'term_taxonomy_id' => $term_id
											),
											array('%d','%d')
										);
									}
									$metaData['attribute_'.$cusAttkey] = $cusAttval;
								}
							}
						}
					}
				}
			} // WooCommerce attribute registration ends here
		}

		$get_csvpro_settings = get_option('sm_uci_pro_settings');	
		if (isset($metaData['_product_attributes']) && !empty($metaData['_product_attributes']) && $import_type !== 'WooCommerce Product Variations') {
			foreach($metaData['_product_attributes'] as $attrKey => $attrVal) {			
				if($get_csvpro_settings['woocomattr'] == 'true'){	
					$attrVal['name'] = str_replace( 'pa_' , '' , $attrVal['name']);
					$get_attributeLabel = $wpdb->get_results( "SELECT attribute_id, attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '{$attrVal['name']}'" );                                                         
					$attrlabel = trim($attrVal['name']);
					$attrslug = strtolower($attrlabel);
					$attrslug = preg_replace('/\s/', '-', $attrslug);
					$custom_attribute_name = "pa_" . $attrslug;
					$attrtype = 'text';
					$attrordr = 'menu_order';
					$attr_data = $attrVal['value'];
					$get_transistent_atrributes = get_option('_transient_wc_attribute_taxonomies');
					$get_count_transistent_attr = is_array($get_transistent_atrributes) ? count($get_transistent_atrributes) : 0;
					$count_transistent_attr = $get_count_transistent_attr + 1;

					if(!empty($get_transistent_atrributes)){
						foreach($get_transistent_atrributes as $tak => $tav) {
							$new_trans_attr_list[$tak] = new \stdClass();
							foreach($tav as $attr_reg_key => $attr_reg_val) {
								$new_trans_attr_list[$tak]->$attr_reg_key = $attr_reg_val;
							}
						}
					}                         
					if(empty($get_attributeLabel)) {
						$wpdb->insert("{$wpdb->prefix}woocommerce_attribute_taxonomies", array('attribute_name' => $attrslug, 'attribute_label' => $attrlabel, 'attribute_type' => $attrtype, 'attribute_orderby' => $attrordr));
						$attr_taxo_id = $wpdb->insert_id;
						if (!isset($new_trans_attr_list[$count_transistent_attr])) 
							$new_trans_attr_list[$count_transistent_attr] = new \stdClass();
						$new_trans_attr_list[$count_transistent_attr]->attribute_id = $attr_taxo_id;
						$new_trans_attr_list[$count_transistent_attr]->attribute_name = $attrslug;
						$new_trans_attr_list[$count_transistent_attr]->attribute_label = $attrlabel;
						$new_trans_attr_list[$count_transistent_attr]->attribute_type = $attrtype;
						$new_trans_attr_list[$count_transistent_attr]->attribute_orderby = $attrordr;
						$new_trans_attr_list[$count_transistent_attr]->attribute_public = 0;
						update_option('_transient_wc_attribute_taxonomies', $new_trans_attr_list);
						$split_line = explode('|', $attr_data);

						register_taxonomy($custom_attribute_name , 'product');
						$reg_attribute_id = wp_set_object_terms($pID, $split_line, $custom_attribute_name);		
						$woocomm_term_data = 'order_'.$custom_attribute_name; 
					} else {

						$attrId = $get_attributeLabel[0]->attribute_id;
						$custom_attribute_name = 'pa_' . $attrVal['name'];	
						$split_line = explode('|', $attr_data);	
						$reg_attribute_id = wp_set_object_terms($pID, $split_line, $custom_attribute_name);
					}

				} 

				elseif($get_csvpro_settings['woocomattr'] == 'false'){
					$attrVal['name'] = str_replace( 'pa_' , '' , $attrVal['name']);
					$get_attributeLabel = $wpdb->get_results( "SELECT attribute_id, attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '{$attrVal['name']}'" );                                                         
					$attrlabel = trim($attrVal['name']);
					$attrslug = strtolower($attrlabel);
					$attrslug = preg_replace('/\s/', '-', $attrslug);
					$custom_attribute_name = "pa_" . $attrslug;
					$attrtype = 'text';
					$attrordr = 'menu_order';
					$attr_data = $attrVal['value'];
					$get_transistent_atrributes = get_option('_transient_wc_attribute_taxonomies');
					$get_count_transistent_attr = is_array($get_transistent_atrributes) ? count($get_transistent_atrributes) : 0;
					$count_transistent_attr = $get_count_transistent_attr + 1;

					if(!empty($get_transistent_atrributes)){
						foreach($get_transistent_atrributes as $tak => $tav) {
							$new_trans_attr_list[$tak] = new \stdClass();
							foreach($tav as $attr_reg_key => $attr_reg_val) {
								$new_trans_attr_list[$tak]->$attr_reg_key = $attr_reg_val;
							}
						}
					} 
					if(empty($get_attributeLabel)){

					}else {
						$attrId = $get_attributeLabel[0]->attribute_id;
						$custom_attribute_name = 'pa_' . $attrVal['name'];	
						$split_line = explode('|', $attr_data);
						$reg_attribute_id = wp_set_object_terms($pID, $split_line, $custom_attribute_name);

						$metaData['_product_attributes'][$attrKey]['value'] = '';
					}                
				}	
			}

			if($get_csvpro_settings['woocomattr'] == 'true'){	
				foreach($metaData['_product_attributes'] as $attr_keys => $attr_values){	
					$metaData['_product_attributes'][$attr_keys]['value'] = '';
				}
			}	
		}
		
		if(!empty($variation_id)){
			for($i=0 ; $i< count($variation_id) ; $i++) {

				if(($import_type == 'WooCommerce Product' || $import_type == 'WooCommerce Product Variations') && isset($core_array['VARIATIONSKU'])){
					$core_array_sku = explode('->', $core_array['VARIATIONSKU']);
					$variationData['_sku'] = $core_array_sku[$i];
				}
	
				foreach ($data_array as $ekey => $eval) {
					$variation_eval = explode('->', $eval);
					switch ($ekey) {
					case 'variation_stock_qty' :
						$variationData['_stock'] = $variation_eval[$i];
						break;
					case 'variation_stock_status' :
	
						if(is_numeric($variation_eval[$i])){
							$stock_status = '';
							if ($variation_eval[$i] == 1) {
								$stock_status = 'instock';
							}
							if ($variation_eval[$i] == 2) {
								$stock_status = 'outofstock';
							}
							$variationData['_stock_status'] = $stock_status;
						}
						else{
							$variationData['_stock_status'] = $data_array[$ekey];
						}
						break;
					case 'variation_tax_status' :
						$tax_status = '';
						if ($variation_eval[$i] == 1) {
							$tax_status = 'taxable';
						}
						if ($variation_eval[$i] == 2) {
							$tax_status = 'shipping';
						}
						if ($variation_eval[$i] == 3) {
							$tax_status = 'none';
						}
						$variationData['_tax_status'] = $tax_status;
						break;
					case 'variation_tax_class' :
						   $variationData['_tax_class']=$variation_eval[$i];
						   break;
					case 'variation_backorders' :
						$backorders = '';
						if ($variation_eval[$i] == 1) {
							$backorders = 'no';
						}
						if ($variation_eval[$i] == 2) {
							$backorders = 'notify';
						}
						if ($variation_eval[$i] == 3) {
							$backorders = 'yes';
						}
						$variationData['_backorders'] = $backorders;
						break;
					case 'variation_manage_stock' :
						$variationData['_manage_stock'] = $variation_eval[$i];
						break;
					case 'variation_downloadable' :
						$variationData['_downloadable'] = $variation_eval[$i];
						break;
					case 'variation_download_limit' :
						$variationData['_download_limit'] = $variation_eval[$i];
						break;
					case 'variation_download_expiry' :
						$variationData['_ownload_expiry'] = $variation_eval[$i];
						break;
					case 'variation_sold_individually' :
						$variationData['_sold_individually'] = $variation_eval[$i];
						break;
					case 'variation_virtual' :
						$variationData['_virtual'] = $variation_eval[$i];
						break;
					case 'variation_description':
						$variationData['_variation_description'] = $variation_eval[$i];
						break;
					case 'variation_weight' :
						$variationData['_weight'] = $variation_eval[$i];
						break;
					case 'variation_length' :
						$variationData['_length'] = $variation_eval[$i];
						break;
					case 'variation_width' :
						$variationData['_width'] = $variation_eval[$i];
						break;
					case 'variation_height' :
						$variationData['_height'] = $variation_eval[$i];
						break;
					case 'variation_regular_price' :
						$variationData['_regular_price'] = $variation_eval[$i];
						if(empty($variationData['_regular_price'])){
							global $wpdb;
							$price = $wpdb->get_var("SELECT meta_value FROM .$wpdb->prefix.postmeta WHERE meta_key = '_price' AND post_id = $pID");
							$variationData['_price'] = $price;
							
							if($variationData['_price']){
								$variationData['_price'] = $variationData['_price'];
							}
							else{
								$variationData['_price'] = $variation_eval[$i];
							}
						}
						else{
							$variationData['_price'] = $variation_eval[$i];
						}
						break;
					case 'variation_sale_price' :
						if(empty($variation_eval[$i])){
							$variationData['_price']= $variationData['_regular_price'];
						}
						else{
							$variationData['_price'] = $variation_eval[$i];
							$variationData['_sale_price'] = $variation_eval[$i];
						}						
						break;	
					case 'variation_thumbnail_id' :
						if (is_numeric($variation_eval[$i])) {
							$variationData['_thumbnail_id'] = $variation_eval[$i];
						}
						break;	
					case 'variation_attributes' :
						if ($variation_eval[$i]) {
							$cusattribute = explode(',',$variation_eval[$i]);
							foreach($cusattribute as $cusattrkey){
								$cus_attribute = explode('|',$cusattrkey);
								
								$cus_attribute_label = $cus_attribute[0];
								$attri_name = $wpdb->get_results( "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies where attribute_label = '$cus_attribute_label' ");
								$cus_attribute_lower = $attri_name[0]->attribute_name;
	
								$cus_attribute_value = $cus_attribute[1];
								$cus_attri_value = $wpdb->get_var("SELECT slug FROM {$wpdb->prefix}terms WHERE name = '$cus_attribute_value' ");
								
								if(empty($cus_attri_value)){
									$cus_attri_value = '';
								}
	
								$variationData['attribute_'.$cus_attribute_lower] = $cus_attri_value;
							}
						}
						break;
					}
				}

				foreach ($variationData as $variation_key => $variation_value) {
					update_post_meta($variation_id[$i], $variation_key, $variation_value);
				}
			}
		}
		// Insert all meta information
		foreach ($metaData as $meta_key => $meta_value) {			
			update_post_meta($pID, $meta_key, $meta_value);
		}
	}	
}
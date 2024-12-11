<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\WCSV;

use Smackcoders\FCSV\CoreFieldsImport;
use Smackcoders\FCSV\ImportHelpers;
use Smackcoders\FCSV\MediaHandling;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class WooCommerceCoreImport {
    private static $woocommerce_core_instance = null,$media_instance;

    public static function getInstance() {
		if (WooCommerceCoreImport::$woocommerce_core_instance == null) {
			WooCommerceCoreImport::$woocommerce_core_instance = new WooCommerceCoreImport;
			WooCommerceCoreImport::$media_instance = new MediaHandling;
			return WooCommerceCoreImport::$woocommerce_core_instance;
		}
		return WooCommerceCoreImport::$woocommerce_core_instance;
    }

    public function woocommerce_variations_import($data_array , $mode , $check , $unikey ,$unikey_name, $line_number, $variation_count) {				
		global $wpdb;
		$log_table_name = $wpdb->prefix ."import_detail_log";
		$core_instance = CoreFieldsImport::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
		$updated_row_counts = $helpers_instance->update_count($unikey,$unikey_name);
		$skipped_count = $updated_row_counts['skipped'];
       
		$productInfo = '';
		$returnArr = array('MODE' => $mode , 'ID' => '');
		$product_id = isset($data_array['PRODUCTID']) ? $data_array['PRODUCTID'] : '';
		$parent_sku = isset($data_array['PARENTSKU']) ? $data_array['PARENTSKU'] : '';
		$variation_id =  isset($data_array['VARIATIONID']) ? $data_array['VARIATIONID'] : '';
		$variation_sku = isset($data_array['VARIATIONSKU']) ? $data_array['VARIATIONSKU'] : '';		
		if($product_id != '') {
			$variation_condition = 'insert_using_product_id';
		} 		
		elseif($parent_sku != '') {			
			$get_parent_product_id = $wpdb->get_results("select id from {$wpdb->prefix}posts where post_status != 'trash' and post_type = 'product' and id in (select post_id from {$wpdb->prefix}postmeta where meta_value = '$parent_sku')");									
			$count = count( $get_parent_product_id );
			$key = 0;
			if ( ! empty( $get_parent_product_id ) ) {				
				$product_id = $get_parent_product_id[$key]->id;
				//Check whether the product is variable type
				$term_details = wp_get_object_terms($product_id,'product_type');
				if((!empty($term_details)) && ($term_details[0]->name != 'variable')){
					
					$core_instance->detailed_log[$line_number]['Message'] = "Skipped,Product is not variable in type.";
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
					return array('MODE' => $mode,'ID' => '');					
				}
			} else {
				$product_id = '';
				$core_instance->detailed_log[$line_number]['Message'] = "Skipped,Product is not available.";
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
					return array('MODE' => $mode,'ID' => '');
			}			
			if($mode == 'Insert'){
				$variation_condition = 'insert_using_product_sku';
			}
			if($variation_sku != '' && $mode == 'Update'){
				$variation_condition = 'update_using_variation_sku';
			}
			if($variation_id != ''){
				$variation_condition = 'update_using_variation_id';
			}
		}
		elseif($parent_sku == '' && ($variation_sku != '' || $variation_id != '')){
			if($variation_sku != ''){
				$variation_condition = 'update_using_variation_sku';
			}
			if($variation_id != ''){
				$variation_condition = 'update_using_variation_id';
			}
		}

		if($variation_sku != '' && $variation_id != ''){
			update_post_meta($variation_id, '_sku', $variation_sku);
		}
        
		if($product_id != '') {
			$is_exist_product = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}posts where ID = %d", $product_id));
			if(!empty($is_exist_product) && $is_exist_product[0]->ID == $product_id) {
				$productInfo = $is_exist_product[0];
			} else {
				#return $returnArr;
			}
		}			
		if(isset($variation_condition)){
		switch ($variation_condition) {
			case 'update_using_variation_id_and_sku':
				
				$get_variation_data = $wpdb->get_results( $wpdb->prepare( "select DISTINCT pm.post_id from {$wpdb->prefix}posts p join {$wpdb->prefix}postmeta pm on p.ID = pm.post_id where p.ID = %d and p.post_type = %s and pm.meta_value = %s", $variation_id, 'product_variation', $variation_sku ) );

				if ( ! empty( $get_variation_data ) && $get_variation_data[0]->post_id == $variation_id ) {
					$returnArr = $this->importVariationData( $product_id, $variation_id, 'update_using_variation_id_and_sku' ,$unikey  , $unikey_name, $line_number, $variation_count,$get_variation_data);
				} else {
					$returnArr = $this->importVariationData( $product_id, $variation_id, 'default' ,$unikey , $unikey_name, $line_number, $variation_count, $productInfo);
				}
				break;
			case 'update_using_variation_id':
				
				$get_variation_data = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}posts where ID = %d and post_type = %s", $variation_id, 'product_variation' ) );
				if ( ! empty( $get_variation_data ) && $get_variation_data[0]->ID == $variation_id ) {
					$returnArr = $this->importVariationData( $product_id, $variation_id, 'update_using_variation_id' ,$unikey  , $unikey_name, $line_number, $variation_count, $get_variation_data);
				} else {
					$returnArr = $this->importVariationData( $product_id, $variation_id, 'default',$unikey , $unikey_name, $line_number, $variation_count, $productInfo );
				}
				break;
			case 'update_using_variation_sku':								
				$variation_data = $wpdb->get_results("select post_id from {$wpdb->prefix}postmeta where meta_value = '$variation_sku' and post_id in (select id from {$wpdb->prefix}posts where post_type = 'product_variation' and post_status != 'trash' and post_parent = $product_id)");
				$variation_id = !empty($variation_data) ? $variation_data[0]->post_id : "";								
				if($variation_id)
				$get_variation_data = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}posts where ID = %d and post_type = %s", $variation_id, 'product_variation' ) );				
				else
				$get_variation_data = [];
				if ( ! empty( $get_variation_data ) && $get_variation_data[0]->ID == $variation_id) {
					$returnArr = $this->importVariationData( $product_id,$variation_id, 'update_using_variation_sku' ,$unikey  , $unikey_name, $line_number, $variation_count,$get_variation_data);
				} else {
					$returnArr = $this->importVariationData( $product_id, $variation_id, 'default' ,$unikey , $unikey_name, $line_number, $variation_count, $productInfo);
				}
				break;
			case 'insert_using_product_id':
				$returnArr = $this->importVariationData( $product_id, $variation_id, 'insert_using_product_id',$unikey , $unikey_name, $line_number, $variation_count,  $productInfo);
				break;
			case 'insert_using_product_sku':
				$returnArr = $this->importVariationData( $product_id, $variation_id, 'insert_using_product_sku',$unikey ,$unikey_name, $line_number, $variation_count, $productInfo );
				break;
			default:
				$returnArr = $this->importVariationData( $product_id, $variation_id, 'default',$unikey  ,$unikey_name, $line_number, $variation_count, $productInfo);
				break;
		}
	}
		return $returnArr;
	}

	public function importVariationData ($product_id, $variation_id, $type,$unikey , $unikey_name, $line_number, $variation_count, $exist_variation_data = array()) {		
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		global $core_instance;
		$log_table_name = $wpdb->prefix ."import_detail_log";

		$updated_row_counts = $helpers_instance->update_count($unikey,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];
		if($type == 'default' || $type == 'insert_using_product_id' || $type == 'insert_using_product_sku') {
			
			$get_count_of_variations = $wpdb->get_results( $wpdb->prepare( "select count(*) as variations_count from {$wpdb->prefix}posts where post_parent = %d and post_type = %s", $product_id, 'product_variation' ) );
			$variations_count = $get_count_of_variations[0]->variations_count;
			$menu_order_count = 0;
			if ($variations_count == 0) {
				$variations_count = '';
				$menu_order= 0 ;
			} else {
				$variations_count = $variations_count + 1;
				$menu_order_count = $variations_count - 1;
				$variations_count = '-' . $variations_count;
			}
			$get_variation_data = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}posts where ID = %d", $product_id));
			foreach($get_variation_data as $key => $val) {
				
				if($product_id == $val->ID){

					$variation_data = array();
					$variation_data['post_title'] = $val->post_title ;
					$variation_data['post_date'] = $val->post_date;
					$variation_data['post_type'] = 'product_variation';
					$variation_data['post_status'] = 'publish';
					$variation_data['comment_status'] = 'closed';
					$variation_data['ping_status'] = 'closed';
					$variation_data['menu_order'] = $menu_order_count;
					$variation_data['post_name'] = 'product-' . $val->ID . '-variation' . $variations_count;
					$variation_data['post_parent'] = $val->ID;
					
				}
			}
			//$variation_data=isset($variation_data)?$variation_data:'';
			$variationid = wp_insert_post($variation_data);					
			if(empty($variation_count)){
				$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Variation ID: ' . $variationid;
			}
			else{				
				$parent_id = $wpdb->get_var( "SELECT post_parent FROM {$wpdb->prefix}posts WHERE id = '$variationid' " );								
				$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Product ID: ' . $parent_id . '   Inserted Variation ID: ' . $variationid;				
			}
			$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");		
			$returnArr = array( 'ID' => $variationid, 'MODE' => 'Inserted' );
			return $returnArr;
		} elseif ($type == 'update_using_variation_id' || $type == 'update_using_variation_sku' || $type == 'update_using_variation_id_and_sku') {
			$core_instance->detailed_log[$line_number]['Message'] = 'Updated Variation ID: ' . $variation_id;
			$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey'");

			$returnArr = array( 'ID' => $variation_id, 'MODE' => 'Updated');			
			return $returnArr;
		}
	}

	public function woocommerce_orders_import($data_array , $mode , $check , $unikey , $unikey_name, $line_number) {
		$returnArr = array();	
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		global $core_instance;

		$log_table_name = $wpdb->prefix ."import_detail_log";

		$updated_row_counts = $helpers_instance->update_count($unikey,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];
		
		$data_array['post_type'] = 'shop_order';
		$arraykey = array_keys($data_array);
		
		if(!in_array('customer_note',$arraykey)){
			$data_array['post_excerpt'] = '';
		}
		else{
			$data_array['post_excerpt'] = $data_array['customer_note'];
		}
		if(isset($data_array['order_status'])) {
			$data_array['post_status'] = $data_array['order_status'];}
		/* Assign order date */
		if(!isset( $data_array['order_date'] )) {
			$data_array['post_date'] = current_time('Y-m-d H:i:s');
		} else {
			if(strtotime( $data_array['order_date'] )) {
				$data_array['post_date'] = date( 'Y-m-d H:i:s', strtotime( $data_array['order_date'] ) );
			} else {
				$data_array['post_date'] = current_time('Y-m-d H:i:s');
			}
		}
		if ($mode == 'Insert') {	
			$retID = wp_insert_post( $data_array );
			$mode_of_affect = 'Inserted';
			
			if(is_wp_error($retID) || $retID == '') {
				$core_instance->detailed_log[$line_number]['Message'] = "Can't insert this Order. " . $retID->get_error_message();
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
				return array('MODE' => $mode, 'ERROR_MSG' => $retID->get_error_message());
			}
			$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Order ID: ' . $retID;
			$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");

		} else {
			if ($mode == 'Update') {
				if($check == 'ORDERID'){
					$orderid = $data_array['ORDERID'];
					$post_type = $data_array['post_type'];
					$update_query = "select ID from {$wpdb->prefix}posts where ID = '$orderid' and post_type = '$post_type' order by ID DESC";
					$ID_result = $wpdb->get_results($update_query);

					if (is_array($ID_result) && !empty($ID_result)) {
						$retID = $ID_result[0]->ID;
						$data_array['ID'] = $retID;
						wp_update_post($data_array);
						$mode_of_affect = 'Updated';

						$core_instance->detailed_log[$line_number]['Message'] = 'Updated Order ID: ' . $retID;
						$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey'");	
					} else{
						
							$core_instance->detailed_log[$line_number]['Message'] = "Skipped.";
							$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
							return array('MODE' => $mode);
					}
				}else{
					$core_instance->detailed_log[$line_number]['Message'] = "Skipped.";
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
					return array('MODE' => $mode);
					
				}
			}
		}
		$returnArr['ID'] = $retID;
		$returnArr['MODE'] = $mode_of_affect;
		return $returnArr;
	}

	public function woocommerce_coupons_import($data_array , $mode , $check , $unikey , $unikey_name, $line_number) {
		global $wpdb; 
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		global $core_instance;
		$log_table_name = $wpdb->prefix ."import_detail_log";

		$updated_row_counts = $helpers_instance->update_count($unikey,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];

		$returnArr = array();
		
		$data_array['post_type'] = 'shop_coupon';
		$data_array['post_title'] = $data_array['coupon_code'];
		$data_array['post_name'] = $data_array['coupon_code'];
		if(isset($data_array['description'])) {
			$data_array['post_excerpt'] = $data_array['description'];
		}

		/* Post Status Options */
		if ( !empty($data_array['coupon_status']) ) {
			$data_array = $helpers_instance->assign_post_status( $data_array );
		} else {
			$data_array['coupon_status'] = 'publish';
		}

		if ($mode == 'Insert') {
			$retID = wp_insert_post($data_array);
			$mode_of_affect = 'Inserted';
			
			if(is_wp_error($retID) || $retID == '') {
				$core_instance->detailed_log[$line_number]['Message'] = "Can't insert this Coupon. " . $retID->get_error_message();
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
				return array('MODE' => $mode, 'ERROR_MSG' => $retID->get_error_message());
			}
			$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Coupon ID: ' . $retID;
			$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");

		} else {
				if($check == 'COUPONID'){
					$coupon_id = $data_array['COUPONID'];
					$post_type = $data_array['post_type'];
					$update_query = "select ID from {$wpdb->prefix}posts where ID = '$coupon_id' and post_type = '$post_type' and post_status not in('trash','draft') order by ID DESC";
					$ID_result = $wpdb->get_results($update_query);

					if (is_array($ID_result) && !empty($ID_result)) {
						$retID = $ID_result[0]->ID;
						$data_array['ID'] = $retID;
						wp_update_post($data_array);
						$mode_of_affect = 'Updated';

						$core_instance->detailed_log[$line_number]['Message'] = 'Updated Coupon ID: ' . $retID;
						$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey'");			
					} else{
						$core_instance->detailed_log[$line_number]['Message'] = "Skipped.";
						$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
						return array('MODE' => $mode);
					}
				}
				else{
					$core_instance->detailed_log[$line_number]['Message'] = "Skipped.";
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
					return array('MODE' => $mode);
				}
			//} 
		}
		$returnArr['ID'] = $retID;
		$returnArr['MODE'] = $mode_of_affect;
		return $returnArr;
	}

	public function woocommerce_refunds_import($data_array , $mode , $check  ,$unikey , $unikey_name, $line_number) {
		$returnArr = array();
		$mode_of_affect = 'Inserted';
		global $wpdb; 
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		global $core_instance;
		$log_table_name = $wpdb->prefix ."import_detail_log";

		$updated_row_counts = $helpers_instance->update_count($unikey,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];
		
		$parent_order_id = 0;
		$post_excerpt = '';
		if(isset($data_array['REFUNDID']))
			$order_id = $data_array['REFUNDID'];
		elseif(isset($data_array['post_parent']))
			$parent_order_id = $data_array['post_parent'];
		if(isset($data_array['post_excerpt']))
			$post_excerpt = $data_array['post_excerpt'];
		$get_order_id = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}posts where ID = %d", $parent_order_id));
		if(!empty($get_order_id)){
			$refund = $get_order_id[0]->ID;
			
			if(isset($refund)){
				$date_format = date('m-j-Y-Hi-a');
				$date_read = date('M j, Y @ H:i a');
				$data_array['post_title'] = 'Refund &ndash;' . $date_read; 
				$data_array['post_type'] = 'shop_order_refund';
				$data_array['post_parent'] = $parent_order_id;
				$data_array['post_status'] = 'wc-completed';
				$data_array['post_name'] = 'refund-'.$date_format;
				$data_array['guid'] = site_url() . '?shop_order_refund=' . 'refund-'.$date_format;
			}
		}
		if ($mode == 'Insert') {
			$retID = wp_insert_post( $data_array );

			update_post_meta($retID , '_refund_reason' , $post_excerpt);
			
			$update_array = array();
			$update_array['ID'] = $parent_order_id;
			$update_array['post_status'] = 'wc-refunded';
			$update_array['post_modified'] = date('Y-m-d H:i:s');
			$update_array['post_modified_gmt'] = date('Y-m-d H:i:s');
			wp_update_post($update_array);

			if(is_wp_error($retID) || $retID == '') {
				$core_instance->detailed_log[$line_number]['Message'] = "Can't insert this Refund. " . $retID->get_error_message();
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
				return array('MODE' => $mode, 'ERROR_MSG' => $retID->get_error_message());
			}
			$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Refund ID: ' . $retID;
			$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");

		}
		
		else{
			if($check == 'REFUNDID'){
				$refund_id = $data_array['REFUNDID'];
				$update_query = "select ID from {$wpdb->prefix}posts where ID = '$refund_id' and post_type = 'shop_order_refund' order by ID DESC";
				$ID_result = $wpdb->get_results($update_query);
				if (is_array($ID_result) && !empty($ID_result)) {
					$retID = $ID_result[0]->ID;
					$data_array['ID'] = $retID;
					wp_update_post($data_array);

					update_post_meta($retID , '_refund_reason' , $post_excerpt);
					$mode_of_affect = 'Updated';

					$core_instance->detailed_log[$line_number]['Message'] = 'Updated Refund ID: ' . $retID;
					$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey'");
				}else{
					if($mode == 'Update'){
						$core_instance->detailed_log[$line_number]['Message'] = "Skipped.";
						$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
						return array('MODE' => $mode);
					}
					
				}

			}else{
				if($mode == 'Update'){
					$core_instance->detailed_log[$line_number]['Message'] = "Skipped.";
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
					return array('MODE' => $mode);
				}	
			} 
		} 
		
		$returnArr['ID'] = $retID;
		$returnArr['MODE'] = $mode_of_affect;
		return $returnArr;
	}

	public function woocommerce_attributes_import($data_array , $mode , $check , $unikey , $unikey_name, $line_number) {
		
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		global $core_instance;
	
		$returnArr = array();
		$name = $data_array['name'];
		$slug = $data_array['slug'];
		$configure_terms = $data_array['configure_terms'];
		$attr = $data_array['default_sort_order'];
			if($attr == 'Custom ordering'){
				$attr = 'menu_order';
			}
			if($attr == 'Name (numeric)'){
				$attr = 'name_num';
			}
			if($attr == 'Term ID'){
				$attr = 'id';
			}
			if($attr == 'Name'){
				$attr = 'name';
			}
		$attribute=$data_array['enable_archive'];

		$log_table_name = $wpdb->prefix ."import_detail_log";

		$updated_row_counts = $helpers_instance->update_count($unikey,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];

		if($check == 'name') { 
			$result = $wpdb->get_row("select attribute_id from {$wpdb->prefix}woocommerce_attribute_taxonomies where attribute_label='".$name."'");
		}
		if($check == 'slug') {
			$result = $wpdb->get_row("select attribute_id from {$wpdb->prefix}woocommerce_attribute_taxonomies where attribute_name='".$slug."'");
		}
		$duplicate_check = $wpdb->get_row("select attribute_id from {$wpdb->prefix}woocommerce_attribute_taxonomies where attribute_name='".$slug."'");
	
		if($mode == 'Insert') {

			if (!empty($result) || (!empty($duplicate_check))) {
				$core_instance->detailed_log[$line_number]['Message'] = 'Skipped Product attribute';
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
				$returnArr['Mode'] = $mode;
				return $returnArr;
			}else{

				$wpdb->query("insert into {$wpdb->prefix}woocommerce_attribute_taxonomies(attribute_label,attribute_name,attribute_type,attribute_orderby,attribute_public) values('".$name."','".$slug."','select','".$attr."','".$attribute."')");
				$id = $wpdb->insert_id;

				$existing_attributes = array();
				$existing_attributes = get_option('_transient_wc_attribute_taxonomies', true);

				$at = array( 
						'attribute_id'=>$id,
						'attribute_name'=>$slug,
						'attribute_label'=>$name,
						'attribute_type'=>'select',
						'attribute_orderby'=>$attr,
						'attribute_public'=>$attribute
					);

				$at=(object)$at;
				array_push($existing_attributes,$at);
				update_option('_transient_wc_attribute_taxonomies',$existing_attributes);
					
				if(isset($configure_terms)){
					$taxo = 'pa_'.$slug;
					register_taxonomy($taxo , 'product');

					$configure_exp = explode(',' , $configure_terms);
					foreach($configure_exp as $config_values){
						$check_term = term_exists($config_values);	
						if(isset($check_term)){
						}else{	
							wp_insert_term($config_values , $taxo);	
						}	
					}
				}

				$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Product attribute ID: '.$id;
				$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");
			}
			
		}
		
		if($mode == 'Update') {
       
				if(!empty($result)) {
					foreach($result as $res=>$value) {
						$id = $value;
					}

					$wpdb->query("update {$wpdb->prefix}woocommerce_attribute_taxonomies set attribute_label='".$name."',attribute_name='".$slug."',attribute_type='select',attribute_orderby='".$attr."',attribute_public='".$attribute."' where attribute_id='".$id."'");
					$wpdb->query("delete from ".$wpdb->prefix."options where option_name='_transient_wc_attribute_taxonomies'");

					$at = array( 'attribute_id'=>$id,
							'attribute_name'=>$slug,
							'attribute_label'=>$name,
							'attribute_type'=>'select',
							'attribute_orderby'=>$attr,
							'attribute_public'=>$attribute
						);
					$at=(object)$at;
					$a=array($at);
					update_option('_transient_wc_attribute_taxonomies',$a);

					if(isset($configure_terms)){
						$taxo = 'pa_'.$slug;
						$configure_exp = explode(',' , $configure_terms);
						foreach($configure_exp as $config_values){
							$check_term = term_exists($config_values);	
							if(isset($check_term)){
							}else{	
								if($mode == 'Import-Update'){
									wp_insert_term($config_values , $taxo);	
								}	
							}	
						}
					}

					$core_instance->detailed_log[$line_number]['Message'] = 'Updated Product attribute ID: '.$id;
					$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey'");
					
				} else{
					if($mode == 'Update'){
						$core_instance->detailed_log[$line_number]['Message'] = 'Skipped';
						$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");
						return array('MODE' => $mode);
					}
				}                                                             
		}

		$returnArr['ID'] = $id;
		return $returnArr;
	}

	public function woocommerce_tags_import($data_array , $mode , $check , $unikey , $unikey_name, $line_number) {

		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		global $core_instance;

		$returnArr = array();
		$name = $data_array['name']; 
		$description = $data_array['description'];
		$slug = $data_array['slug'];
		$log_table_name = $wpdb->prefix ."import_detail_log";
		$updated_row_counts = $helpers_instance->update_count($unikey,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];

		if ($check == 'TERMID') {
			$term_id = $data_array['TERMID'];
			$termid =$wpdb->get_row("select term_id from {$wpdb->prefix}terms where term_id = '$term_id' ");
		}
		if($check == 'slug') {
			$termid =$wpdb->get_row("select term_id from {$wpdb->prefix}terms where slug='".$slug."'");
		}

		if($mode == 'Insert') {
			if (!empty($termid)) {

				$core_instance->detailed_log[$line_number]['Message'] = 'Skipped Product tag';
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
				$returnArr['Mode'] = $mode;
				return $returnArr;
				#skipped
			}else{
				$wpdb->query("insert into {$wpdb->prefix}terms(name,slug) values ('".$name."','".$slug."')");
				$id = $wpdb->insert_id;
				$wpdb->query("insert into {$wpdb->prefix}term_taxonomy(term_taxonomy_id,term_id,taxonomy,description) values('".$id."','".$id."','product_tag','".$description."')");
				
				$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Product tag ID: '.$id;
				$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");
			}
		}
		
		if($mode == 'Update'){
			if (!empty($termid)) {
				foreach($termid as $term =>$value) {
					$id = $value;
				}
				$wpdb->query("update {$wpdb->prefix}terms set name='".$name."',slug='".$slug."' where term_id='".$id."'");
				$wpdb->query("update {$wpdb->prefix}term_taxonomy set term_taxonomy_id='".$id."',term_id='".$id."',taxonomy='product_tag',description='".$description."' where term_id='".$id."'"); 
				
				$core_instance->detailed_log[$line_number]['Message'] = 'Updated Product tag ID: '.$id;
				$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey'");
			}else{
				$core_instance->detailed_log[$line_number]['Message'] = 'Skipped.';
				$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");
				$returnArr['Mode'] = $mode;
				return $returnArr;
				//return array('MODE' => $mode);
			}
		}
		$returnArr['ID'] = $id;
		return $returnArr;
	}
	public function woocommerce_product_import($data_array, $mode, $type, $unmatched_row , $check , $unikey , $unikey_name, $line_number  , $acf,$pods, $toolset, $header_array ,$value_array, $wpml_values = null) {
		global $wpdb,$core_instance,$sitepress; 
		$core_instance = CoreFieldsImport::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
		$log_table_name = $wpdb->prefix ."import_detail_log";
		$data_array['PRODUCTSKU']=isset($data_array['PRODUCTSKU'])?$data_array['PRODUCTSKU']:'';
		$data_array['PRODUCTSKU'] = trim($data_array['PRODUCTSKU']);
		$returnArr = array();
		$assigned_author = '';
		$mode_of_affect = 'Inserted';
		$data_array['post_type'] = 'product';
		$data_array = $core_instance->import_core_fields($data_array);
		$post_type = $data_array['post_type'];
		if($check == 'ID'){	
			$ID = $data_array['ID'];	
			$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE ID = '$ID' AND post_type = '$post_type' AND post_status != 'trash' order by ID DESC ");			
		}
		if($check == 'post_title'){
			$title = $data_array['post_title'];
			$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$title' AND post_type = '$post_type' AND post_status != 'trash' order by ID DESC ");		
		}
		if($check == 'post_name'){
			$name = $data_array['post_name'];
			if($sitepress != null) {
			$language_code = $wpml_values['language_code'];
			$get_result =  $wpdb->get_results("SELECT DISTINCT p.ID FROM {$wpdb->prefix}posts p join {$wpdb->prefix}icl_translations pm ON p.ID = pm.element_id WHERE p.post_name = '$name' AND p.post_type = '$post_type' AND p.post_status != 'trash' AND pm.language_code = '{$language_code}'");
		}
			else{
			$get_result =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_name = '$name' AND post_type = '$post_type' AND post_status != 'trash' order by ID DESC ");	
			}
			}
		if($check == 'PRODUCTSKU'){
			$sku = $data_array['PRODUCTSKU'];
			if($sitepress != null) {
				$language_code = $wpml_values['language_code'];
				$get_result =  $wpdb->get_results("SELECT DISTINCT p.ID FROM {$wpdb->prefix}posts p join {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id inner join {$wpdb->prefix}icl_translations icl ON pm.post_id = icl.element_id WHERE p.post_type = 'product' AND p.post_status != 'trash' and pm.meta_value = '$sku' and icl.language_code = '{$language_code}'");               
			}
			else{
				$get_result =  $wpdb->get_results("SELECT DISTINCT p.ID FROM {$wpdb->prefix}posts p join {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id WHERE p.post_type = 'product' AND p.post_status != 'trash' and pm.meta_value = '$sku' ");
			}
		}

		$update = array('ID','post_title','post_name','PRODUCTSKU');
			if(!in_array($check, $update)){
				if(is_plugin_active('advanced-custom-fields-pro/acf.php')||is_plugin_active('advanced-custom-fields/acf.php')){
					if(is_array($acf)){
						$get_key = "";
						foreach($acf as $acf_key => $acf_value){
							if($acf_key == $check){
								$get_key= array_search($acf_value , $header_array);
							}
							if($get_key && isset($value_array[$get_key])){
								$csv_element = $value_array[$get_key];								
								$get_result = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}postmeta as a join {$wpdb->prefix}posts as b on a.post_id = b.ID WHERE a.meta_key = '$check' AND a.meta_value = '$csv_element' AND b.post_status != 'trash' order by a.post_id DESC ");
							}
						}	
					}		
					if(empty($get_result)) {
						$core_instance->detailed_log[$line_number]['Message'] = "Skipped, Due to existing field value is not presents.";
					}
				}
			}
			if(!in_array($check, $update)){
				if(is_plugin_active('pods/init.php')){
					if(is_array($pods)){
						
						foreach($pods as $pods_key => $pods_value){
							if($pods_key == $check){
								$get_key= array_search($pods_value , $header_array);
							}
							if(isset($value_array[$get_key])){
								$csv_element = $value_array[$get_key];	
							}
							
							$get_result = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}postmeta as a join {$wpdb->prefix}posts as b on a.post_id = b.ID WHERE a.meta_key = '$check' AND a.meta_value = '$csv_element' AND b.post_status != 'trash' order by a.post_id DESC ");
						}	
					}	
					if(empty($get_result)) {
						$core_instance->detailed_log[$line_number]['Message'] = "Skipped, Due to existing field value is not presents.";
					}	
				}
			}
			if(!in_array($check, $update)){
				if(is_plugin_active('types/wpcf.php')){
					if(is_array($toolset)){
						foreach($toolset as $tool_key => $tool_value){
							if($tool_key == $check){
								$get_key= array_search($tool_value , $header_array);
							}
							if(isset($value_array[$get_key]) && isset($get_key)){
								$csv_element = $value_array[$get_key];
								$key='wpcf-'.$check;
								$get_result = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}postmeta as a join {$wpdb->prefix}posts as b on a.post_id = b.ID WHERE a.meta_key = '$key' AND a.meta_value = '$csv_element' AND b.post_status != 'trash' order by a.post_id DESC ");	
							}
							
						}	
					}	
					if(empty($get_result)) {
						$core_instance->detailed_log[$line_number]['Message'] = "Skipped, Due to existing field value is not presents.";
					}	
				}
			}
	
		$updated_row_counts = $helpers_instance->update_count($unikey,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];

		if ($mode == 'Insert') {
			if(!isset( $data_array['post_date'] )) {
				$data_array['post_date'] = current_time('Y-m-d H:i:s');
			} else {	
				if(strtotime( $data_array['post_date'] )) {
					$data_array['post_date'] = date( 'Y-m-d H:i:s', strtotime( $data_array['post_date'] ) );
				} else {
					$data_array['post_date'] = current_time('Y-m-d H:i:s');
				}
			}
			if (isset($get_result) && is_array($get_result) && !empty($get_result)) {
				#skipped
				$core_instance->detailed_log[$line_number]['Message'] = "Skipped, Due to duplicate Product found!.";
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
				return array('MODE' => $mode);
			}else{
				
				$post_id = wp_insert_post($data_array); 
				$data_array['post_format']=isset($data_array['post_format'])?$data_array['post_format']:'';
				set_post_format($post_id , $data_array['post_format']);	
				if(!empty($data_array['PRODUCTSKU'])){
					update_post_meta($post_id , '_sku' , $data_array['PRODUCTSKU']);
				}
				if(is_wp_error($post_id) || $post_id == '') {
					# skipped
					$core_instance->detailed_log[$line_number]['Message'] = "Can't insert this Product. " . $post_id->get_error_message();
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
					return array('MODE' => $mode);
				}

				if($unmatched_row == 'true'){
					global $wpdb;
					$post_entries_table = $wpdb->prefix ."post_entries_table";
					$file_table_name = $wpdb->prefix."smackcsv_file_events";
					$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `$unikey_name` = '$unikey'");	
					$file_name = $get_id[0]->file_name;
					$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$post_id}','{$type}', '{$file_name}','Inserted')");
				}

				$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Product ID: ' . $post_id . ', ' . $assigned_author;
				$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");
			}	
		}	
		if($mode == 'Update'){			
			if(isset($core_instance->detailed_log[$line_number]['Message']) && preg_match("(Skipped)", $core_instance->detailed_log[$line_number]['Message']) !== 0) {					
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
				$returnArr['MODE'] = $mode_of_affect;
			}	
			else {						
			if (is_array($get_result) && !empty($get_result)) {
				if(!in_array($check, $update)){
					$post_id = $get_result[0]->post_id;		
					$data_array['ID'] = $post_id;
				}else{
					$post_id = $get_result[0]->ID;	
					$data_array['ID'] = $post_id;
				}
				wp_update_post($data_array);
				$data_array['post_format']=isset($data_array['post_format'])?$data_array['post_format']:'';
				set_post_format($post_id , $data_array['post_format']);		
				if(!empty($data_array['PRODUCTSKU'])){
					update_post_meta($post_id , '_sku' , $data_array['PRODUCTSKU']);
				}

				if($unmatched_row == 'true'){
					global $wpdb;
					$post_entries_table = $wpdb->prefix ."post_entries_table";
					$file_table_name = $wpdb->prefix."smackcsv_file_events";
					$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `$unikey_name` = '$unikey'");	
					$file_name = $get_id[0]->file_name;
					$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$post_id}','{$type}', '{$file_name}','Updated')");
				}

				$core_instance->detailed_log[$line_number]['Message'] = 'Updated Product ID: ' . $post_id . ', ' . $assigned_author;
				$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey'");

			}else{
				$posttitle = $data_array['post_title'];
				$productsku = $data_array['PRODUCTSKU'];
				if(!empty($posttitle)){
					$post_id = wp_insert_post($data_array); 
					$data_array['post_format'] = isset($data_array['post_format'])?$data_array['post_format']:'';
					set_post_format($post_id , $data_array['post_format']);
					if(is_wp_error($post_id) || $post_id == '') {
						# skipped
						$core_instance->detailed_log[$line_number]['Message'] = "Can't insert this Product. " . $post_id->get_error_message();
						$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
						return array('MODE' => $mode);
					}
					if($unmatched_row == 'true'){
						global $wpdb;
						$post_entries_table = $wpdb->prefix ."post_entries_table";
						$file_table_name = $wpdb->prefix."smackcsv_file_events";
						$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `$unikey_name` = '$unikey'");	
						$file_name = $get_id[0]->file_name;
						$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$post_id}','{$type}', '{$file_name}','Inserted')");
					}
					$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Product ID: ' . $post_id . ', ' . $assigned_author;
					$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");	
				}
				else{
					$core_instance->detailed_log[$line_number]["Message"] = "Skipped. SKU: ".$productsku."<br>";
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey'");
					// return array('MODE' => $mode);
				}
				
			
			}
		}

		}

		$returnArr['ID'] = $post_id;
		$returnArr['MODE'] = $mode_of_affect;
		if (!empty($data_array['post_author'])) {
			$returnArr['AUTHOR'] = isset($assigned_author) ? $assigned_author : '';
		}
		return $returnArr;
	}

}
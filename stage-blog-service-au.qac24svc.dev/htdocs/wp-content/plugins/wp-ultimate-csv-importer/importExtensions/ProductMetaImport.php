<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;
use Smackcoders\WCSV\WooCommerceMetaImport;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly
class ProductMetaImport {
    private static $product_meta_instance = null;

    public static function getInstance() {
		
		if (ProductMetaImport::$product_meta_instance == null) {
			ProductMetaImport::$product_meta_instance = new ProductMetaImport;
			return ProductMetaImport::$product_meta_instance;
		}
		return ProductMetaImport::$product_meta_instance;
    }

    function set_product_meta_values($header_array ,$value_array , $map , $post_id ,$variation_id ,$type , $line_number , $mode,$hash_key,$selected_type=null){
        global $wpdb;

        $woocommerce_meta_instance = WooCommerceMetaImport::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
		$data_array = [];
			
		$data_array = $helpers_instance->get_header_values($map , $header_array , $value_array);
        $image_meta = $helpers_instance->get_meta_values($map , $header_array , $value_array);
        if(($type == 'WooCommerce Product') || ($type =='WooCommerce Product Variations')){
            $woocommerce_meta_instance->woocommerce_meta_import_function($data_array,$image_meta,$post_id ,$variation_id , $type , $line_number , $mode , $header_array, $value_array,'',$hash_key,'','');
        }
    }

}
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

class ProductAttrImport {
    private static $product_attr_instance = null;

    public static function getInstance() {
		
		if (ProductAttrImport::$product_attr_instance == null) {
			ProductAttrImport::$product_attr_instance = new ProductAttrImport;
			return ProductAttrImport::$product_attr_instance;
		}
		return ProductAttrImport::$product_attr_instance;
    }


    function set_product_attr_values($header_array ,$value_array , $map ,$maps, $post_id, $variation_id,$type , $line_number , $mode , $hash_key, $wpml_map){
        global $wpdb;
       
        $woocommerce_meta_instance = WooCommerceMetaImport::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
        $data_array = $helpers_instance->get_header_values($map , $header_array , $value_array);
        $core_array = [];
        $image_meta = [];
        if(($type == 'WooCommerce Product') || ($type == 'WooCommerce Product Variations')){
            $woocommerce_meta_instance->woocommerce_meta_import_function($data_array,$image_meta,$post_id ,$variation_id , $type , $line_number , $mode , $header_array, $value_array,'',$hash_key,'','');
        }
    }
}
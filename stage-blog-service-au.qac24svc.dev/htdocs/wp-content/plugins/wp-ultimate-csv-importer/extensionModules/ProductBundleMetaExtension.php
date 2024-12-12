<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class ProductBundleMetaExtension extends ExtensionHandler{
    private static $instance = null;

    public static function getInstance() {
		
		if (ProductBundleMetaExtension::$instance == null) {
            ProductBundleMetaExtension::$instance = new ProductBundleMetaExtension;
		}
		return ProductBundleMetaExtension::$instance;
    }

    /**
	* Provides Product Bundle Meta fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data){
        $import_type = $data;
        $response = [];
        $import_type = $this->import_type_as($import_type);
        if(is_plugin_active('woocommerce/woocommerce.php')){ 
            if(is_plugin_active('woocommerce-product-bundles/woocommerce-product-bundles.php')){ 
  
                if($import_type == 'WooCommerce'){
                    $pro_meta_fields = array(
                            
                        'Product Bundle Items' => 'product_bundle_items',
                        'Layout' => 'layout',
                        'Form Location' => 'form_location',
                        'Item Grouping' => 'item_grouping',
                        'Min Bundle Size' => 'min_bundle_size',
                        'Max Bundle Size' => 'max_bundle_size',
                        'Edit in Cart' => 'edit_in_cart',
                        'Product Bundle Regular Price' => 'pb_regular_price',
                        'Product Bundle Sale Price' => 'pb_sale_price',
                        'Optional' => 'optional',
                        'Quantity Minimum' => 'quantity_min',
                        'Quantity Maximum' => 'quantity_max',
                        'Priced Individually' => 'priced_individually',
                        'Discount' => 'discount',
                        'Product details' => 'single_product_visibility',
                        'Cart Visibility' => 'cart_visibility',
                        'Order Visibility ' => 'order_visibility',
                        'Product Price details' => 'single_product_price_visibility',
                        'Cart Price Visibility' => 'cart_price_visibility',
                        'Order Price Visibility' => 'order_price_visibility',
                        'Override Title' => 'override_title',
                        'Override Title Value' => 'override_title_value',
                        'Override Description' => 'override_description',
                        'Override Description Value' => 'override_description_value',
                        'Hide Thumbnail' => 'hide_thumbnail',
                        'Bundle Virtual'=>'_wc_pb_virtual_bundle',
                        'Bundle Min Qty '=>'_wcpb_min_qty_limit',
                        'Bundle Max Qty '=>'_wcpb_max_qty_limit'
                    
                    );
                }
            }
        }

        $pro_meta_fields_line = $this->convert_static_fields_to_array($pro_meta_fields);
        $response['product_bundle_meta_fields'] = $pro_meta_fields_line;
		return $response;		
    }

    /**
	* Product Bundle Meta extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type ){
        if(is_plugin_active('woocommerce-product-bundles/woocommerce-product-bundles.php')){
            if($import_type == 'nav_menu_item'){
				return false;
			}

            $import_type = $this->import_name_as($import_type);
            if($import_type == 'WooCommerce') { 
                return true;
            }else{
                return false;
            }
        }
	}

}

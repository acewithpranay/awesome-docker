<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class ProductAttributeExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {	
		if (ProductAttributeExtension::$instance == null) {
			ProductAttributeExtension::$instance = new ProductAttributeExtension;
		}
		return ProductAttributeExtension::$instance;
    }

	/**
	* Provides Product Attribute mapping fields
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
		global $wpdb;

        $response = [];
		$import_type = $data;
		$import_type = $this->import_type_as($import_type);
		$importas = $this->import_post_types($import_type);	
		$taxonomies = get_object_taxonomies( $importas, 'names' );

        $pro_attr_fields = array(
            'Product Attribute Name' => 'product_attribute_name',
            'Product Attribute Value' => 'product_attribute_value',
            'Product Attribute Visible' => 'product_attribute_visible',
            'Product Attribute Variation' => 'product_attribute_variation',
            'Product Attribute Position' => 'product_attribute_position',
            'Product Attribute Taxonomy' => 'product_attribute_taxonomy',
        );

		if($import_type == 'WooCommerceVariations'){
			unset($pro_attr_fields['Product Attribute Taxonomy']);
		}

        // if(!empty($taxonomies)) {
		// 	foreach ($taxonomies as $key => $value) {
		// 		$check_for_pro_attr = explode('_', $value);
		// 		if($check_for_pro_attr[0] == 'pa'){	
        //             $get_taxonomy_label = get_taxonomy($value);
        //             $taxonomy_label = $get_taxonomy_label->name;

        //             $pro_attr_fields[$taxonomy_label] = $value;
		// 		}
        //     }
        // }
		
        $pro_attr_fields_line = $this->convert_static_fields_to_array($pro_attr_fields);
		// $response['product_attr_fields'] = $pro_attr_fields_line; 
		$response['product_attr_fields'] = null;
		return $response;	
	}

	/**
	* Pods extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
	public function extensionSupportedImportType($import_type){
		if(is_plugin_active('woocommerce/woocommerce.php')){
			$import_type = $this->import_name_as($import_type);
			if($import_type == 'WooCommerce' || $import_type == 'WooCommerceVariations' ) { 
				return true;
			}else{
				return false;
			}
		}
	}
}
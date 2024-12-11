<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\FCSV;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
class MetaBoxRelationsExtension extends ExtensionHandler
{
    private static $instance = null;

    public static function getInstance()
    {
        if (MetaBoxRelationsExtension::$instance == null)
        {
            MetaBoxRelationsExtension::$instance = new MetaBoxRelationsExtension;
        }
        return MetaBoxRelationsExtension::$instance;
    }

    /**
     * Provides Metabox mapping fields for specific post type
     * @param string $data - selected import type
     * @return array - mapping fields
     */
    public function processExtension($data)
    {
        global $wpdb;
        $response = [];
        $import_type = $this->import_post_types($data);
        $metabox_fields = [];
        $taxonomies = get_taxonomies();
        if ($import_type == 'user')
        {
            $get_metabox_fields = \rwmb_get_object_fields($import_type, 'user');
        }
        else if (array_key_exists($import_type, $taxonomies))
        {
            $get_metabox_fields = \rwmb_get_object_fields($import_type, 'term');
        }
        else
        {
            $get_metabox_fields = \rwmb_get_object_fields($import_type);
        }
        if (!empty($get_metabox_fields))
        {
            $customFields =array();
            foreach ($get_metabox_fields as $meta_key => $meta_value)
            {
                if(isset($meta_value['relationship'])) {
                    if (strpos($meta_key, '_to') !== false)
                    {
                        $meta_title = 'from';
                    }
                    else
                    {
                        $meta_title = 'to';
                    }
                
                    $meta_title_names = explode('_', $meta_key);
                    $meta_title_name = $meta_title_names[0];
                    $related = $meta_title_names[1];
                    $p_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts where post_name='$meta_title_name'");
                    $relation_value = $wpdb->get_var("SELECT meta_value FROM {$wpdb->prefix}postmeta where post_id='$p_id' AND meta_key='relationship'");
                    $relation_value = unserialize($relation_value);
                    
                    if(!empty($relation_value) && is_array($relation_value)){
                        foreach ($relation_value as $relate_key => $relate_value)
                        {
                            if ($relate_key == $meta_title)
                            {
                                // $customFields[$relate_value['meta_box']['title']] = $meta_key;
                                $customFields[$relation_value[$related]['field']['name']] = $meta_key;
                            }
                        }
                    }
                    else{
                        $customFields[$meta_title_name] = $meta_key;
                    }
                }
            }
            $mb_value = $this->convert_static_fields_to_array($customFields);
        }
        else
        {
            $mb_value = '';
        }
        if(!empty($mb_value)){
            $response['metabox_relations_fields'] = null;
        }
        
        return $response;
    }

    /**
     * Metabox extension supported import types
     * @param string $import_type - selected import type
     * @return boolean
     */
    public function extensionSupportedImportType($import_type)
    {
        if (is_plugin_active('meta-box-aio/meta-box-aio.php'))
        {
            if ($import_type == 'nav_menu_item')
            {
                return false;
            }
            $import_type = $this->import_name_as($import_type);
            if ($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'event' || $import_type == 'event-recurring' || $import_type == 'Users' || $import_type == 'Taxonomies' || $import_type == 'Categories' || $import_type == 'Tags' 
                || $import_type =='WooCommerce'  || $import_type =='WooCommerceCategories' || $import_type =='WooCommerceattribute' || $import_type =='WooCommercetags' || $import_type =='WPeCommerce' || $import_type == 'WooCommerceVariations' || $import_type == 'WooCommerceOrders' || $import_type == 'WooCommerceCoupons' || $import_type == 'WooCommerceRefunds')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    function import_post_types($import_type, $importAs = null)
    {
        $import_type = trim($import_type);

        $module = array(
            'Posts' => 'post',
            'Pages' => 'page',
            'Users' => 'user',
            'WooCommerce Product Variations' => 'product_variation',
            'WooCommerce Refunds' => 'shop_order_refund',
            'WooCommerce Orders' => 'shop_order',
            'WooCommerce Coupons' => 'shop_coupon',
            'Comments' => 'comments',
            'Taxonomies' => $importAs,
            'WooCommerce Product' => 'product',
            'WooCommerce' => 'product',
            'CustomPosts' => $importAs
        );
        foreach (get_taxonomies() as $key => $taxonomy)
        {
            $module[$taxonomy] = $taxonomy;
        }
        if (array_key_exists($import_type, $module))
        {
            return $module[$import_type];
        }
        else
        {
            return $import_type;
        }
    }
}


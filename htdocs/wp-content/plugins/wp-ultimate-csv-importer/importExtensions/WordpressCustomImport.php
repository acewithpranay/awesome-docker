<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class WordpressCustomImport {
    private static $wordpress_custom_instance = null,$media_instance;

    public static function getInstance() {
		
		if (WordpressCustomImport::$wordpress_custom_instance == null) {
			WordpressCustomImport::$wordpress_custom_instance = new WordpressCustomImport;
            WordpressCustomImport::$media_instance = MediaHandling::getInstance();
			return WordpressCustomImport::$wordpress_custom_instance;
		}
		return WordpressCustomImport::$wordpress_custom_instance;
    }
    function set_wordpress_custom_values($header_array ,$value_array , $map, $post_id , $type,$hash_key,$line_number,$templatekey,$gmode){	
        $post_values = [];
        $helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		
		$this->wordpress_custom_import_function($post_values, $post_id ,$type , 'off',$hash_key,$line_number,$templatekey,$gmode);
		
    }    

    public function wordpress_custom_import_function($data_array, $pID, $importType, $core_serialize_info, $hash_key, $line_number, $templatekey, $gmode) {
        global $wpdb;
        $createdFields = array();
        if (!empty($data_array)) {
            if(!empty($data_array) && !is_plugin_active('masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php') ) {
            foreach ($data_array as $custom_key => $custom_value) {
                $createdFields[] = $custom_key;
                if ($importType != 'Users') {
                    if ((isset($core_serialize_info[$custom_key]) && $core_serialize_info[$custom_key] == 'on') || is_plugin_active('wpml-import/plugin.php')) {
    
                        // Check if value is serialized,
                        if (is_serialized($custom_value)) {
                            $custom_value = maybe_unerialize($custom_value);
                        }
                        $get_meta_info = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id=%d AND meta_key=%s", $pID, $custom_key), ARRAY_A);
    
                        if (!empty($get_meta_info)) {
                            $wpdb->update($wpdb->prefix . 'postmeta', array('meta_value' => $custom_value), array('meta_key' => $custom_key, 'post_id' => $pID));
                        } else {
                            $wpdb->insert($wpdb->prefix . 'postmeta', array('meta_key' => $custom_key, 'meta_value' => $custom_value, 'post_id' => $pID));
                        }
                    } else {
                        if (is_serialized($custom_value)) {
                            $custom_value = maybe_unerialize($custom_value);
                        }
                        if (is_array($custom_value) || is_object($custom_value)) {
                            // Convert to JSON format
                            $custom_value = json_encode($custom_value);
                        }
                    
                       
                        update_post_meta($pID, $custom_key, $custom_value);
                    }
                } else {
                    if (isset($core_serialize_info[$custom_key]) && $core_serialize_info[$custom_key] == 'on') {
    
                        // Check if value is serialized,
                        if (!is_serialized($custom_value)) {
                            $custom_value = maybe_serialize($custom_value);
                        }
    
                        $get_meta_info = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->prefix}usermeta WHERE user_id=%d AND meta_key=%s", $pID, $custom_key), ARRAY_A);
    
                        if (!empty($get_meta_info)) {
                            $wpdb->update($wpdb->prefix . 'usermeta', array('meta_value' => $custom_value), array('meta_key' => $custom_key, 'user_id' => $pID));
                        } else {
                            $wpdb->insert($wpdb->prefix . 'usermeta', array('meta_key' => $custom_key, 'meta_value' => $custom_value, 'user_id' => $pID));
                        }
                    } else {
                        // Prevent double serialization for non-serialized user fields
                        if (is_serialized($custom_value)) {
                            $custom_value = maybe_unerialize($custom_value);
                        }
                        update_user_meta($pID, $custom_key, $custom_value);
                    }
                }
            }
        }
    }
        return $createdFields;
    }
    
    
    
}

<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class WordpressCustomExtension extends ExtensionHandler{
    private static $instance = null;

    public static function getInstance() {
		
		if (WordpressCustomExtension::$instance == null) {
			WordpressCustomExtension::$instance = new WordpressCustomExtension;
		}
		return WordpressCustomExtension::$instance;
    }

    /**
	* Provides Wordpress Custom fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {		
        global $wpdb;
        $import_types = $data;

        $import_type = $this->import_type_as($import_types);
        $response =[];
        $module = $this->import_post_types($import_type);
        $acf_values = $acfvalues = [];
        $acf_values = array('admin_color', 'comment_shortcuts', 'community-events-location', 'dbem_phone', 'health-check', 'first_name', 'last_name', 'last_update', 'locale',
                            'nickname', 'orderby', 'rich_editing', 'syntax_highlighting', 'toolset-rg-view', 'username', 'use_ssl', 'session_tokens', 'smack_uci_import', 'description');

        $get_acf_groups = $wpdb->get_results( $wpdb->prepare("SELECT ID, post_content FROM {$wpdb->prefix}posts WHERE post_status != 'trash' AND post_type = %s", 'acf-field-group'));
		foreach ( $get_acf_groups as $item => $group_rules ) {
			$rule = maybe_unserialize($group_rules->post_content);
			
			if(!empty($rule)) {
				if ($import_types != 'Users') {
					foreach($rule['location'] as $key => $value) {
						if($value[0]['operator'] == '==' && $value[0]['value'] == $this->import_post_types($import_types)){	
							$group_id_arr[] = $group_rules->ID; #. ',';
						}
						elseif($value[0]['operator'] == '==' && $value[0]['value'] == 'all' && $value[0]['param'] == 'taxonomy' && in_array($this->import_post_types($import_types) , get_taxonomies())){
							$group_id_arr[] = $group_rules->ID;
						}
					}
				} else {
					foreach($rule['location'] as $key => $value) {
						if( $value[0]['operator'] == '==' && $value[0]['param'] == 'user_role'){
							$group_id_arr[] = $group_rules->ID;
						}
					}
				}
			}
		}
        if ( !empty($group_id_arr) ) {	
			foreach($group_id_arr as $groupId) {	
				$get_acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_content, post_excerpt, post_name FROM {$wpdb->prefix}posts where post_status != 'trash' AND post_parent in (%s)", array($groupId) ) );				
				if ( ! empty( $get_acf_fields ) ) {						
					foreach ( $get_acf_fields as $acf_pro_fields ) {
						$acf_values[] = $acf_pro_fields->post_excerpt;  
                        $acfvalues[] = $acf_pro_fields->post_excerpt;   
                    }
                }
            }   
        }

        $acf = [];
        $get_acf_fields = $wpdb->get_results("SELECT post_excerpt FROM {$wpdb->prefix}posts where post_type = 'acf-field' ");
        foreach($get_acf_fields as $acf_fields){
            $acf[] = $acf_fields->post_excerpt;
        }

        $pods = [];
        $get_pods_fields = $wpdb->get_results("SELECT post_name FROM {$wpdb->prefix}posts where post_type = '_pods_field' ");
        foreach($get_pods_fields as $pods_fields){
            $pods[] = $pods_fields->post_name;  
        }
  
        if(is_plugin_active('meta-box/meta-box.php')){
            $metabox_fields = [];
            $import_as = $this->import_post_types($import_types);
            $get_metabox_fields = \rwmb_get_object_fields( $import_as );
            $metabox_fields = array_keys($get_metabox_fields);
        }
        else{
            $metabox_fields = '';
        }

        $jet_cpt = [];
        $jet_meta_field = [];
        if(is_plugin_active('jet-engine/jet-engine.php')){
            $get_jet_cpt_fields = $wpdb->get_results("SELECT meta_fields FROM {$wpdb->prefix}jet_post_types where slug = '".$module."'");
            foreach($get_jet_cpt_fields as $jet_cpt_fields){
                $jet_cpt_fields = $jet_cpt_fields->meta_fields;
                $unserialize_jet_cpt = unserialize($jet_cpt_fields);
            }

            if(isset($unserialize_jet_cpt) && is_array($unserialize_jet_cpt)){
                foreach($unserialize_jet_cpt as $jet_cpt_field){
                    $jet_cpt[] = $jet_cpt_field['name'];  
                }
            }
            
        
            $jet_field  = [];
            $jet_fields = $wpdb->get_results("SELECT option_value FROM {$wpdb->prefix}options where option_name ='jet_engine_meta_boxes'");    
            foreach($jet_fields as $jetfield){
                $jet_field_value = $jetfield->option_value;
                $unserialize_jet_field_value = unserialize($jet_field_value);
            }
            foreach($unserialize_jet_field_value as $jet_fields_value){
                $jet_field [] = $jet_fields_value['meta_fields'];
            }
            foreach($jet_field as $j_field){
                foreach($j_field as $jfield){
                    $jet_meta_field[] = $jfield['name'];
                }
            }
        }
    
        $commonMetaFields = array();
        
        if($module != 'user') { 
            //query to remove all acf fields from meta
            $acf_not_like_query = '';
            if(!empty($acfvalues)){
                foreach($acfvalues as $acf_name){
                    $acf_not_like_query .= "meta_key NOT LIKE '%{$acf_name}%' AND ";
                }
                $acf_not_like_query = 'AND ' . rtrim($acf_not_like_query, 'AND ');
            }
    
            //query to remove all pods fields from meta
            $pods_not_like_query = '';
            if(!empty($pods)){
                foreach($pods as $pods_name){
                    $pods_not_like_query .= "meta_key NOT LIKE '%{$pods_name}%' AND ";
                }
                $pods_not_like_query = 'AND ' . rtrim($pods_not_like_query, 'AND ');
            }

            //query to remove all metabox fields from meta
            $metabox_not_like_query = '';
            if(!empty($metabox_fields)){
                foreach($metabox_fields as $metabox_name){
                    $metabox_not_like_query .= "meta_key NOT LIKE '%{$metabox_name}%' AND ";
                }
                $metabox_not_like_query = 'AND ' . rtrim($metabox_not_like_query, 'AND ');
            }
         
            $keys = $wpdb->get_col( "SELECT pm.meta_key FROM {$wpdb->prefix}posts p
                                    JOIN {$wpdb->prefix}postmeta pm
                                    ON p.ID = pm.post_id
                                    WHERE p.post_type = '{$module}' AND NOT p.post_status = 'trash'
                                    GROUP BY meta_key
                                    HAVING meta_key NOT LIKE '\_%' and meta_key NOT LIKE 'rank_%'and meta_key NOT LIKE 'ywbc_%' and meta_key NOT LIKE 'field_%' and meta_key NOT LIKE 'wpcf-%' and meta_key NOT LIKE 'wpcr3_%' and meta_key NOT LIKE '%pods%' and meta_key NOT LIKE '%group_%' and meta_key NOT LIKE '%repeat_%' and meta_key NOT LIKE 'mp_%' $acf_not_like_query $pods_not_like_query $metabox_not_like_query
                                    ORDER BY meta_key" );
                                    
        } else {
            $keys = $wpdb->get_col( "SELECT um.meta_key FROM {$wpdb->prefix}users u
                                    JOIN {$wpdb->prefix}usermeta um
                                    ON u.ID = um.user_id
                                    GROUP BY meta_key
                                    HAVING meta_key NOT LIKE '\_%' and meta_key NOT LIKE 'field_%' and meta_key NOT LIKE 'wpcf-%' and meta_key NOT LIKE 'wpcr3_%' and meta_key NOT LIKE '%pods%' and meta_key NOT LIKE '%group_%' and meta_key NOT LIKE '%repeat_%' 
                                    and meta_key NOT LIKE 'closedpostboxes_%' and meta_key NOT LIKE 'metaboxhidden_%' and meta_key NOT LIKE 'billing_%' and meta_key NOT LIKE 'aioseop_%' and meta_key NOT LIKE 'dismissed_%' and meta_key NOT LIKE 'manageedit-%'
                                    and meta_key NOT LIKE 'wp_%' and AND meta_key NOT LIKE 'ywbc_%' AND meta_key NOT LIKE 'wc_%' and meta_key NOT LIKE 'mp_%' and meta_key NOT LIKE 'shipping_%' and meta_key NOT LIKE 'show_%' and meta_key NOT LIKE 'acf_%' and meta_key NOT LIKE 'user_%'
                                    ORDER BY meta_key" );                             
        }

        foreach ($keys as $val) {
            if(!in_array($val , $acf_values) && !empty($val) && !in_array($val , $pods) && !in_array($val , $acf) && !in_array($val,$jet_cpt) && !in_array($val,$jet_meta_field)){
                $commonMetaFields['CORECUSTFIELDS'][$val]['label'] = $val;
                $commonMetaFields['CORECUSTFIELDS'][$val]['name'] = $val;
            }
        }
        
        $wp_custom_value = $this->convert_fields_to_array($commonMetaFields);
        $response['wordpress_custom_fields'] = $wp_custom_value ;
		return $response;	
    
    }

    /**
	* Wordpress Custom extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type){
        if($import_type == 'nav_menu_item'){
            return false;
        }

        if(!is_plugin_active('wpml-import/plugin.php')){
            $active_plugin = array(
                "masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php",  
                "seo-by-rank-math/rank-math.php",
                "seo-by-rank-math-pro/rank-math-pro.php"
            );
        }

        foreach($active_plugin as $value){
            if(is_plugin_active("$value")){
                return false;
            }
        }


        $import_type = $this->import_name_as($import_type);
        if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'Users' || $import_type == 'WooCommerce') {
			return true;
        }
    }
}

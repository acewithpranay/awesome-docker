<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */ 

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class SeopressExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {
		
		if (SeopressExtension::$instance == null) {
			SeopressExtension::$instance = new SeopressExtension;
		}
		return SeopressExtension::$instance;
    }

	/**
	* Provides SEOPress fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
	  //return true;
      $response = [];
	  $seoPressFields = array(
		'SEO Title' => '_seopress_titles_title',
		'Meta Description' => '_seopress_titles_desc',
		'Robots Index' => '_seopress_robots_index',
		'Robots Follow'=>'_seopress_robots_follow',
		'Robots Imageindex' => '_seopress_robots_imageindex',
		'Robots Archive' => '_seopress_robots_archive',
		 'Canonical' => '_seopress_robots_canonical',
		'Target Keyword' =>'_seopress_analysis_target_kw',
		'Robots Category' => '_seopress_robots_primary_cat',
		'Robots Breadcrumbs'  => '_seopress_robots_breadcrumbs', // 'bread-crumbs-title'
		'Facebook Title' => '_seopress_social_fb_title',
		'Facebook Description' => '_seopress_social_fb_desc',
		'Facebook Image' => '_seopress_social_fb_img',
		'Twitter Title' => '_seopress_social_twitter_title',
		'Twitter Description' => '_seopress_social_twitter_desc',
		'Twitter Image' => '_seopress_social_twitter_img',
		'Redirections Type' => '_seopress_redirections_type',
		'Redirections Value' => '_seopress_redirections_value',
		'Enable redirection' => '_seopress_redirections_enabled',
		'Redirection logged status' => '_seopress_redirections_logged_status'
	);
	$seopress_value = $this->convert_static_fields_to_array($seoPressFields);
	$response['seopress_fields'] = $seopress_value ;
	return $response;
    }

	/**
	* Yoast SEOPress extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type ){		
		if(is_plugin_active('wp-seopress/seopress.php') || is_plugin_active('wp-seopress-pro/seopress-pro.php')){
			if($import_type == 'nav_menu_item'){
				return false;
			}

			$import_type = $this->import_name_as($import_type);
		//	print_r($import_type);
			if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts') {	
			
				return true;
			}
			else{
				return false;
			}
		}
	}
}
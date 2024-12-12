<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

/**
 * Class InstallAddons
 * @package Smackcoders\FCSV
 */

class InstallAddons {
	
    protected static $instance = null;
    private static $smack_csv_instance = null,$plugin;

		public function __construct() {
					$plugin = Plugin::getInstance();
		}

		public static function getInstance() {
			if ( InstallAddons::$instance == null ) {
							InstallAddons::$instance = new InstallAddons;
							InstallAddons::$smack_csv_instance = SmackCSV::getInstance();
							InstallAddons::$instance->doHooks();
			}
        return InstallAddons::$instance;
    }

    public function doHooks()
		{
			add_action('wp_ajax_install_plugins',array($this,'install'));
			add_action('wp_ajax_install_addon',array($this,'separateaddons'));
    }
    
    public function install(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		delete_option("WP_ULTIMATE_ADDONS_FAILED");
	
		$fields = $_POST;				
		foreach($fields as $fieldKey => $fieldVal ){
			if( is_array($fieldVal)){
				$postvalue[$fieldKey] = array_map( 'sanitize_text_field', $fieldVal );
			}
			else{
				$postvalue[$fieldKey] = sanitize_text_field($fieldVal);
			}
		}
		
		$all_addons = $postvalue['addons'];
		foreach($all_addons as $type){			
			self::plugin_install($type);
		}				
			self::activate_all($all_addons);	
			print "Plugin Installed";
			    wp_die();	
		
	}

	public function separateaddons(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		$type = isset($_POST['addons']) ? sanitize_text_field($_POST['addons']) : "";
		if($type == 'Users'){
			$plugin_slug = 'import-users/import-users.php';
			$plugin_zip = 'https://downloads.wordpress.org/plugin/import-users.zip';
		}
		if($type == 'WooCommerce'){
			$plugin_slug = 'import-woocommerce/import-woocommerce.php';
			$plugin_zip = 'https://downloads.wordpress.org/plugin/import-woocommerce.zip';
		}
		if($type == 'Exporter'){
			$plugin_slug = 'wp-ultimate-exporter/wp-ultimate-exporter.php';
			$plugin_zip = 'https://downloads.wordpress.org/plugin/wp-ultimate-exporter.zip';
		}
		
		if ( self::is_plugin_installed( $plugin_slug ) ) {
			self::upgrade_plugin( $plugin_slug );
			$installed = true;		
    } 
		else {
				$installed = self::install_plugin( $plugin_zip );
		}
	
		if ( $installed ) {
			$activate = activate_plugin( $plugin_slug );			
			if ( !is_null($activate) ) {
				$result['activate'] = true;				
			}
			else {
				$result['activate'] = $activate;				
			}
		}	
				echo wp_json_encode($result);
			    wp_die();
	}

		public function activate_all($get_all_selected_addons){		
			foreach($get_all_selected_addons as $selected_addon){
				if($selected_addon == 'Users'){
					activate_plugin('import-users/import-users.php');
				}
				elseif($selected_addon == 'WooCommerce'){
					activate_plugin('import-woocommerce/import-woocommerce.php');
				}
				elseif($selected_addon == 'Exporter'){
					activate_plugin('wp-ultimate-exporter/wp-ultimate-exporter.php');
				}
			}			
		}
	/**
	 * Code for download and install plugin from org
	 **/

	public function plugin_install($crmtype){		
		$plugin_slug = $plugin_zip = "";		
	
		switch($crmtype){
			case 'Users':
				$plugin_slug = 'import-users/import-users.php';
				$plugin_zip = 'https://downloads.wordpress.org/plugin/import-users.zip';
				break;

			case 'WooCommerce':
				$plugin_slug = 'import-woocommerce/import-woocommerce.php';
				$plugin_zip = 'https://downloads.wordpress.org/plugin/import-woocommerce.zip';
				break;

			case 'Exporter':
				$plugin_slug = 'wp-ultimate-exporter/wp-ultimate-exporter.php';
				$plugin_zip = 'https://downloads.wordpress.org/plugin/wp-ultimate-exporter.zip';
				break;
		}				

		if ( self::is_plugin_installed( $plugin_slug ) ) {			
			self::upgrade_plugin( $plugin_slug );
			$installed = true;			
    } 
		else {
				$installed = self::install_plugin( $plugin_zip );
		}
	
		if ( $installed ) {
			$activate = activate_plugin( $plugin_slug );			
			if ( is_null($activate) ) {
			}
		} 
	}

	/**
	 * Check whether the plugin is already installed
	 **/
	public function is_plugin_installed( $slug ) {		
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();		

		if ( !empty( $all_plugins[$slug] ) ) {			
			return true;
		} else {			
			return false;
		}
	}

	/**
	 * Code for Install plugin  
	 **/
	public function install_plugin( $plugin_zip ) {				
		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}			
		wp_cache_flush();
		$upgrader = new \Plugin_Upgrader();		
		$installed = $upgrader->install( $plugin_zip );		
		if ( !is_wp_error( $installed ) ) {
			return true;
		}
	}

	public function upgrade_plugin( $plugin_slug ) {
		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}		
		wp_cache_flush();
		$upgrader = new \Plugin_Upgrader();
		$upgraded = $upgrader->upgrade( $plugin_slug );
		return $upgraded;
	}	
}
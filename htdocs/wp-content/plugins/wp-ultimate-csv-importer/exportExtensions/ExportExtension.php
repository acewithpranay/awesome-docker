<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class ExportExtension {

	public $response = array();
	public  $headers = array();
	public  $module;	
	public  $exportType = 'csv';
	public $optionalType = null;	
	public $conditions = array();	
	public $eventExclusions = array();
	public $fileName;	
	public $data = array();	
	public $heading = true;	
	public $delimiter = ',';
	public $enclosure = '"';
	public $auto_preferred = ",;\t.:|";
	public $output_delimiter = ',';
	public $linefeed = "\r\n";
	public $export_mode;
	public $export_log = array();
	public $limit;
	protected static $instance = null,$mapping_instance,$export_handler,$post_export,$woocom_export,$review_export,$learn_export,$ecom_export;
	protected $plugin,$activateCrm,$crmFunctionInstance;
	public $plugisnScreenHookSuffix=null;
	public static function getInstance() {
		global $post_export_class;
		if ( null == self::$instance ) {
			self::$instance = new self;
			ExportExtension::$mapping_instance = MappingExtension::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
	}	

	public  function doHooks(){
		add_action('wp_ajax_total_records', array($this, 'totalRecords'));
		add_action('wp_ajax_check_export', array($this, 'checkExport'));
	}

	public function checkExport(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		if(\is_plugin_active('wp-ultimate-exporter/wp-ultimate-exporter.php')){
			$result['success'] =true;
		}
		else{
			$result['success'] = false;
		}
		echo wp_json_encode($result);
		wp_die();
	}

	public function totalRecords(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		global $wpdb,$post_export_class;
		$module = sanitize_text_field($_POST['module']);
		$optionalType = isset($_POST['optionalType']) ? sanitize_text_field($_POST['optionalType']) : '';
		if ($module == 'WooCommerceOrders') {
			$module = 'shop_order';
		}
		elseif ($module == 'WooCommerceCoupons') {
			$module = 'shop_coupon';
		}
		elseif ($module == 'WooCommerceRefunds') {
			$module = 'shop_order_refund';
		}
		elseif ($module == 'WooCommerceVariations') {
			$module = 'product_variation';
		}
		elseif($module == 'WPeCommerceCoupons'){
			$query = $wpdb->get_col("SELECT * FROM {$wpdb->prefix}wpsc_coupon_codes");
			echo wp_json_encode(count($query));
			wp_die();
		}
		elseif($module == 'Comments' || $module == 'WooCommerceReviews'){
			$get_all_comments = $this->commentsCount($module);	
			echo wp_json_encode($get_all_comments);
			wp_die();
		}
		elseif($module == 'Users'){
			$get_available_user_ids = "select DISTINCT ID from {$wpdb->prefix}users u join {$wpdb->prefix}usermeta um on um.user_id = u.ID";
			$availableUsers = $wpdb->get_col($get_available_user_ids);
			$total = count($availableUsers);
			echo wp_json_encode($total);
			wp_die();
		}
		elseif($module == 'Tags'){
			// $get_all_terms = get_tags('hide_empty=0');
			$query = "SELECT * FROM {$wpdb->prefix}terms t INNER JOIN {$wpdb->prefix}term_taxonomy tax 
				ON  `tax`.term_id = `t`.term_id WHERE `tax`.taxonomy =  'post_tag'";         
			$get_all_taxonomies =  $wpdb->get_results($query);
			echo wp_json_encode(count($get_all_taxonomies));
			wp_die();
		}
		elseif($module == 'Categories'){
			// $get_all_terms = get_categories('hide_empty=0');
			$query = "SELECT * FROM {$wpdb->prefix}terms t INNER JOIN {$wpdb->prefix}term_taxonomy tax 
				ON  `tax`.term_id = `t`.term_id WHERE `tax`.taxonomy =  'category'";         
			$get_all_taxonomies =  $wpdb->get_results($query);
			echo wp_json_encode(count($get_all_taxonomies));
			wp_die();
		}
		elseif($module == 'Taxonomies'){
			$query = "SELECT * FROM {$wpdb->prefix}terms t INNER JOIN {$wpdb->prefix}term_taxonomy tax 
				ON  `tax`.term_id = `t`.term_id WHERE `tax`.taxonomy =  '{$optionalType}'";         
				$get_all_taxonomies =  $wpdb->get_results($query);
			echo wp_json_encode(count($get_all_taxonomies));
			wp_die();
		}
		elseif($module == 'CustomPosts' && $optionalType == 'nav_menu_item'){
			$get_menu_ids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}terms AS t LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_id = t.term_id WHERE tt.taxonomy = 'nav_menu' ", ARRAY_A);
			echo wp_json_encode(count($get_menu_ids));
			wp_die();
		}
		else {
			if($module == 'CustomPosts') {
				$optional_type = $optionalType;
			}
			else{
				$optional_type = '';
			}
			$module = $post_export_class->import_post_types($module,$optional_type);
		}
		if(is_plugin_active('jet-engine/jet-engine.php')){
			$get_slug_name = $wpdb->get_results("SELECT slug FROM {$wpdb->prefix}jet_post_types WHERE status = 'content-type'");
			foreach($get_slug_name as $key=>$get_slug){
				$value=$get_slug->slug;
				$optional_type=$value;	
				if($optionalType == $optional_type){
					$table_name='jet_cct_'.$optional_type;
					$get_menu= $wpdb->get_results("SELECT * FROM {$wpdb->prefix}$table_name");
					if(is_array($get_menu))
					$total = count($get_menu);
					else
					$total = 0;

						echo wp_json_encode($total);
						wp_die();
				}
			}
		}
		$get_post_ids = "select DISTINCT ID from {$wpdb->prefix}posts";
		$get_post_ids .= " where post_type = '$module'";

		/**
		 * Check for specific status
		 */
		// if($module == 'product'){
		// 	$product_statuses = array('publish', 'draft', 'future', 'private', 'pending');
		// 	$products = wc_get_products(array('status' => $product_statuses));
		// 	$response = count($products);
		// 	echo wp_json_encode($response);	
        //     wp_die();
		// }
		if($module  == 'product_variation'){
			$product_statuses = array('publish', 'draft', 'future', 'private', 'pending');
			$products = wc_get_products(array('status' => $product_statuses));
			$variable_product_ids = [];
			foreach($products as $product){
				if ($product->is_type('variable')) {
					$variable_product_ids[] = $product->get_id();
				}
			}	
			$variation_count = 0;
			foreach($variable_product_ids as $variable_product_id){
				$variations = wc_get_products(array('parent_id' => $variable_product_id,'type' => 'variation','limit'=> -1));
				$variation_count += count($variations);
			}	
			$response = $variation_count;
			echo wp_json_encode($response);	
            wp_die();			
		}
		elseif($module == 'shop_order'){
			$order_statuses = array('wc-completed', 'wc-cancelled', 'wc-on-hold', 'wc-processing', 'wc-pending');
			$orders = wc_get_orders(array('status' => $order_statuses));
			$response = count($orders);
			echo wp_json_encode($response);	
            wp_die();
		}elseif ($module == 'shop_coupon') {

			$get_post_ids .= " and post_status in ('publish','draft','pending')";

		}elseif ($module == 'shop_order_refund') {

		}
		elseif($module == 'lp_order'){
			$get_post_ids .= " and post_status in ('lp-pending', 'lp-processing', 'lp-completed', 'lp-cancelled', 'lp-failed')";
		}
		elseif ($module == 'forum') {
			$get_post_ids .= " and post_status in ('publish','draft','future','private','pending','hidden')";
		}
		elseif ($module == 'topic') {
			$get_post_ids .= " and post_status in ('publish','draft','future','open','pending','closed','spam')";
		}
		elseif ($module == 'reply') {
			$get_post_ids .= " and post_status in ('publish','spam','pending')";
		}
		else{
			$get_post_ids .= " and post_status in ('publish','draft','future','private','pending')";
		}
		$get_total_row_count = $wpdb->get_col($get_post_ids);
		$total = count($get_total_row_count);
		echo wp_json_encode($total);
		wp_die();
	}

	/**
	 * ExportExtension constructor.
	 * Set values into global variables based on post value
	 */
	public function __construct() {
		global $post_export_class;
		$this->plugin = Plugin::getInstance();
	}

	public function commentsCount($mode = null) {
		global $wpdb;
		$get_comments = "select * from $wpdb->comments";
		// Check status
		if(isset($this->conditions['specific_status']) && !empty($this->conditions['specific_status'])) {
		if($this->conditions['specific_status']['is_check'] == 'true') {
			if($this->conditions['specific_status']['status'] == 'Pending')
				$get_comments .= " where comment_approved = '0'";
			elseif($this->conditions['specific_status']['status'] == 'Approved')
				$get_comments .= " where comment_approved = '1'";
			else
				$get_comments .= " where comment_approved in ('0','1')";
		}
		}
		else
			$get_comments .= " where comment_approved in ('0','1')";
		// Check for specific period
		if(isset($this->conditions['specific_period']) && !empty($this->conditions['specific_period'])) {
		if($this->conditions['specific_period']['is_check'] == 'true') {
			$get_comments .= " and comment_date >= '" . $this->conditions['specific_period']['from'] . "' and comment_date <= '" . $this->conditions['specific_period']['to'] . "'";
		}
		}
		// Check for specific authors
		if(isset($this->conditions['specific_authors']) && !empty($this->conditions['specific_authors'])) {
		if($this->conditions['specific_authors']['is_check'] == 'true') {
			if(isset($this->conditions['specific_authors']['author'])) {
				$get_comments .= " and comment_author_email = '".$this->conditions['specific_authors']['author']."'"; 
			}
		}
		}
	
		if($mode == 'WooCommerceReviews'){
			$get_comments .= " and comment_type = 'review'";
	    }
	
		$get_comments .= " order by comment_ID";
		$comments = $wpdb->get_results( $get_comments );
		$totalRowCount = count($comments);
		return $totalRowCount;
	}

	/**
	 * Get activated plugins
	 * @return mixed
	 */
	public function get_active_plugins() {
		$active_plugins = get_option('active_plugins');
		return $active_plugins;
	}
}
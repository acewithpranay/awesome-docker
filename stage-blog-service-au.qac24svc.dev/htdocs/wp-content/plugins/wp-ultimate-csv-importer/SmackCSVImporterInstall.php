<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class SmackCSVInstall {

	protected static $instance = null,$smack_instance,$tables_instance,$plugin;

	/**
	 * SmackCSVInstall Constructor
	 */
	public function __construct() {
		$plugin = Plugin::getInstance();
		self::$tables_instance = new Tables();
	}

	/**
	 * SmackCSVInstall Instance
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/** @var array DB updates that need to be run */
	private static $db_updates = array(
			
			);

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
	}

	/**
	 * Check WPUltimateCSVImporterPro version.
	 */
	public static function check_version() {
		if ( get_option( 'ULTIMATE_CSV_IMP_VERSION' ) != SmackUCI()->version )  {
			self::install();
			do_action( 'sm_uci_pro_updated' );
		}
	}

	/**
	 * Install actions when a update button is clicked.
	 */
	public static function install_actions() {
		if ( ! empty( sanitize_text_field($_GET['do_update_sm_uci_pro'] )) ) {
			self::update();
		}
	}

	/**
	 * Show notice stating update was successful.
	 */
	public static function updated_notice() {
		?>
			<div class='notice updated uci-message wc-connect is-dismissible'>
			<p><?php esc_html__( 'Ultimate CSV Importer PRO data update complete. Thank you for updating to the latest version!', 'wp-ultimate-csv-importer-pro' ); ?></p>
			</div>
			<?php
	}

	/**
	 * Install WUCI.
	 */
	public  function install() {
		$current_uci_version    = get_option( 'ULTIMATE_CSV_IMP_VERSION', null );
		if(empty($current_uci_version)){
			add_option("WP_ULTIMATE_CSV_FIRST_ACTIVATE", 'On');
		}
		
		self::$tables_instance->create_tables(); 
		if ( is_null( $current_uci_version )) {
			self::create_options();         // Create option data on the initial stage
	        
		} 

		self::update_uci_version();


		// Trigger action
		do_action( 'sm_uci_installed' );
	}

	/**
	 * Update UCI version to current.
	 */
	private static function update_uci_version() {
		$version = '5.7';
		delete_option( 'ULTIMATE_CSV_IMP_VERSION' );
		add_option( 'ULTIMATE_CSV_IMP_VERSION', $version );
	}


	public static function content_media_url_modification($content) {
		$region=self::bucket_region();
		preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $content, $match);
		$content_urls = $match[0];
		$rewrite_url=get_option('media_rewrite_url');
		$media_bucket=get_option('updated_media_bucket');
        $media_bucket=trim($media_bucket);
		$upload_directory = wp_upload_dir();
		$domain_name=get_option('do_domain_name');
		$copy_year=get_option('copy_year_path');
		$media_path =get_option('media_file_path');
		$media_path = substr_replace($media_path, "", -1);
		$end_points=get_option('media_bucket_origin');
		if($copy_year =='true'){
			$media_base_url = $upload_directory['baseurl'];
			
		}else{
			$media_base_url = $upload_directory['url'];
		}
		if($rewrite_url=='true'){
			foreach ($content_urls as $content_url) {
				if(!empty($domain_name)){
					if(!empty($media_path)){
						$do_storage_location = $domain_name.'/'.$media_path;
					  }
					  else{
						$do_storage_location=$domain_name;
					  }
				  }
				  else{
					if(!empty($media_path)){
						$do_storage_location = $end_points.'/'.$media_path;
					}
					else{
						$do_storage_location = $end_points;
					}
				  }
				
				if (strpos($content_url, $do_storage_location) !== false) {
					$content = str_replace($media_base_url, $do_storage_location, $content);
				}
				else {
					$content = str_replace($media_base_url, $do_storage_location, $content);
				}
			}
		}
		return $content;
	}

	/**
	 * @param null $version
	 * Update DB version to current.
	 */
	private static function update_db_version( $version = null ) {
		delete_option( 'sm_uci_db_version' );
		add_option( 'sm_uci_db_version', is_null( $version ) ? SmackUCI()->version : $version );
	}

	/**
	 * Handle updates.
	 */
	private static function update() {
		$current_db_version = get_option( 'ULTIMATE_CSV_IMP_VERSION' );
		foreach ( self::$db_updates as $version => $updater ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				include_once ( $updater );
				self::update_db_version( $version );
			}
		}

		self::update_db_version();
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	public static function create_options() {

		// We assign the default option data for the fresh instalization
		$settings = array('debug_mode' => 'off',
				'send_log_email' => 'on',
				'drop_table' => 'off',
				'author_editor_access' => 'off',
				'woocomattr' => 'off',
				'unmatchedrow' => 'off'
				);

		add_option('sm_uci_pro_settings', $settings);

	}

	/**
	 * Todo: add PHP docs
	 */
	public static function remove_options() {
		delete_option('ULTIMATE_CSV_IMP_VERSION');
		delete_option('ULTIMATE_CSV_IMPORTER_UPGRADE_VERSION');
	}

	public static function important_upgrade_notice() {
		$get_notice = get_option('smack_uci_upgrade_notice');
		if($get_notice != 'off') {
			?>
			<div class="notice notice-error is-dismissible" onclick="dismiss_notices('upgrade_notice');">
				<p style="margin-top: 10px"><strong><?php echo esc_html__('Upgrade Notice:','wp-ultimate-csv-importer');?> </strong> <?php echo esc_html__('Download and replace the latest version of','wp-ultimate-csv-importer');?> <a href="https://wordpress.org/plugins/wp-ultimate-csv-importer/" target="_blank">WP Ultimate CSV Importer</a> <?php echo esc_html__('for 10x faster import performance with easy user interface.','wp-ultimate-csv-importer');?> </p>
			</div>
			<?php
		}
	}
}

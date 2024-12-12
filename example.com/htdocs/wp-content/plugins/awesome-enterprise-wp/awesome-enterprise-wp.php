<?php
/*
Plugin Name: Awesome Enterprise WP
Plugin URI: http://www.getawesomestudio.com
Description: Awesome Enterprise is a shortcode-based low code platform with useful services and apps enabling us to easily create custom WordPress workflow. 
Version: 3.1.8
Author: WPoets Team
Author URI: http://www.wpoets.com
License: GPLv3 or Later
*/


if (!defined('IS_WP'))define('IS_WP', true);
if (!defined('AWESOME_DEBUG'))define('AWESOME_DEBUG', false);

if (!defined('ENV_CACHE_KEY'))define('ENV_CACHE_KEY', 'env_cache-'.$table_prefix.DB_NAME);

if (!defined('AWESOME_CORE_POST_TYPE'))define('AWESOME_CORE_POST_TYPE', 'awesome_core');
if (!defined('AWESOME_APPS_POST_TYPE'))define('AWESOME_APPS_POST_TYPE', 'aw2_app');

if (!defined('REQUEST_START_POINT'))define('REQUEST_START_POINT', '');

define('AWESOME_APP_BASE_PATH', SITE_URL . REQUEST_START_POINT);

define('HANDLERS_PATH', AWESOME_PATH.'/core-handlers');
define('WP_HANDLERS_PATH',  __DIR__ .'/handlers');
define('EXTRA_HANDLERS_PATH', AWESOME_PATH.'/extra-handlers');

if(!defined('AWESOME_PATH') || !file_exists(AWESOME_PATH.'/includes/aw2_library.php')){
	echo 'Issue with AWESOME_PATH '.AWESOME_PATH.' make sure it is defined and path readable';
	return;
}

require  __DIR__ .'/vendor/autoload.php';
require AWESOME_PATH.'/vendor/autoload.php';


$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
define('AWE_VERSION',$plugin_data['Version']);



$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
  'https://github.com/WPoets/awesome-enterprise-wp',
  __FILE__,
  'awesome-enterprise-wp'
);

//Optional: Check for automatical release
$myUpdateChecker->getVcsApi()->enableReleaseAssets();
/*********************** plugin-update-checker code end ***************************/
\register_activation_hook( __FILE__, 'aw2\db_delta\create_awesome_tables' );



require_once AWESOME_PATH.'/includes/util.php';
require_once AWESOME_PATH.'/includes/aw2_library.php';
require_once AWESOME_PATH.'/includes/error_log.php';

require_once AWESOME_PATH.'/includes/awesome_flow.php';
require_once AWESOME_PATH.'/includes/awesome_app.php';
require_once AWESOME_PATH.'/includes/awesome_auth.php';
require_once AWESOME_PATH.'/includes/awesome-controllers.php';

require_once __DIR__ .'/includes/awesome-wp-util.php';
require_once __DIR__ .'/includes/apps_setup_wp.php';
require_once __DIR__ .'/includes/app_seo.php';
require_once __DIR__ .'/includes/awesome-menus.php';
require_once __DIR__ .'/includes/app-rights.php';


require_once __DIR__ .'/includes/monoframe.php';
require_once __DIR__ .'/includes/wordpress-hooks.php';
require_once __DIR__ .'/libraries/db-delta.php';


aw2_library::load_handlers_from_path(HANDLERS_PATH,'structure','lang','cache','session');
aw2_library::load_handlers_from_path(HANDLERS_PATH,'utils');
aw2_library::load_handlers_from_path(HANDLERS_PATH,'database');
aw2_library::load_handlers_from_path(HANDLERS_PATH,'front-end');
aw2_library::load_handlers_from_path(HANDLERS_PATH,'controllers','connectors');

aw2_library::load_handlers_from_path(WP_HANDLERS_PATH,'wp');
aw2_library::load_all_extra_handlers();


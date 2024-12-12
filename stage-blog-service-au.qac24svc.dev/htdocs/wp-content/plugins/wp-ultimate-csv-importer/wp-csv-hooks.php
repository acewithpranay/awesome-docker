<?php 
 
// wp-csv-hooks.php
 
namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) ) {
        die;
}

global $plugin_ajax_hooks;
global $smackCLI;

if($smackCLI || (is_user_logged_in() && current_user_can('edit_published_posts'))){
$plugin_ajax_hooks = [

    'security_performance',
    'settings_options',
    'get_options',
    'support_mail',
    'send_subscribe_email',
    'parse_data',
    'total_records',
    'get_post_types',
    'get_taxonomies',
    'get_authors',
    'get_download',
    'mappingfields',
    'display_log',
    'zip_upload',
    'download_media_log',
    'download_log',
    'download_failed_log',
    'get_desktop',
    'get_ftp_url',
    'get_csv_url',
    'media_mappingfields',
    'get_parse_xml',
    'LineChart',
    'BarChart',
    'displayCSV',
    'updatefields',
    'image_options',
    'delete_image',
    'saveMappedFields',
    'StartImport',
    'GetProgress',
    'ImportState',
    'ImportStop',
    'checkmain_mode',
    'close_notification_action',
    'bulk_file_import',
    'bulk_import',
    'check_export',
    'PauseImport',
    'ResumeImport',
    'active_addons',
    'install_plugins',
    'activate_addon',
    'DeactivateMail',
    'get_ftp_details',
    'delete_log',
    'install_addon',
    'get_setting',
    'get_plugin_notice',
    'dismiss_notice',
    'helperImport',
    'helperSearch',
    'needHelper',
];  
}
else {
    $plugin_ajax_hooks = [];
}

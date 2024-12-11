<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly
class WpcompleteExtension extends ExtensionHandler{
	private static $instance = null;

	public static function getInstance() {
		if (WpcompleteExtension::$instance == null) {
			WpcompleteExtension::$instance = new WpcompleteExtension;
		}
		return WpcompleteExtension::$instance;
	}
    public function processExtension($data) {
	    $response = [];
        $import_type = $this->import_name_as($data);
      
        $wpcompletefields = array(
        'Checkbox' => 'checkbox',
		'Redirect URl' => 'redirect_url',
		'Course' => 'course');
      
		$wpcomplete_value = $this->convert_static_fields_to_array($wpcompletefields);
		$response['wpcomplete_fields'] = $wpcomplete_value ;
		return $response;
	}
    /**
	* Wpcomplete extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type ){
        if(is_plugin_active("wpcomplete/wpcomplete.php")){
            $posttype =get_option('wpcomplete_post_type');
            $types=explode(',',$posttype);
            if($import_type == 'Posts'){
                $import_types = 'post';
            }
            elseif($import_type == 'Pages'){
                $import_types = 'page';
            }
            elseif($import_type == 'WooCommerce Product'){
                $import_types = 'product';
            }
            else{
                $import_types = $import_type;
            }
            if(in_array($import_types,$types)){
                if($import_type == 'nav_menu_item'){
                    return false;
                }
                $import_type = $this->import_name_as($import_type);
                if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'event' || $import_type == 'WooCommerce' ) {	
                    return true;
                }
                if($import_type == 'ticket'){
                    if(is_plugin_active('events-manager/events-manager.php')){
                        return false;
                    }else{
                        return true;
                    }
                }
                else{
                    return false;
                }
            }
            
        }
	}
}
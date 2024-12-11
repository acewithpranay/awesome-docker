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
class WPcompleteImport {
    private static $wpcomplete_instance = null;

    public static function getInstance() {
		
			if (WPCompleteImport::$wpcomplete_instance == null) {
				WPCompleteImport::$wpcomplete_instance = new WPCompleteImport;
				return WPCompleteImport::$wpcomplete_instance;
			}
			return WPCompleteImport::$wpcomplete_instance;
		}
		
    function set_wpcomplete_values($header_array ,$value_array , $map, $post_id , $type, $hash_key,$gmode,$templatekey){	
			$post_values = [];
			$helpers_instance = ImportHelpers::getInstance();
			$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);

			$this->wpcomplete_import_function($post_values,$type, $post_id, $header_array , $value_array, $hash_key,$gmode,$templatekey);
    }

    function wpcomplete_import_function($data_array, $importas, $pID, $header_array , $value_array, $hash_key,$gmode,$templatekey) {
        if(isset($data_array['checkbox']) && $data_array['checkbox']=='yes'){
            $d_array =array();
            $d_array['buttons'][0] =$pID;
            if(isset($data_array['course'])){
                $d_array['course'] =$data_array['course'];
            }
            if(isset($data_array['redirect_url'])){
                $d_array['redirect']['title'] =$data_array['redirect_url'];
                $d_array['redirect']['url'] ='';
            }

        }
        if(!empty($d_array)){
            update_post_meta($pID,'wpcomplete',json_encode($d_array));
        }
    }	
}
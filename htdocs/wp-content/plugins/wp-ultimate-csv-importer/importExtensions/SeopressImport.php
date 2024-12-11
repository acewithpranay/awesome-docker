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

class SeoPressImport {
    private static $seopress_instance = null;

    public static function getInstance() {
		
			if (SeoPressImport::$seopress_instance == null) {
				SeoPressImport::$seopress_instance = new SeoPressImport;
				return SeoPressImport::$seopress_instance;
			}
			return SeoPressImport::$seopress_instance;
		}
		
    function set_seopress_values($header_array ,$value_array , $map, $post_id , $type, $hash_key,$gmode,$templatekey){	
		$post_values = [];
			$helpers_instance = ImportHelpers::getInstance();
			$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);

			$this->seopress_import_function($post_values,$type, $post_id, $header_array , $value_array, $hash_key,$gmode,$templatekey);
    }

    function seopress_import_function($data_array, $importas, $pID, $header_array , $value_array, $hash_key,$gmode,$templatekey) {
		
		$createdFields = $seoPressData = array();
		$media_instance = MediaHandling::getInstance();

		if (!empty ($data_array)) {
			foreach ($data_array as $dataKey => $dataValue) {
				update_post_meta($pID, $dataKey, $dataValue);
			}
		}		

		return $createdFields;
	}
	
}
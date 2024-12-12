<?php

/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

 namespace Smackcoders\FCSV;

if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

class FIFUImport
{
	private static $fifu_instance = null;

	public static function getInstance()
	{
		if (FIFUImport::$fifu_instance == null) {
			FIFUImport::$fifu_instance = new FIFUImport;
			return FIFUImport::$fifu_instance;
		}
		return FIFUImport::$fifu_instance;
    }
    
    public function set_fifu_values($header_array, $value_array, $map, $post_id, $type, $mode){
        $post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map, $header_array, $value_array);

		$this->fifu_values_import($post_values, $post_id, $type, $mode);	

    }

    public function fifu_values_import($post_values, $post_id, $type, $mode){
        global $wpdb;	
        $fifu_array = [];
      
        $author_id = get_option('fifu_author');

        $attachment_id = wp_insert_attachment( array(
            'post_mime_type' => 'image/jpeg', 
            'post_status' => 'inherit',
            'post_title' => $post_values['fifu_image_alt'],
            'post_type' => 'attachment',
            'post_author' => $author_id,
            'guid'=>$post_values['fifu_image_url'],
            'post_parent'=>$post_id, 
           )); 


       $fifu_array = [];
       $fifu_array['fifu_image_url'] = isset($post_values['fifu_image_url']) ? $post_values['fifu_image_url'] : '';
       $fifu_array['fifu_image_alt'] = isset($post_values['fifu_image_alt']) ? $post_values['fifu_image_alt'] : '';
       $fifu_array['_thumbnail_id'] = isset($post_values['fifu_image_url']) ? $attachment_id : '';

       foreach ($fifu_array as $fifu_key => $fifu_value) {
           update_post_meta($post_id, $fifu_key, $fifu_value);
       }
   
       update_post_meta($attachment_id, '_wp_attached_file', $post_values['fifu_image_url']);
       update_post_meta($attachment_id, '_wp_attachment_image_alt', $post_values['fifu_image_alt']);
   
      }
}

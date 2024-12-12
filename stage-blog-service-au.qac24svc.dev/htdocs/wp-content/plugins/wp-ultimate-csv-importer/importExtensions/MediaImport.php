<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\FCSV;

use Smackcoders\FCSV\CoreFieldsImport;
use Smackcoders\FCSV\ImportHelpers;
use Smackcoders\FCSV\LogManager;
use Smackcoders\FCSV\MediaHandling;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class MediaImport {
    private static $media_core_instance = null,$media_instance;

    public static function getInstance() {
		if (MediaImport::$media_core_instance == null) {
			MediaImport::$media_core_instance = new MediaImport;
			MediaImport::$media_instance = new MediaHandling;
			return MediaImport::$media_core_instance;
		}
		return MediaImport::$media_core_instance;
    }
	public function media_fields_import($data_array , $mode , $type , $media_type , $unikey , $unikey_name, $line_number,$hash_key,$header_array ,$value_array) {
		$returnArr = array();	
		global $wpdb;
        $image_type = '';
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		$log_manager_instance = LogManager::getInstance();
		global $core_instance;
		$import_detail_table_name = $wpdb->prefix ."import_detail_log";
        $media_handle = get_option('smack_image_options');
		$updated_row_counts = $helpers_instance->update_count($unikey,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];
		$failed_count = $updated_row_counts['failed'];
		$title = isset($data_array['title']) ? $data_array['title'] : '';
		$caption = isset($data_array['caption']) ? $data_array['caption'] : '';
		$alt_text = isset($data_array['alt_text']) ? $data_array['alt_text'] : '';
		$description = isset($data_array['description']) ? $data_array['description'] : '';
		$actual_url = isset($data_array['actual_url']) ? $data_array['actual_url'] : '';
		if(!empty($data_array['file_name'])){
			$sanitized_filename = str_replace(' ', '-', basename($data_array['file_name']));
			$img = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $sanitized_filename);
			$file_name = $img;
		}
		if($media_type == 'External'){
			$img = isset($data_array['actual_url']) ? $data_array['actual_url'] : '';
		}
		if($media_handle['media_settings']['media_handle_option'] == 'true'){
			$media_handle['media_settings']['title'] = $title ;
			$media_handle['media_settings']['caption'] = $caption ;
			$media_handle['media_settings']['alttext'] = $alt_text;
			$media_handle['media_settings']['description'] = $description ;
			$media_handle['media_settings']['file_name'] = $file_name;

			update_option('smack_image_options', $media_handle); 
		}
		if ($mode == 'Insert') {
			$mode_of_affect = 'Inserted';
			if(!empty($img)){
				$attach_id = MediaImport::$media_instance->image_meta_table_entry($line_number,$data_array, '', '',$img, $hash_key, 'Media', 'Media','','','','','','','',$media_type);
				$attachment_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND post_title = 'image-failed' AND ID ={$attach_id}", ARRAY_A);
			}else{
				$core_instance->detailed_log[$line_number]['Message'] = "The provided image file is empty. Double-check your import file and retry";
				$wpdb->get_results("UPDATE $import_detail_table_name SET failed = $failed_count WHERE $unikey_name = '$unikey'");
				return array('MODE' => $mode, 'ERROR_MSG' =>"Can't insert this Image" );
			}		
			if(!empty($attachment_id)) {
				if($media_type == 'Local'){
					$data = array('media_id'   => $attach_id,'file_url'   => wp_get_attachment_url($attach_id),'file_name' => $file_name,'title' => $title,'caption' => $caption,'alt_text'=> $alt_text,'description' => $description,'status' => 'failed');
					$core_instance->detailed_log[$line_number]['Message'] = "Unable to detect the image in your import file. Please check and try again.";
				}else if($media_type == 'External'){
					$data = array('media_id'   => $attach_id,'file_url'   => wp_get_attachment_url($attach_id),'actual_url'  => $actual_url,'file_name' => $file_name,'title' => $title,'caption' => $caption,'alt_text'=> $alt_text,'description' => $description,'status' => 'failed');
					$core_instance->detailed_log[$line_number]['Message'] = "The provided image file URL is invalid. Please verify the URL and try again";
				}
				$core_instance->media_log[$line_number] = $data;
				$core_instance->detailed_log[$line_number]['state'] = 'Failed';
				$wpdb->get_results("UPDATE $import_detail_table_name SET failed = $failed_count WHERE $unikey_name = '$unikey'");
				return array('MODE' => $mode, 'ERROR_MSG' =>"Can't insert this Image" );
			}else{
				if($media_type == 'Local'){
					$data = $this->imageMetaImport($attach_id,$data_array,$media_type);
					$core_instance->media_log[$line_number] = $data;
					$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Image ID: ' . $attach_id .' FileName: '.$file_name;
				}else if($media_type == 'External'){
					$data = array('media_id'   => $attach_id,'file_url'   => wp_get_attachment_url($attach_id),'actual_url'  => $actual_url,'file_name' => $file_name,'title'  => $title,'caption'  => $caption,'alt_text'    => $alt_text,'description' => $description,'status' => 'success');
					$core_instance->media_log[$line_number] = $data;
					$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Image ID: ' . $attach_id .' FileName: '.$file_name;
				}
				$core_instance->detailed_log[$line_number]['state'] = 'Inserted';
				$wpdb->get_results("UPDATE $import_detail_table_name SET created = $created_count WHERE $unikey_name = '$unikey'");				
			}

		}	
		if($media_handle['media_settings']['media_handle_option'] == 'true'){
			$media_handle['media_settings']['title'] =  '';
			$media_handle['media_settings']['caption'] = '';
			$media_handle['media_settings']['alttext'] = '';
			$media_handle['media_settings']['description'] = '';
			$media_handle['media_settings']['file_name'] = '';
			update_option('smack_image_options', $media_handle); 
		}
		$returnArr['ID'] = $attach_id;
		$returnArr['MODE'] = $mode_of_affect;
		return $returnArr;
	}
	public function imageMetaImport($attach_id,$data_array,$media_type){
		if(!empty($data_array['file_name'])){
			$sanitized_filename = str_replace(' ', '-', basename($data_array['file_name']));
			$file_name = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $sanitized_filename);
		}
		$title = isset($data_array['title']) ? $data_array['title'] : '';
		$caption = isset($data_array['caption']) ? $data_array['caption'] : '';
		$alt_text = isset($data_array['alt_text']) ? $data_array['alt_text'] : '';
		$description = isset($data_array['description']) ? $data_array['description'] : '';
		if(isset($caption) || isset($description)){
			$updated=wp_update_post(array(
				'ID'           =>$attach_id,
				'post_content' =>$description,
				'post_excerpt' =>$caption
			));
		}
		if(!empty($title)){
			wp_update_post(array(
				'ID'           =>$attach_id,
				'post_title'   =>$title
			));
		}
		if(isset($alt_text)){  
			$updated = update_post_meta($attach_id, '_wp_attachment_image_alt', $alt_text);
		}
			$attachment_data = array('media_id'   => $attach_id,'file_url'   => wp_get_attachment_url($attach_id),'file_name' => $file_name,'title'  => $title,'caption'  => $caption,'alt_text'    => $alt_text,'description' => $description,'status' => 'success');
			return $attachment_data;
	}
}
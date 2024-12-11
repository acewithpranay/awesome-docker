<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class MediaHandling{
	private static $instance=null,$smack_instance;
	public $header_array;
	public $value_array;

	public function __construct(){
		
		include_once(ABSPATH . 'wp-admin/includes/image.php');
		add_action('wp_ajax_zip_upload' , array($this , 'zipImageUpload'));	
		add_action('wp_ajax_image_options', array($this , 'imageOptions'));
		add_action('wp_ajax_delete_image' , array($this , 'deleteImage'));
	}

	public static function imageOptions(){	
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		$media_settings['media_handle_option'] = sanitize_text_field($_POST['media_handle_option']);
		$media_settings['use_ExistingImage'] = sanitize_text_field($_POST['use_ExistingImage']);
		$media_settings['enable_postcontent_image'] = sanitize_text_field($_POST['postContent_image_option']);
		$image_info = array(
			'media_settings'  => $media_settings
		);
		update_option( 'smack_image_options', $image_info );
		$result['success'] = 'true';
		echo wp_json_encode($result);
		wp_die();
	}
	public static function getInstance() {
		if (MediaHandling::$instance == null) {
			MediaHandling::$instance = new MediaHandling;
			MediaHandling::$smack_instance = SmackCSV::getInstance();
			return MediaHandling::$instance;
		}
		return MediaHandling::$instance;
	}
	public function zipImageUpload() {
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		 // Check if the ZIP extension is loaded
		 if (!extension_loaded('zip')) {
			$result['success'] = false;
			$result['message'] = 'The PHP ZIP extension is not installed. Please install it.';
			echo wp_json_encode($result);
			wp_die();
		}
	
		// Check if a file was uploaded
		if (isset($_FILES['zipFile']['name'])) {
			$zip_file_name = $_FILES['zipFile']['name'];
			$file_ext = pathinfo($zip_file_name, PATHINFO_EXTENSION);
	
			// Validate file extension
			if (strtolower($file_ext) !== 'zip') {
				$result['success'] = false;
				$result['message'] = 'Invalid file format. Please upload a zip file.';
				echo wp_json_encode($result);
				wp_die();
			}
	
			$hash_key = MediaHandling::$smack_instance->convert_string2hash_key($zip_file_name);
			$media_dir = wp_get_upload_dir();
			$upload_dir = MediaHandling::$smack_instance->create_upload_dir();
			$path = $upload_dir . $hash_key . '.zip';
			$extract_path = $media_dir['path'] . '/';
			
			if (file_exists($path)) {
				chmod($path, 0777);
			}
	
			move_uploaded_file($_FILES['zipFile']['tmp_name'], $path);
			$zip = new \ZipArchive;
			$res = $zip->open($path);
	
			if ($res === TRUE) {
				$filename = [];
				$size = [];
				$kbsize = [];
				
				for ($i = 0; $i < $zip->numFiles; $i++) {
					$filename[$i] = $zip->getNameIndex($i);
					
					// Skip directories
					if (substr($filename[$i], -1) == '/') continue;
					$sanitized_filename = str_replace(' ', '-', basename($filename[$i]));
					$sanitized_filename = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $sanitized_filename);
					$full_extract_path = $extract_path . $sanitized_filename;
					$fp = $zip->getStream($filename[$i]);
					$size[$i] = $zip->statIndex($i)['size'];
					$kbsize[$i] = $this->convertToReadableSize($size[$i]);
					$ofp = fopen($full_extract_path, 'w');
					if (!$fp)
						throw new Exception('Unable to extract the file.');
					
					while (!feof($fp))
						fwrite($ofp, fread($fp, 8192));
					
					fclose($fp);
					fclose($ofp);
				}
	
				$zip->close();
				$result['success'] = true;
				$result['message'] ='success';
				$result['zip_file_name'] = $zip_file_name;
				$result['count'] = $zip->numFiles;
				$result['filename'] = $filename;
				$result['size'] = $kbsize;
			} else {
				$result['success'] = false;
				$result['message'] = 'Failed to open the zip file.';
			}
		} else {
			$result['success'] = false;
			$result['message'] = 'No file uploaded.';
		}
	
		echo wp_json_encode($result);
		wp_die();
	}
	
	public function convertToReadableSize($size){
		$base = log($size) / log(1024);
		$suffix = array("", "KB", "MB", "GB", "TB");
		$f_base = floor($base);
		return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
	}

	public function deleteImage(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		$images = json_decode(stripslashes($_POST['images']), true);
		if (!empty($images)) {
				// Get the media upload directory
			$media_dir = wp_get_upload_dir();
			$upload_path = $media_dir['path'];
			foreach ($images as $image) {
				// Ensure that the image name is a valid file name
				if(strpos($image , '/')){
					$img_parts = explode('/', $image);
					$deleteimage = end($img_parts);
				}else{
					$deleteimage=$image;
				}
				$file_path = $upload_path . '/' . $deleteimage;	
				if (file_exists($file_path)) {
					unlink($file_path);
				}
			}
			$result['success'] = 'true';
		}else{
		$result['success'] = 'true';
		}
		
		echo wp_json_encode($result);
		wp_die();    
	}
	public function media_handling($img_url , $post_id , $data_array = null,$module = null, $image_type = null ,$hash_key = null,$templatekey = null,$header_array=null,$value_array=null,$indexs=null,$acf_wpname_element=null,$acf_image_meta=null,$media_type=null,$line_number = null){
		global $wpdb;

		if(strpos($img_url,'%') !==false){

		}
		else{
			$encodedurl = urlencode($img_url);
			$img_url = urldecode($encodedurl);
		}

		$url = parse_url($img_url);
		if($hash_key == null){
			$hash_key = "";
		}
		$media_handle = get_option('smack_image_options');
		if(!empty($media_handle['media_settings']['file_name']) && $media_type == 'External'){
			$image_file_name = $media_handle['media_settings']['file_name'];
			if (strpos($image_file_name, ' ') !== false) {
				$image_file_name = str_replace(' ', '-', $image_file_name);
				$image_file_name = preg_replace('/[^a-zA-Z0-9._\-]/', '', $image_file_name);
			}
			$attachment_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND guid LIKE '%" . esc_sql( $wpdb->esc_like( $image_file_name ) ) . "%'" );
			if($attachment_id != false){
				$image_title = $image_file_name;
			}else{
				$image_title = $image_file_name;
			// $get_path_values = $this->get_filename_path($img_url,$media_type);
			// $image_title = isset($get_path_values['fimg_name']) ? $get_path_values['fimg_name'] : '';
			}
		}
		else if (isset($url['scheme']) && ($url['scheme'] == 'http' || $url['scheme'] == 'https')) {		
			$get_path_values = $this->get_filename_path($img_url,'');
			$image_title = isset($get_path_values['fimg_name']) ? $get_path_values['fimg_name'] : '';
		}
		else{
			$image_title=preg_replace('/\\.[^.\\s]{3,4}$/', '', $img_url);
		}
		if($media_handle['media_settings']['use_ExistingImage'] == 'true'){
			if(is_numeric($img_url)){
				$attach_id=$img_url;
				if(!empty($data_array['featured_image'])) {
					set_post_thumbnail( $post_id, $attach_id );
				}
				return $attach_id;
			}
			else{
				$attachment_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND guid LIKE '%$image_title%'", ARRAY_A);
			}
			if(is_array($attachment_id) && !empty($attachment_id[0]['ID']) && $image_type != 'Featured'){
				if($media_type == 'Local' || $media_type == 'External'){
					return $attachment_id[0]['ID'];
				}
					$table_name = $wpdb->prefix . 'smackcsv_file_events';
					$post_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = '{$post_id}' AND post_status != 'trash'");
					$file_name = $wpdb->get_var("SELECT file_name FROM $table_name WHERE hash_key = '$hash_key'");
					$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";                                                                   
					$attach_id = $attachment_id[0]['ID'];
					$check_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE ID ='{$attach_id}' AND post_title ='image-failed' AND post_type = 'attachment' AND guid LIKE '%$image_title%'", ARRAY_A);
					if(!empty($check_id)){
						$failed_ids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id='{$post_id}' AND media_id = '{$attach_id}'");
						if(!empty($failed_ids) && $failed_ids[0]->post_id != $post_id){
							$attach_id = $check_id[0]['ID'];
								$this->store_failed_image_ids($attach_id);
								$this->failed_media_data($line_number,$post_id,$post_title,$attach_id,$img_url);
							
						}elseif(!empty($failed_ids) && ($failed_ids[0]->post_id == $post_id) && ($check_id[0]['ID'] == $failed_ids[0]->media_id)){
							$attach_id = $check_id[0]['ID'];
								$this->store_failed_image_ids($attach_id);
								$this->failed_media_data($line_number,$post_id,$post_title,$attach_id,$img_url);
						}
						elseif(empty($failed_ids) ){
								$this->store_failed_image_ids($attach_id);
								$this->failed_media_data($line_number,$post_id,$post_title,$attach_id,$img_url);
						}
						
						return $attach_id;
					}				   
			}
			else{
				$attach_id = $this->image_function($img_url , $post_id , $data_array,'','use_existing_image',$header_array,$value_array);
			}

		}
		else{
			$img_url = is_array($img_url) ? implode(',', array_filter($img_url)) : $img_url;
			$hash_key = is_array($hash_key) ? implode(',', array_filter($hash_key)) : $hash_key;
			$templatekey = is_array($templatekey) ? implode(',', array_filter($templatekey)) : $templatekey;
			$module = is_array($module) ? implode(',', array_filter($module)) : $module;
			$image_type = is_array($image_type) ? implode(',', array_filter($image_type)) : $image_type;
			if(strpos($img_url,'%') !==false){
			}
			else{
				$img_url = esc_url($img_url);
			}
			$hash_key = esc_sql($hash_key);
			$templatekey = esc_sql($templatekey);
			$module = esc_sql($module);
			$image_type = esc_sql($image_type);
			
			$attach_id = $this->image_function($img_url, $post_id, $data_array,'','',$header_array,$value_array);
			// if ($attach_id != null) {
			// 	global $wpdb;
			// 	$image_table = $wpdb->prefix . "ultimate_csv_importer_media";
			// 	$wpdb->query($wpdb->prepare("INSERT INTO $image_table (image_url, attach_id, post_id, hash_key, templatekey, module, image_type, status) VALUES (%s, %d, %d, %s, %s, %s, %s, 'Completed')",$img_url,$attach_id,$post_id,$hash_key,$templatekey,$module,$image_type));
			// }

		}
		return $attach_id;
	}
	public function image_function($f_img , $post_id , $data_array = null,$option_name = null, $use_existing_image = false,$header_array = null , $value_array = null){
		global $wpdb;
		$media_handle = get_option('smack_image_options');
		if(!empty($header_array) && !empty($value_array) ){
			$media_settings = array_combine($header_array,$value_array);
		}

		if(preg_match_all('/\b(?:(?:https?|http|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $f_img , $matchedlist, PREG_PATTERN_ORDER)) {
			$f_img = $f_img;
		}
		 
		else{
			$media_dir = wp_get_upload_dir();
			$names = glob($media_dir['path'] . '/*.*');
			$image_found = false;
			foreach ($names as $values) {
				if (!empty($f_img) && strpos($values, $f_img) !== false) {
					$image_found = true;
					if (!empty($media_handle['media_settings']['file_name'])) {
						$file_type = wp_check_filetype($f_img, null);
						$ext = '.' . $file_type['ext'];
						$fimg_name = $media_handle['media_settings']['file_name']. $ext;
						$f_img = $media_dir['url'] . '/' . $fimg_name;
					} else {
						$f_img = $media_dir['url'] . '/' . $f_img;
					}
					break;
				}
			}
			if ($image_found) {
			} else {
				return null;
			}		             
		}
		$image_name = pathinfo($f_img);
		if(!empty($media_handle['media_settings']['file_name']) && isset($media_handle['media_settings']['file_name'])){
			$file_type = wp_check_filetype( $f_img, null );
			if(empty($file_type['ext'])){
				$file_type['ext'] = 'jpeg';
			}
			$ext = '.'. $file_type['ext'];
			if(!empty($media_handle['media_settings']['file_name'])){
				if(strrpos($media_handle['media_settings']['file_name'], '.')){
					$fimg_name = $media_handle['media_settings']['file_name'];
				}else{
					$fimg_name = $media_handle['media_settings']['file_name'].$ext;
				}
			}
		}		
		else{
			$fimg_name = $image_name['basename'];
		}
		$file_type = wp_check_filetype( $fimg_name, null );
		if($use_existing_image){
			if(empty($file_type['ext'])){
				$fimg_name = @basename($f_img);
				$fimg_name = str_replace(' ', '-', trim($fimg_name));
				$fimg_name = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $fimg_name);
			}
			$attachment_id = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix."posts WHERE post_type = 'attachment' AND guid LIKE '%$fimg_name'");
			if($attachment_id){
				$this->imageMetaImport($attachment_id,$media_handle,$header_array,$value_array);
				if(!empty($data_array['featured_image'])){
					set_post_thumbnail( $post_id, $attachment_id );
					return $attachment_id;
				}else{
					return $attachment_id;
				}
			}
		}
		$attachment_title = sanitize_file_name( pathinfo( $fimg_name, PATHINFO_FILENAME ) );
		$file_type = wp_check_filetype( $fimg_name, null ); 
		$dir = wp_upload_dir();
		$dirname = date('Y') . '/' . date('m');
		$uploads_use_yearmonth = get_option('uploads_use_yearmonth_folders');
        if($uploads_use_yearmonth == 1){
            $uploaddir_paths = $dir ['basedir'] . '/' . $dirname ;
            $uploaddir_url = $dir ['baseurl'] . '/' . $dirname;
        }
        else{
            $uploaddir_paths = $dir ['basedir'];
            $uploaddir_url = $dir ['baseurl'];
        }
		$f_img = str_replace(" ","%20",$f_img);
		if(empty($file_type['ext'])){
			$fimg_name = @basename($f_img);
			$fimg_name = str_replace(' ', '-', trim($fimg_name));
			$fimg_name = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $fimg_name);
		}
		if ($uploaddir_paths != "" && $uploaddir_paths) {
			if (strpos($fimg_name, ' ') !== false) {
				$fimg_name = str_replace(' ', '-', $fimg_name);
				$fimg_name = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $fimg_name);
			}
			$uploaddir_path = $uploaddir_paths . "/" . $fimg_name;
		}
		if(isset($media_dir['url'])){
			$post_info = array(
				'guid'           => $uploaddir_url . "/" .  $fimg_name,
				'post_mime_type' => $file_type['type'],
				'post_title'     => $attachment_title,
				'post_content'   => '',
				'post_status'    => 'inherit',
				'post_author'  => isset($data_array['post_author']) ? $data_array['post_author'] : ''
			);
			$attach_id = wp_insert_attachment( $post_info,$uploaddir_path);
			$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaddir_path );
			wp_update_attachment_metadata( $attach_id,  $attach_data );

			$this->imageMetaImport($attach_id,$media_handle,$header_array,$value_array);
			return $attach_id;
		}
			if($file_type['ext'] == 'jpeg'){
				$response = wp_safe_remote_get($f_img, array( 'timeout' => 30));		
			}else{
				$response = wp_safe_remote_get($f_img, array( 'timeout' => 10));		
			}	
			if(is_wp_error($response))	{
				return null;
			}
			$rawdata =  wp_remote_retrieve_body($response);
		
		$http_code = wp_remote_retrieve_response_code($response);
		if($http_code == 404 || $http_code == 403 || $http_code == 500 || $http_code == 401 || $http_code == 408 || $http_code == 502 || $http_code == 503 || $http_code == 504){
			return null;
		}

		if ( $http_code != 200 && strpos( $rawdata, 'Not Found' ) != 0 ) {
			return null;
		}
		if(is_plugin_active('exmage-wp-image-links/exmage-wp-image-links.php')){
			$guid =$fimg_name;
		}
		if ($rawdata == false) {
			return null;
		} else {		
			if(is_plugin_active('exmage-wp-image-links/exmage-wp-image-links.php')){
				$link = new \EXMAGE_WP_IMAGE_LINKS;
				$postID = $link->add_image($data_array['featured_image'],$value);
				wp_update_post(array(
					'ID'           => $postID['id'],
					'post_title'   => $data_array['title'],
					'post_content' => $data_array['description'],
					'post_excerpt' => $data_array['caption']
				));
                if($postID['id'] != null && isset($data_array['alt_text'])){  
					update_post_meta($postID['id'], '_wp_attachment_image_alt', $data_array['alt_text']);
				}
			}
			else{
				if (file_exists($uploaddir_path)) {
					$i = 1;
					$exist = true;
					while($exist){
						$fimg_name = $attachment_title . "-" . $i . "." . $file_type['ext'];        
						$uploaddir_path = $uploaddir_paths . "/" . $fimg_name;

						if (file_exists($uploaddir_path)) {
							$i = $i + 1;
						}
						else{
							$exist = false;
						}
					}
				}
				$fp = fopen($uploaddir_path, 'x');
				if ($fp === false) {
					return null;
				}
				fwrite($fp, $rawdata);
				fclose($fp);
			}
		}
		if(empty($file_type['type'])){
			$file_type['type'] = 'image/jpeg';
		}
		if(is_plugin_active('exmage-wp-image-links/exmage-wp-image-links.php')){
			$guids =$data_array['featured_image'];
		}
		else{
			$guids=$uploaddir_url . "/" .  $fimg_name;
		}
		if(!empty($data_array['title'])){
			$attachment_title = $data_array['title'];
		}else{
			$attachment_title = str_replace('-', ' ', $attachment_title);
		}

		$post_info = array(
			'guid'           => $guids,
			'post_mime_type' => $file_type['type'],
			'post_title'     => $attachment_title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		if(!is_plugin_active('exmage-wp-image-links/exmage-wp-image-links.php')){
			$attach_id = wp_insert_attachment( $post_info,$uploaddir_path, $post_id );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaddir_path );
			wp_update_attachment_metadata( $attach_id,  $attach_data );
		}
		$this->imageMetaImport($attach_id,$media_handle,$header_array,$value_array);
		
		if(!empty($data_array['featured_image'])) {
			set_post_thumbnail( $post_id, $attach_id );
		}
		return $attach_id;
	}
	public function imageMetaImport($attach_id,$media_handle,$header_array,$value_array){
		$media_handle = get_option('smack_image_options');
		if($media_handle['media_settings']['media_handle_option']){
			if(isset($media_handle['media_settings']['alttext'])) {
				$alttext ['_wp_attachment_image_alt'] = $media_handle['media_settings']['alttext'];
			} 
			if(isset($media_handle['postcontent_image_alt'])) {
				$alttext ['_wp_postcontent_image_alt'] = $media_handle['postcontent_image_alt'];
			}
			if(isset($media_handle['media_settings']['caption']) || isset($media_handle['media_settings']['description'])){
				wp_update_post(array(
					'ID'           =>$attach_id,
					'post_content' =>$media_handle['media_settings']['description'],
					'post_excerpt' =>$media_handle['media_settings']['caption']
				));
			}
			if(!empty($media_handle['media_settings']['title'])){
				wp_update_post(array(
					'ID'           =>$attach_id,
					'post_title'   =>$media_handle['media_settings']['title']
				));
			}
			if($attach_id != null && isset($alttext['_wp_attachment_image_alt'])){  
				update_post_meta($attach_id, '_wp_attachment_image_alt', $alttext['_wp_attachment_image_alt']);
			}

			if($attach_id != null && isset($alttext['_wp_postcontent_image_alt'])){  
				update_post_meta($attach_id, '_wp_attachment_image_alt', $alttext['_wp_postcontent_image_alt']);
			}
		}
	}

	public function get_filename_path($image_url,$media_type=null){
		$media_handle = get_option('smack_image_options');
		if(!empty($media_handle['media_settings']['file_name']) && $media_type == 'External'){
			$fimg_name = $media_handle['media_settings']['file_name'];
			$fimg_name = str_replace(' ', '-', trim($fimg_name));
			$fimg_name = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $fimg_name);
			$dir = wp_upload_dir();
			$dirname = date('Y') . '/' . date('m');
			$uploads_use_yearmonth = get_option('uploads_use_yearmonth_folders');
			if($uploads_use_yearmonth == 1){
				$uploaddir_paths = $dir ['basedir'] . '/' . $dirname ;
				$uploaddir_url = $dir ['baseurl'] . '/' . $dirname;
			}
			else{
				$uploaddir_paths = $dir ['basedir'];
				$uploaddir_url = $dir ['baseurl'];
			}
			if ($uploaddir_paths != "" && $uploaddir_paths) {
				$uploaddir_path = $uploaddir_paths . "/" . $fimg_name;
			}
			return ['uploaddir_path' => $uploaddir_path,'uploaddir_url' => $uploaddir_url,'fimg_name' => $fimg_name];
		}else{
			$image_name = pathinfo($image_url);
			$fimg_name = $image_name['basename'];
			$fimg_name_without_ext = $image_name['filename'];
			if(empty($fimg_name_without_ext)){
				$fimg_name_without_ext = $fimg_name;
			}
			$file_type = wp_check_filetype( $fimg_name, null );
			if(empty($file_type['ext'])){
				$fimg_name = @basename($image_url);
				$fimg_name = str_replace(' ', '-', trim($fimg_name));
				$fimg_name = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $fimg_name);
			}

			if (strstr($image_url, 'https://drive.google.com')){
				preg_match('/[?&]id=([^&]+)/', $image_url, $matches);
				$fimg_name = isset($matches[1]) ? $matches[1] : basename($image_url);	
			}
			$dir = wp_upload_dir();
			$dirname = date('Y') . '/' . date('m');
			$uploads_use_yearmonth = get_option('uploads_use_yearmonth_folders');
			if($uploads_use_yearmonth == 1){
				$uploaddir_paths = $dir ['basedir'] . '/' . $dirname ;
				$uploaddir_url = $dir ['baseurl'] . '/' . $dirname;
			}
			else{
				$uploaddir_paths = $dir ['basedir'];
				$uploaddir_url = $dir ['baseurl'];
			}
			if ($uploaddir_paths != "" && $uploaddir_paths) {
				$uploaddir_path = $uploaddir_paths . "/" . $fimg_name;
			}

			return ['uploaddir_path' => $uploaddir_path,'uploaddir_url' => $uploaddir_url,'fimg_name' => $fimg_name];	

		}
		

	}
	public function image_meta_table_entry($line_number, $post_values, $post_id, $acf_wpname_element, $acf_csv_name, $hash_key, $plugin, $get_import_type, $templatekey = null, $gmode = null, $header_array = null, $value_array = null, $imgformat = null, $typecct = null, $indexs = null, $media_type = null) {
		global $wpdb,$core_instance;
		$core_instance = CoreFieldsImport::getInstance();
		$acf_wpname_element = isset($acf_wpname_element) ? $acf_wpname_element : '';
		$table_name = $wpdb->prefix . 'smackcsv_file_events';
		$file_name = $wpdb->get_var("SELECT file_name FROM $table_name WHERE hash_key = '$hash_key'");
		$post_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = '{$post_id}' AND post_status != 'trash'");
		$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";
		$failed_ids = $wpdb->get_results("SELECT post_title, post_id, image_shortcode, media_id, original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE image_shortcode = 'Featured_image_' AND post_id = '{$post_id}' AND original_image = '{$acf_csv_name}'");

		$get_path_values = $this->get_filename_path($acf_csv_name,$media_type);
		$uploaddir_path = $get_path_values['uploaddir_path'] ?? '';
		$uploaddir_url = $get_path_values['uploaddir_url'] ?? '';
		$fimg_name = $get_path_values['fimg_name'] ?? '';
		$file_type = 'image/jpeg';
	
		if ($plugin == 'Media') {
			return $this->handle_media_image($core_instance,$acf_csv_name, $post_id, $post_values, $plugin, $get_import_type, $acf_wpname_element, $hash_key, $header_array, $value_array, $line_number, $indexs, $media_type, $uploaddir_url, $fimg_name, $file_type, $uploaddir_path);
		}
	
		if (isset($post_id) && !empty($acf_csv_name)) {
			if ($plugin == 'inline') {
				return $this->handle_inline_image($core_instance,$acf_csv_name, $post_id, $post_values, $plugin, $get_import_type, $acf_wpname_element, $hash_key, $header_array, $value_array, $line_number, $indexs, $uploaddir_url, $fimg_name, $file_type, $uploaddir_path, $shortcode_table, $post_title, $file_name);
			}
	
			if ($plugin == 'Featured') {
				return $this->handle_featured_image($core_instance,$acf_csv_name, $post_id, $post_values, $plugin, $get_import_type, $acf_wpname_element, $hash_key, $header_array, $value_array, $line_number, $uploaddir_url, $fimg_name, $file_type, $uploaddir_path, $shortcode_table, $post_title, $file_name, $failed_ids);
			}
	
			return $this->handle_custom_image($core_instance,$acf_csv_name, $post_id, $post_values, $plugin, $get_import_type, $acf_wpname_element, $hash_key, $header_array, $value_array,$line_number, $imgformat, $typecct, $indexs, $uploaddir_url, $fimg_name, $file_type, $uploaddir_path, $shortcode_table, $post_title, $file_name);
		}
	
		return '';
	}
	
	public function handle_media_image($core_instance,$acf_csv_name, $post_id, $post_values, $plugin, $get_import_type, $acf_wpname_element, $hash_key, $header_array, $value_array, $line_number, $indexs, $media_type, $uploaddir_url, $fimg_name, $file_type, $uploaddir_path) {
		$media_handle = get_option('smack_image_options');
		$attach_id = $this->media_handling($acf_csv_name, $post_id, $post_values, $get_import_type,$plugin ,$hash_key,'',$header_array,$value_array,$indexs,'','',$media_type,$line_number);
		if (!empty($attach_id)) {
			return $attach_id;
		}
	
		$post_info = array(
			'guid' => $uploaddir_url . "/" . $fimg_name,
			'post_mime_type' => $file_type,
			'post_title' => 'image-failed',
			'post_content' => '',
			'post_status' => 'inherit',
			'post_author' => $post_values['author'] ?? ''
		);
		$attach_id = wp_insert_attachment($post_info, $uploaddir_path, $post_id);
		if(!empty($media_handle)){
			$media_handle['media_settings']['title'] = 'image-failed';
			update_option('smack_image_options', $media_handle);
			$this->imageMetaImport($attach_id,$media_handle,$header_array,$value_array);
		}
		return $attach_id;
	}
	
	public function handle_inline_image($core_instance,$acf_csv_name, $post_id, $post_values, $plugin, $get_import_type, $acf_wpname_element, $hash_key, $header_array, $value_array, $line_number, $indexs, $uploaddir_url, $fimg_name, $file_type, $uploaddir_path, $shortcode_table, $post_title, $file_name) {
		global $wpdb;
		$failed_inline_ids = $wpdb->get_results("SELECT post_title, post_id, image_shortcode, media_id, original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE image_shortcode = 'inline_image_' AND post_id = '{$post_id}'");
	
		$attach_id = $this->media_handling($acf_csv_name, $post_id, $post_values,$get_import_type,$plugin,$hash_key,'',$header_array,$value_array,$indexs,'','','',$line_number);
		if (!empty($attach_id)) {
			return $attach_id;
		}
	
		if (empty($failed_inline_ids) || $failed_inline_ids[0]->original_image != $acf_csv_name) {
			$post_info = array(
				'guid' => $uploaddir_url . "/" . $fimg_name,
				'post_mime_type' => $file_type,
				'post_title' => 'image-failed',
				'post_content' => '',
				'post_status' => 'inherit',
				'post_author' => $post_values['author'] ?? ''
			);
			$attach_id = wp_insert_attachment($post_info, $uploaddir_path, $post_id);
		}
	
		if (empty($failed_inline_ids)) {
			$this->store_failed_image_ids($attach_id);
			$this->failed_media_data($line_number,$post_id,$post_title,$attach_id,$acf_csv_name);
		} 
		elseif (isset($failed_inline_ids[0]->post_id) && $failed_inline_ids[0]->post_id == $post_id) {
			$this->store_failed_image_ids($failed_inline_ids[0]->media_id);
			$this->failed_media_data($line_number,$failed_inline_ids[0]->post_id,$failed_inline_ids[0]->post_title,$failed_inline_ids[0]->media_id,$failed_inline_ids[0]->original_image);
		}
	
		return $attach_id;
	}
	
	public function handle_featured_image($core_instance,$acf_csv_name, $post_id, $post_values, $plugin, $get_import_type, $acf_wpname_element, $hash_key, $header_array, $value_array, $line_number, $uploaddir_url, $fimg_name, $file_type, $uploaddir_path, $shortcode_table, $post_title, $file_name, $failed_ids) {
		global $wpdb;
	
		if (empty($failed_ids)) {
			$attach_id = $this->media_handling($acf_csv_name, $post_id, $post_values,$get_import_type,$plugin,$hash_key,'',$header_array,$value_array,'','','','',$line_number);
			if (!empty($attach_id)) {
				return $attach_id;
			}
		}
	
		if (empty($attach_id) || !empty($failed_ids)) {
			$post_info = array(
				'guid' => $uploaddir_url . "/" . $fimg_name,
				'post_mime_type' => $file_type,
				'post_title' => 'image-failed',
				'post_content' => '',
				'post_status' => 'inherit',
				'post_author' => $post_values['author'] ?? ''
			);
			$attach_id = wp_insert_attachment($post_info, $uploaddir_path, $post_id);
	
			if (empty($failed_ids)) {
				$this->store_failed_image_ids($attach_id);
				$this->failed_media_data($line_number,$post_id,$post_title,$attach_id,$acf_csv_name);
			}
		}
	
		return $attach_id;
	}
	
	public function handle_custom_image($core_instance,$acf_csv_name, $post_id, $post_values, $plugin, $get_import_type, $acf_wpname_element, $hash_key, $header_array, $value_array, $line_number,$imgformat, $typecct, $indexs, $uploaddir_url, $fimg_name, $file_type, $uploaddir_path, $shortcode_table, $post_title, $file_name) {
		global $wpdb;
	
		$image_meta_value = array(
			'headerarray' => $header_array,
			'valuearray' => $value_array,
			'tablename' => $typecct,
			'returnformat' => $imgformat
		);
		$acf_image_meta = json_encode($image_meta_value);
	
		$failed_id = $wpdb->get_results("SELECT post_title, post_id, image_shortcode, media_id, original_image FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager WHERE post_id = '{$post_id}' AND original_image = '{$acf_csv_name}' AND image_shortcode = '" . esc_sql($plugin . '_image__' . $acf_wpname_element) . "'");
	
		$attach_id = $this->media_handling($acf_csv_name, $post_id, $post_values,$get_import_type,$plugin,$hash_key,'',$header_array,$value_array,$indexs,$acf_wpname_element,$acf_image_meta,'',$line_number);
	
		if (!empty($attach_id)) {
			return $attach_id;
		}
	
		if (empty($failed_id)) {
			$post_info = array(
				'guid' => $uploaddir_url . "/" . $fimg_name,
				'post_mime_type' => $file_type,
				'post_title' => 'image-failed',
				'post_content' => '',
				'post_status' => 'inherit',
				'post_author' => $post_values['author'] ?? ''
			);
			$attach_id = wp_insert_attachment($post_info, $uploaddir_path, $post_id);
	
			$this->store_failed_image_ids($attach_id);
			$this->failed_media_data($line_number,$post_id,$post_title,$attach_id,$acf_csv_name);
		} else {
			$media_id = $failed_id[0]->media_id;
			$attachment_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE ID = $media_id AND post_title = 'image-failed' AND post_type = 'attachment' AND guid LIKE '%$fimg_name%'", ARRAY_A);
			$attach_id = $attachment_id[0]['ID'];
		}
	
		return $attach_id;
	}
	
	public function store_image_ids($attach_id){
		//get number of images count
			$stored_ids = get_option('total_attachment_ids', '');
			$att_id = $attach_id;
			if ($stored_ids === '') {
				add_option('total_attachment_ids', serialize(array($att_id)));
				$stored_ids = unserialize(get_option('total_attachment_ids', ''));
			} else {
				$get_stored_ids = unserialize(get_option('total_attachment_ids', ''));
				if (is_array($get_stored_ids) && !empty($att_id)) {
					$att_id = is_array($att_id) ? $att_id : array($att_id);
					$stored_ids = array_merge($get_stored_ids,$att_id);
				} else {
					$stored_ids = $att_id;
				}
				update_option('total_attachment_ids', serialize($stored_ids));
				$stored_ids = unserialize(get_option('total_attachment_ids', ''));
			}
	}
	public function store_failed_data($data, $option_name){
		// Get stored data
		$stored_data = get_option($option_name, '');
		if ($stored_data === '') {
			add_option($option_name, serialize(array($data)));
			$stored_data = unserialize(get_option($option_name, ''));
		} else {
			// Unserialize stored data
			$stored_data = unserialize($stored_data);
			$data = is_array($data) ? $data : array($data);
			$stored_data = array_merge($stored_data, $data);
			update_option($option_name, serialize($stored_data));
			$stored_data = unserialize(get_option($option_name, ''));
		}
		
		return $stored_data;
	}

	public function store_failed_image_ids($attach_id) {
		$option_name = 'failed_attachment_ids';
		$stored_data = $this->store_failed_data($attach_id, $option_name);
		return $stored_data;
	}
	/** add the failed image data */
	public function failed_media_data($line_number, $post_id, $post_title, $media_id, $actual_url) {
		global $core_instance;
		$core_instance = CoreFieldsImport::getInstance();
		$option_name = 'failed_line_number';
		$stored_ids = $this->store_failed_data($media_id, $option_name);
		$count = count($stored_ids);
		$data = array('post_id' => $post_id, 'post_title' => $post_title, 'media_id' => $media_id, 'actual_url' => $actual_url);
		$core_instance->failed_media_data[$count] = $data;
	}

}
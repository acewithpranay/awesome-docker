<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class LogManager {

    private static $instance = null,$saveMappingInstance;
    private static $smack_csv_instance = null;
    // declare log file and file pointer as private properties
	private $log_file, $fp;
	public $logArr;
	public function __construct(){
		add_action('wp_ajax_display_log',array($this,'display_log'));		
		add_action('wp_ajax_download_log',array($this,'download_log'));
		add_action('wp_ajax_download_media_log',array($this,'download_media_log'));
		add_action('wp_ajax_download_failed_log',array($this,'download_failed_log'));		
		add_action('wp_ajax_delete_log',array($this,'delete_log'));	
    }

    public static function getInstance() {
		if (LogManager::$instance == null) {
			LogManager::$instance = new LogManager;
            LogManager::$smack_csv_instance = SmackCSV::getInstance();
			LogManager::$saveMappingInstance = SaveMapping::getInstance();
			return LogManager::$instance;
		}
		return LogManager::$instance;
    }


	/**
	 * Writes event log in log file.
	 * @param  string $hash_key - file hash key
     * @param  string $original_file_name - file name
	 * @param  string $fileType - file extension
	 * @param  string $mode - file mode (import or update)
	 * @param  int    $totalCount - Total number of records
	 * @param  string $importType - Post type
	 * @param  string $core_log - Event log
	 * @param  boolean $addHeader 
	 */
	public function get_event_log($hash_key , $original_file_name , $fileType , $mode , $totalCount , $importType , $core_log, $addHeader,$templatekey = null){
		$smack_instance = SmackCSV::getInstance();
		global $logArr;
		if (is_array($core_log)){
			$logArr = $core_log;
			$this->displayLogValue($logArr);	
		}
	}
	public function displayLogValue(){
		global $logArr;
		return $logArr;
	}


	/**
	 * Retrieves and display the file events history.
	 */
	public function display_log(){	
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		global $wpdb,$logArr;
		$response = [];
		$logInfo = [];
		$value = [];
		
		$logInformation = $wpdb->get_results("select * from {$wpdb->prefix}smackuci_events where deletelog = 0 order by id desc ");
		if(empty($logInformation)){
			$response['success'] = false;
			$response['message'] = "No logs Found";
		}else{
			foreach($logInformation as $logIndex => $logValue){

				$file_name = $logValue->original_file_name;
				$revision = $logValue->revision;
				$module = $logValue->import_type;
				$inserted = $logValue->created;
				$updated = $logValue->updated;
				$skipped = $logValue->skipped;
				$failed = $logValue->failed;
	
				$logInfo['filename'] = $file_name;
				$logInfo['revision'] = $revision;
				$logInfo['module'] = $module;
				$logInfo['inserted'] = $inserted ;
				$logInfo['updated'] = $updated;
				$logInfo['skipped'] = $skipped;
				$logInfo['failed'] = $failed;
	
				array_push($value , $logInfo);
			}
			$response['success'] = true;
			$response['info'] = $value;
			$response['importer_records']=$logArr;
		}	
		echo wp_json_encode($response);
		wp_die();
	}

	/**
	 * Delete the Logs
	 */
	public function delete_directory($directoryPath) {
		if (file_exists($directoryPath) && is_dir($directoryPath)) {
			$files = glob($directoryPath . '*');
			foreach ($files as $file) {
				if (is_file($file)) {
					unlink($file);
				}
			}
			if (!rmdir($directoryPath)) {
				return false;
			}
		}
		return true;
	}
	public function delete_log(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		global $wpdb;		
		$smack_instance = SmackCSV::getInstance();
		$filename = sanitize_text_field($_POST['filename']);
		$revision = sanitize_text_field($_POST['revision']);		
		$upload_path = $smack_instance->create_upload_dir();
		$get_details = $wpdb->get_results($wpdb->prepare("select id,eventKey from {$wpdb->prefix}smackuci_events where revision = %d and original_file_name = %s", $revision, $filename));		
		if (!empty($get_details)) {
			foreach ($get_details as $records) {
				$eventKey = $records->eventKey;	
				$directories = [
					$upload_path . 'import_logs/' . $eventKey . '/',
					$upload_path . 'failed_media_logs/' . $eventKey . '/',
					$upload_path . 'media_logs/' . $eventKey . '/',
				];
				
				foreach ($directories as $directoryPath) {
					if (!$this->delete_directory($directoryPath)) {
						$response['message'] = "File not available. Kindly refresh the page.";
						echo wp_json_encode($response);
						wp_die();
					}
				}
				
				$wpdb->update($wpdb->prefix . 'smackuci_events', ['deletelog' => true], ['id' => $records->id]);
			}	
			$response['message'] = "Deleted Successfully";
			echo wp_json_encode($response);
			wp_die();
		}
		else {
			$response['message'] = "Record not found";
		}
		
		echo wp_json_encode($response);
		wp_die();
	}  

	/**
	 * Downloads download_media_log .
	 */
	// public function download_media_log(){
	// 	check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
	// 	global $wpdb;		
	// 	$response = [];
    //     $filename = sanitize_file_name($_POST['filename']);
    //     $revision = sanitize_text_field($_POST['revision']);

    //     $upload = wp_upload_dir();
    //     $upload_dir = $upload['baseurl'];
    //     $upload_url = $upload_dir . '/smack_uci_uploads/imports/media_logs/';
        
    //     $upload_path = LogManager::$smack_csv_instance->create_upload_dir();
	// 	$get_event_key = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision , $filename));
	// 	if(empty($get_event_key)) {
	// 		$response['success'] = false;
    //         $response['message'] = 'Log not exists';
	// 	}
	// 	else {
	// 		$logPath = $upload_path.'media_logs'.'/'.$get_event_key[0]->eventKey .'/';
	// 		if (file_exists($logPath)) :
	// 			$loglink = $upload_url .$get_event_key[0]->eventKey .'/'.'media_log.csv';
	// 			$response['success'] = true;
	// 			$response['media_log_link'] = $loglink;
				
	// 		else :
	// 			$response['success'] = false;
	// 			$response['message'] = 'Log not exists';
				
	// 		endif;
	// 	}   
    //     echo wp_json_encode($response); 
    //     wp_die();  
	// }
	public function download_media_log(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
	
	global $wpdb;
	
	$response = [];
	$filename = sanitize_file_name($_POST['filename']);
	$revision = isset($_POST['revision']) ? sanitize_text_field($_POST['revision']) : '';
	$hash_key=isset($_POST['hashkey'] ) ? sanitize_text_field($_POST['hashkey']) : '';
		if(empty($hash_key)){
			$event_key_result = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision, $filename));
		$get_event_key = $event_key_result[0]->eventKey;
		
		}
		else{
			$get_event_key=$hash_key;
		}
	
	if (empty($get_event_key)) {
		$response['success'] = false;
		$response['message'] = 'Log not exists';
		echo wp_json_encode($response);
		wp_die();
	} else {

		$upload_dir = WP_CONTENT_DIR . '/uploads/smack_uci_uploads/media_log/' . $get_event_key . '/';
		
		if (!is_dir($upload_dir)) {
			if (!wp_mkdir_p($upload_dir)) {
				return null;
			}
		}
		chmod($upload_dir, 0777);
	
		$index_file = $upload_dir . 'index.php';
		if (!file_exists($index_file)) {
			$index_content = '<?php' . PHP_EOL . '?>';
			file_put_contents($index_file, $index_content);
			chmod($index_file, 0644);
		}

		$baseFileName = 'Media_log';
		$export_type = 'csv';
		$file_path = $upload_dir . $baseFileName . '.' . $export_type;
		$file_url = network_home_url() . '/wp-content/uploads/smack_uci_uploads/media_log/' . $get_event_key . '/' . $baseFileName . '.' . $export_type;

		

		if (file_exists($file_path)) {
			// If the file already exists, return the file URL
			$response['success'] = true;
			$response['file_url'] = $file_url;
			echo wp_json_encode($response);
			wp_die();
		
		} else {
			
			
			$results = $wpdb->get_results(
				$wpdb->prepare(
						"SELECT  media_id, title, actual_url,file_url,file_name,caption,alt_text,description,status 
					 FROM " . $wpdb->prefix . "failed_media 
					 WHERE event_id = %s",
					$get_event_key,
					
				)
			);			
			$json_posts = wp_json_encode($results);
			$posts_array = json_decode($json_posts, true);
			
			if (empty($posts_array)) {
				$response['success'] = false;
				$response['message'] = 'No posts found or failed to decode JSON.';
				echo wp_json_encode($response);
				wp_die();
			}

			$csv_file = fopen('php://temp', 'w');
			if (!empty($posts_array)) {
				fputcsv($csv_file, array_keys($posts_array[0]));
			}

			foreach ($posts_array as $post) {
				fputcsv($csv_file, $post);
			}
			rewind($csv_file);

			$csv_contents = stream_get_contents($csv_file);
			fclose($csv_file);

			// Save the CSV data to the file
			file_put_contents($file_path, $csv_contents);

			$response['success'] = true;
			$response['file_url'] = $file_url;
			echo wp_json_encode($response);
			wp_die();
		}
	}  
	}

	/**
	 * Downloads failed event log.
	 */
	// public function download_failed_log(){
	// 	check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
	// 	global $wpdb;		
	// 	$response = [];
    //     $filename = sanitize_file_name($_POST['filename']);
    //     $revision = sanitize_text_field($_POST['revision']);

    //     $upload = wp_upload_dir();
    //     $upload_dir = $upload['baseurl'];
    //     $upload_url = $upload_dir . '/smack_uci_uploads/imports/failed_media_logs/';
        
    //     $upload_path = LogManager::$smack_csv_instance->create_upload_dir();
	// 	$get_event_key = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision , $filename));
	// 	if(empty($get_event_key)) {
	// 		$response['success'] = false;
    //         $response['message'] = 'Log not exists';
	// 	}
	// 	else {
	// 		$logPath = $upload_path .'failed_media_logs'.'/'.$get_event_key[0]->eventKey .'/failed_media_log.csv';
	// 		if (file_exists($logPath)) :
	// 			$loglink = $upload_url .$get_event_key[0]->eventKey .'/'.'failed_media_log.csv';
	// 			$response['success'] = true;
	// 			$response['failed_log_link'] = $loglink;
				
	// 		else :
	// 			$response['success'] = false;
	// 			$response['message'] = 'Log not exists';
				
	// 		endif;
	// 	}   
    //     echo wp_json_encode($response); 
    //     wp_die();  
	// }
	

	/**
	 * Downloads file event log.
	 */
	// public function download_log(){
	// 	check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
	// 	global $wpdb;
       
    //     $response = [];
    //     $filename = sanitize_file_name($_POST['filename']);
    //     $revision = sanitize_text_field($_POST['revision']);

    //     $upload = wp_upload_dir();
    //     $upload_dir = $upload['baseurl'];
    //     $upload_url = $upload_dir . '/smack_uci_uploads/imports/import_logs/';
        
    //     $upload_path = LogManager::$smack_csv_instance->create_upload_dir();
	// 	$get_event_key = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision , $filename));
	// 	if(empty($get_event_key)) {
	// 		$response['success'] = false;
    //         $response['message'] = 'Log not exists';
	// 	}
	// 	else {
	// 		$logPath = $upload_path.'import_logs'.'/'.$get_event_key[0]->eventKey .'/';

	// 		if (file_exists($logPath)) :
	// 			$loglink = $upload_url .$get_event_key[0]->eventKey .'/'.'summary_log.csv';
	// 			$response['success'] = true;
	// 			$response['log_link'] = $loglink;
				
	// 		else :
	// 			$response['success'] = false;
	// 			$response['message'] = 'Log not exists';
				
	// 		endif;
	// 	}   
    //     echo wp_json_encode($response); 
    //     wp_die();
	// }

	public function download_log() {
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		
		global $wpdb;
		
		$response = [];
		$filename = sanitize_file_name($_POST['filename']);
		$revision = isset($_POST['revision']) ? sanitize_text_field($_POST['revision']) : '';
		$hash_key=isset($_POST['hashkey'] ) ? sanitize_text_field($_POST['hashkey']) : '';
		$module  = sanitize_text_field($_POST['type']);
		if(empty($hash_key)){
			$event_key_result = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision, $filename));
		$get_event_key = $event_key_result[0]->eventKey;
		
		}
		else{
			$get_event_key=$hash_key;
		}
		if (empty($get_event_key)  || $module == 'Media') {
			$response['success'] = false;
			$response['message'] = 'Log not exists';
			echo wp_json_encode($response);
			wp_die();
		} else {

			$upload_dir = WP_CONTENT_DIR . '/uploads/smack_uci_uploads/summary_logs/' . $get_event_key . '/';
		
			if (!is_dir($upload_dir)) {
				if (!wp_mkdir_p($upload_dir)) {
					return null;
				}
			}
			chmod($upload_dir, 0777);
		
			$index_file = $upload_dir . 'index.php';
			if (!file_exists($index_file)) {
				$index_content = '<?php' . PHP_EOL . '?>';
				file_put_contents($index_file, $index_content);
				chmod($index_file, 0644);
			}

			$baseFileName = 'summary';
			$export_type = 'csv';
			$file_path = $upload_dir . $baseFileName . '.' . $export_type;
			$file_url = network_home_url() . '/wp-content/uploads/smack_uci_uploads/summary_logs/' . $get_event_key . '/' . $baseFileName . '.' . $export_type;
	
			if (file_exists($file_path)) {
				// If the file already exists, return the file URL
				$response['success'] = true;
				$response['file_url'] = $file_url;
				echo wp_json_encode($response);
				wp_die();
			
			} else {
				$cat_check = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));
				foreach ($cat_check as $item) {
					
					switch (strtolower($item->is_category)) {
						case '1':
							$found_category = true;
							break;
						case '2':
							$found_tag = true;
							break;

							case '3':
								$found_users = true;
								break;
								case '4':
									$found_comment = true;
									break;
						
					}
					
				}
				// category log
				if (isset($found_category) && $found_category){
					global $wpdb;
				
						$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));
					
						if (!empty($cat_ids)) {
							
							$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));
					

							$query = "SELECT * FROM " . $wpdb->prefix . "terms WHERE term_id IN ($placeholders)";
							$results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));
							foreach ($results as $result) {
								$term_id = $result->term_id;
								
								// Admin link
								$admin_link = admin_url("term.php?taxonomy=category&tag_ID={$term_id}");
								
								// Weblink
								$site_url = get_site_url();
								$term_link = trailingslashit($site_url) . 'index.php/'.'category/' . $result->slug . '/';
							
								$result->admin_link = $admin_link;
								$result->weblink = $term_link;
							}
						
					} 
				}
				//tag log
				elseif(isset($found_tag) && $found_tag){
					global $wpdb;
				
						$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));
					
						if (!empty($cat_ids)) {
							
							$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));
					

							$query = "SELECT * FROM " . $wpdb->prefix . "terms WHERE term_id IN ($placeholders)";
							$results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));
							foreach ($results as $result) {
								$term_id = $result->term_id;
								
								// Admin link
								$admin_link = admin_url("term.php?taxonomy=post_tag&tag_ID={$term_id}");
								
								// Weblink
								$site_url = get_site_url();
								$term_link = trailingslashit($site_url) . 'index.php/'.'tag/' . $result->slug . '/';
							
								$result->admin_link = $admin_link;
								$result->weblink = $term_link;
							}
						
					} 
					
				}
				//users log
				elseif(isset($found_users) && $found_users){
					global $wpdb;
				
					$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));

					if (!empty($cat_ids)) {
						$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));
					
						// Query to get user information, including the ID
						$query = "SELECT ID, user_login, user_nicename,display_name, user_email, user_url FROM " . $wpdb->prefix . "users WHERE ID IN ($placeholders)";
						$results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));
					
					
				} 

				}
				//comment logs
				elseif(isset($found_comment) && $found_comment){
					global $wpdb;
				
					$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));

					if (!empty($cat_ids)) {
						$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));
					
						// Query to get user information, including the ID
						$query = "SELECT comment_post_id, comment_author, comment_author_url,comment_content, comment_type FROM " . $wpdb->prefix . "comments WHERE comment_ID IN ($placeholders)";
						$results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));
					

				}
			}

				else{
					global $wpdb;

		$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));

		if (!empty($cat_ids)) {
    	$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));

   		 $query = "SELECT ID, post_type, post_title, post_content, post_excerpt, post_status, post_name, guid FROM " . $wpdb->prefix . "posts WHERE ID IN ($placeholders)";
   		 $results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));

   		 $media_query = "SELECT post_id, associated_media, failed_media FROM " . $wpdb->prefix . "summary WHERE event_id = %s";
   		 $media_results = $wpdb->get_results($wpdb->prepare($media_query, $get_event_key), OBJECT_K);

    if ($media_results) {
        foreach ($results as $result) {
            if (isset($media_results[$result->ID])) {
                $result->associated_media = $media_results[$result->ID]->associated_media;
                $result->failed_media = $media_results[$result->ID]->failed_media;
            } else {
                $result->associated_media = null;
                $result->failed_media = null;
            }
        }
    } else {
        
        foreach ($results as $result) {
            $result->associated_media = null;
            $result->failed_media = null;
        }
    }
		} else {
 
    $results = [];
			}

			}
	
				
				$json_posts = wp_json_encode($results);
				$posts_array = json_decode($json_posts, true);
				
				if (empty($posts_array)) {
					$response['success'] = false;
					$response['message'] = 'No posts found or failed to decode JSON.';
					echo wp_json_encode($response);
					wp_die();
				}
	
				$csv_file = fopen('php://temp', 'w');
				if (!empty($posts_array)) {
					fputcsv($csv_file, array_keys($posts_array[0]));
				}
	
				foreach ($posts_array as $post) {
					fputcsv($csv_file, $post);
				}
				rewind($csv_file);
	
				$csv_contents = stream_get_contents($csv_file);
				fclose($csv_file);
	
				// Save the CSV data to the file
				file_put_contents($file_path, $csv_contents);
	
				$response['success'] = true;
				$response['file_url'] = $file_url;
				echo wp_json_encode($response);
				wp_die();
			}
		}
	}
	
//failed media log download
public function download_failed_log() {
	check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
	
	global $wpdb;
	
	$response = [];
	$filename = sanitize_file_name($_POST['filename']);
	$revision = isset($_POST['revision']) ? sanitize_text_field($_POST['revision']) : '';
	$hash_key=isset($_POST['hashkey'] ) ? sanitize_text_field($_POST['hashkey']) : '';
	$module  = sanitize_text_field($_POST['type']);
		if(empty($hash_key)){
			$event_key_result = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision, $filename));
		$get_event_key = $event_key_result[0]->eventKey;
		
		}
		else{
			$get_event_key=$hash_key;
		}
	
	if (empty($get_event_key)) {
		$response['success'] = false;
		$response['message'] = 'Log not exists';
		echo wp_json_encode($response);
		wp_die();
	} else {

		$upload_dir = WP_CONTENT_DIR . '/uploads/smack_uci_uploads/failed_media_logs/' . $get_event_key . '/';
		
		if (!is_dir($upload_dir)) {
			if (!wp_mkdir_p($upload_dir)) {
				return null;
			}
		}
		chmod($upload_dir, 0777);
	
		$index_file = $upload_dir . 'index.php';
		if (!file_exists($index_file)) {
			$index_content = '<?php' . PHP_EOL . '?>';
			file_put_contents($index_file, $index_content);
			chmod($index_file, 0644);
		}

		$baseFileName = 'FailedMedia';
		$export_type = 'csv';
		$file_path = $upload_dir . $baseFileName . '.' . $export_type;
		$file_url = network_home_url() . '/wp-content/uploads/smack_uci_uploads/failed_media_logs/' . $get_event_key . '/' . $baseFileName . '.' . $export_type;

		if (file_exists($file_path)) {
			// If the file already exists, return the file URL
			$response['success'] = true;
			$response['file_url'] = $file_url;
			echo wp_json_encode($response);
			wp_die();
		
		} else {
			
			if($module == 'Media'){
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT  media_id, title,file_name, caption,description,alt_text,actual_url,status ,file_url
						 FROM " . $wpdb->prefix . "failed_media 
						 WHERE event_id = %s AND status = %s",
						$get_event_key,
						'failed'
					)
				);	
			}
			else{
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT  media_id,title,post_id,actual_url,status
						 FROM " . $wpdb->prefix . "failed_media 
						 WHERE event_id = %s AND status = %s",
						$get_event_key,
						'failed'
					)
				);	
			}
					
			$json_posts = wp_json_encode($results);
			$posts_array = json_decode($json_posts, true);
			
			if (empty($posts_array)) {
				$response['success'] = false;
				$response['message'] = 'No posts found or failed to decode JSON.';
				echo wp_json_encode($response);
				wp_die();
			}

			$csv_file = fopen('php://temp', 'w');
			if (!empty($posts_array)) {
				fputcsv($csv_file, array_keys($posts_array[0]));
			}

			foreach ($posts_array as $post) {
				fputcsv($csv_file, $post);
			}
			rewind($csv_file);

			$csv_contents = stream_get_contents($csv_file);
			fclose($csv_file);


			// Save the CSV data to the file
			file_put_contents($file_path, $csv_contents);

			$response['success'] = true;
			$response['file_url'] = $file_url;
			echo wp_json_encode($response);
			wp_die();
		}
	}
}


	/**
	 * Saves event logs in database.
	 * @param  string $hash_key - File hash key
     * @param  string $selected_type - Post type
	 * @param  string $file_name - File name
	 * @param  string $total_rows - Total rows in file
	 */
    public function manage_records($hash_key ,$selected_type , $file_name , $total_rows){		
        global $wpdb;
        $log_table_name = $wpdb->prefix ."import_detail_log";

        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_extn = '.' . $file_extension;
        $get_local_filename = explode($file_extn, $file_name);
        $extension_object = new ExtensionHandler;
        $import_type = $extension_object->import_name_as($selected_type);

        $imported_on = date('Y-m-d h:i:s');
		$month = date("M", strtotime($imported_on));
        $year = date("Y", strtotime($imported_on));
        $file_path = '/smack_uci_uploads/imports/' . $hash_key . '/' . $hash_key;
        
        $get_name = $wpdb->get_results( "SELECT original_file_name FROM {$wpdb->prefix}smackuci_events " );

        if(!empty($get_name)){
			foreach($get_name as $name_values){
				$inserted_name_values[] = $name_values->original_file_name;
            }
            if(in_array($file_name , $inserted_name_values)){
                $get_revision = $wpdb->get_results( "SELECT revision FROM {$wpdb->prefix}smackuci_events WHERE original_file_name = '$file_name' " );
				foreach($get_revision as $value){
                    $last_version_id = $value->revision;
                }
                $revision = $last_version_id + 1;
                $name = $get_local_filename[0] .'-'. $revision . $file_extn;
            }    
			else{
                $name = $get_local_filename[0] . '-1' . $file_extn;
                $revision = 1;
            }
        }
        else{
            $name = $get_local_filename[0] . '-1' . $file_extn;
            $revision = 1;
        }

        $get_data =  $wpdb->get_results("SELECT skipped , created , updated,failed FROM $log_table_name WHERE hash_key = '$hash_key' ");
		$skipped_count = $get_data[0]->skipped;
			$created_count = $get_data[0]->created;
			$updated_count = $get_data[0]->updated;
			$failed_count = $get_data[0]->failed;			
			$processed = $created_count + $updated_count + $skipped_count;
			if($processed > $total_rows)	
				$processed = $created_count;

		$smack_uci_table = $wpdb->prefix."smackuci_events";

		$getid = $wpdb->get_results("SELECT distinct( id ) from {$wpdb->prefix}smackuci_events where import_type = '$import_type' and eventKey = '$hash_key'",ARRAY_A);		
		if(!empty($getid)){
			$wpdb->update($smack_uci_table, array(
				'created' => "{$created_count}",
				'updated' => "{$updated_count}",
				'skipped' => "{$skipped_count}",
				'failed' => "{$failed_count}",
				'processed' => "{$processed}",
				'last_activity' => "{$imported_on}",
				), 
				array('id' => $getid[0]['id'])
			);
		}
		else {
        $wpdb->insert($smack_uci_table, array(
            'revision' => $revision,
            'name' => "{$name}",
            'original_file_name' => "{$file_name}",
            'import_type' => "{$import_type}",
            'filetype' => "{$file_extension}",
            'filepath' => "{$file_path}",
            'eventKey' => "{$hash_key}",
            'registered_on' => $imported_on,
            'processing' => 1,
            'count' => $total_rows,
            'processed' => $created_count,
            'created' => $created_count,
            'updated' => $updated_count,
            'skipped' => $skipped_count,
			'failed' => $failed_count,
            'last_activity' => $imported_on,
            'month' => $month,
            'year' => $year
        ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s','%s','%s')
		);
	}
    }
	/**
	 * insert log file log.
	 */
	public function Insert_log_details($data, $line_number, $hash_key) {
		if (empty($data)) {
			return null; // Exit function if data is empty
		}
		if(!isset($data[$line_number]) || !is_array($data[$line_number])){
			$line_number = $line_number - 1;
			if(!isset($data[$line_number]) || !is_array($data[$line_number])){
				return null;
			}
		}

		$upload_dir = WP_CONTENT_DIR . '/uploads/smack_uci_uploads/imports/import_logs/' . $hash_key . '/';
		
		if (!is_dir($upload_dir)) {
			if (!wp_mkdir_p($upload_dir)) {
				return null;
			}
		}
		
		chmod($upload_dir, 0777);
		
		$index_file = $upload_dir . 'index.php';
		if (!file_exists($index_file)) {
			$index_content = '<?php' . PHP_EOL . '?>';
			file_put_contents($index_file, $index_content);
			chmod($index_file, 0644);
		}
		
		$baseFileName = 'summary_log';
		$export_type = 'csv';
		$filePath = $upload_dir . $baseFileName . '.' . $export_type;
		$fileURL = network_home_url() . '/wp-content/uploads/smack_uci_uploads/imports/import_logs/' . $hash_key . '/' . $baseFileName . '.' . $export_type;
		
		$headers = array_keys($data[$line_number]);
	
	
		// Read existing CSV file and store lines
		$lines = [];
		if (file_exists($filePath)) {
			$file = fopen($filePath, 'r');
			while (($line = fgetcsv($file)) !== FALSE) {
				$lines[] = $line;
			}
			fclose($file);
		} else {
			// If file does not exist, create header row
			$lines[] = $headers;
		}
	
		// Update the specific line numbers
		foreach ($data as $index => $row) {
			$new_line = [];
			foreach ($headers as $header) {
				$new_line[] = isset($row[$header]) ? $row[$header] : '';
			}
			$lines[$line_number + $index - 1] = $new_line;
		}
	
		// Write back the updated lines to the CSV file
		$file = fopen($filePath, 'w');
		if($file){
			foreach ($lines as $line) {
				fputcsv($file, $line);
			}
			fclose($file);
		}
		chmod($filePath, 0644);
		return isset($fileURL) ? $fileURL : null;
	}
	
	/**
	 * Starts the failed media download
	 */
	public function failedMediaExport($data, $line_number, $hash_key) {
		$baseFileName = 'failed_media_log';
		$export_type = 'csv';
		$upload_dir = WP_CONTENT_DIR . '/uploads/smack_uci_uploads/imports/failed_media_logs/' . $hash_key . '/';
		$file_path = $upload_dir . $baseFileName . '.' . $export_type;
		$file_url = network_home_url() . '/wp-content/uploads/smack_uci_uploads/imports/failed_media_logs/' . $hash_key . '/' . $baseFileName . '.' . $export_type;
		$headers = ['post_id', 'title', 'media_id', 'actual_url'];
	
		if (empty($data)) {
			if (!is_dir($upload_dir) && !wp_mkdir_p($upload_dir)) {
				return null;
			}
	
			if (!file_exists($file_path) || !is_readable($file_path)) {
				return null;
			}
	
			$file_handle = fopen($file_path, 'r');
			if ($file_handle === false) {
				return null;
			}
	
			$data_found = false;
			while (($row = fgetcsv($file_handle)) !== false) {
				if (!empty(array_filter($row))) {
					$data_found = true;
					break;
				}
			}
			fclose($file_handle);
	
			return $data_found ? $file_url : null;
		}
	
		if (!is_dir($upload_dir) && !wp_mkdir_p($upload_dir)) {
			return null;
		}
	
		chmod($upload_dir, 0777);
	
		$index_file = $upload_dir . 'index.php';
		if (!file_exists($index_file)) {
			file_put_contents($index_file, "<?php\n?>");
			chmod($index_file, 0644);
		}
	
		$lines = [];
		if (file_exists($file_path)) {
			$file = fopen($file_path, 'r');
			while (($line = fgetcsv($file)) !== false) {
				$lines[] = $line;
			}
			fclose($file);
		} else {
			$lines[] = $headers;
		}
	
		foreach ($data as $index => $row) {
			$new_line = [];
			foreach ($headers as $header) {
				$new_line[] = isset($row[$header]) ? $row[$header] : '';
			}
			$lines[$line_number + $index - 1] = $new_line;
		}
	
		$file = fopen($file_path, 'w');
		foreach ($lines as $line) {
			fputcsv($file, $line);
		}
		fclose($file);
	
		chmod($file_path, 0644);
	
		return $file_url;
	}
	
	/**
	 * Starts the media download
	 */
	public function mediaExport($data,$line_number,$hash_key){
		if (empty($data)) {
			return null; // Exit function if data is empty
		}
		if(!isset($data[$line_number]) || !is_array($data[$line_number])){
			$line_number = $line_number - 1;
			if(!isset($data[$line_number]) || !is_array($data[$line_number])){
				return null;
			}
		}
		$upload_dir = WP_CONTENT_DIR . '/uploads/smack_uci_uploads/imports/media_logs/' . $hash_key . '/';
		
		if (!is_dir($upload_dir)) {
			if (!wp_mkdir_p($upload_dir)) {
				return null;
			}
		}
		
		chmod($upload_dir, 0777);
		
		$index_file = $upload_dir . 'index.php';
		if (!file_exists($index_file)) {
			$index_content = '<?php' . PHP_EOL . '?>';
			file_put_contents($index_file, $index_content);
			chmod($index_file, 0644);
		}
		
		$baseFileName = 'media_log';
		$export_type = 'csv';
		$filePath = $upload_dir . $baseFileName . '.' . $export_type;
		$fileURL = network_home_url() . '/wp-content/uploads/smack_uci_uploads/imports/media_logs/' . $hash_key . '/' . $baseFileName . '.' . $export_type;
		$headers = array_keys($data[$line_number]);
	
		// Read existing CSV file and store lines
		$lines = [];
		if (file_exists($filePath)) {
			$file = fopen($filePath, 'r');
			while (($line = fgetcsv($file)) !== FALSE) {
				$lines[] = $line;
			}
			fclose($file);
		} else {
			// If file does not exist, create header row
			$lines[] = $headers;
		}
	
		// Update the specific line numbers
		foreach ($data as $index => $row) {
			$new_line = [];
			foreach ($headers as $header) {
				$new_line[] = isset($row[$header]) ? $row[$header] : '';
			}
			$lines[$line_number + $index - 1] = $new_line;
		}
	
		// Write back the updated lines to the CSV file
		$file = fopen($filePath, 'w');
		foreach ($lines as $line) {
			fputcsv($file, $line);
		}
		fclose($file);
	
		chmod($filePath, 0644);
		return isset($fileURL) ? $fileURL : null;
	}

}

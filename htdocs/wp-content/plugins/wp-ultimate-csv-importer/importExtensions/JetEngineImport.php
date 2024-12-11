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

class JetEngineImport {

	private static $instance = null;

	public static function getInstance() {		
		if (JetEngineImport::$instance == null) {
			JetEngineImport::$instance = new JetEngineImport;
		}
		return JetEngineImport::$instance;
	}

    function set_jet_engine_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key,$line_number){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		$this->jet_engine_import_function($post_values,$type, $post_id, $mode, $hash_key,$line_number,$header_array,$value_array);
	}

    public function jet_engine_import_function($data_array, $type, $pID ,$mode, $hash_key,$line_number,$header_array,$value_array) 
	{
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$media_instance = MediaHandling::getInstance();
		$jet_data = $this->JetEngineFields($type);
		foreach ($data_array as $dkey => $dvalue) {
			if(array_key_exists($dkey,$jet_data['JE'])){
				if($jet_data['JE'][$dkey]['type'] == 'advanced-date'){
					$meta_value = [];
					$date_test = trim($dvalue) ?? '';
			
					if (!empty($date_test)) {
						$date_parts = explode(',', $date_test);
			
						// Assign date parts to core fields
						$meta_value = [
							'date' => $date_parts[0] ?? '',
							'time' => $date_parts[1] ?? '',
							'is_end_date' => $date_parts[2] ?? '',
							'end_date' => $date_parts[3] ?? '',
							'end_time' => $date_parts[4] ?? '',
							'is_recurring' => $date_parts[5] ?? '',
							'recurring' => $date_parts[6] ?? '',
							'recurring_period' => $date_parts[7] ?? ''
						];
			
						// Add recurring details based on recurrence type
						$recurring = $meta_value['recurring'];
						switch ($recurring) {
							case 'daily':
								$meta_value['end'] = $date_parts[8] ?? '';
								$meta_value['end_after_date'] = $date_parts[9] ?? '';
								break;
							
							case 'weekly':
								$meta_value['week_days'] = explode('|', $date_parts[8] ?? '');
								$meta_value['end'] = $date_parts[9] ?? '';
								$meta_value['end_after_date'] = $date_parts[10] ?? '';
								break;
			
							case 'monthly':
								$meta_value['monthly_type'] = $date_parts[8] ?? '';
								$meta_value['month_day'] = $date_parts[9] ?? '';
								$meta_value['month_day_type'] = $date_parts[10] ?? '';
								$meta_value['month_day_type_value'] = $date_parts[11] ?? '';
								$meta_value['end'] = $date_parts[12] ?? '';
								$meta_value['end_after_date'] = $date_parts[13] ?? '';
								break;
			
							case 'yearly':
								$meta_value['monthly_type'] = $date_parts[8] ?? '';
								$meta_value['month'] = $date_parts[9] ?? '';
								$meta_value['month_day'] = $date_parts[10] ?? '';
								$meta_value['month_day_type'] = $date_parts[11] ?? '';
								$meta_value['month_day_type_value'] = $date_parts[12] ?? '';
								$meta_value['end'] = $date_parts[13] ?? '';
								$meta_value['end_after_date'] = $date_parts[14] ?? '';
								break;
						}
			
						//update meta
						$meta_key = sanitize_key($dkey . '__config');
						$meta_value_json = json_encode($meta_value);
						update_post_meta($pID, $meta_key, $meta_value_json);
					}
				}
				else if($jet_data['JE'][$dkey]['type'] == 'gallery' || $jet_data['JE'][$dkey]['type'] == 'media'){
					$gallery_ids = $media_ids = '';
					$exploded_gallery_items = explode( ',', $dvalue );
					
					$galleryvalue=array();
					$shortcode_table = $wpdb->prefix . "ultimate_csv_importer_shortcode_manager";
					$indexs = 0;				
					foreach ( $exploded_gallery_items as $gallery ) {
						$gallery = trim( $gallery );						
						if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
							$field_name = $jet_data['JE'][$dkey]['name'];
							$field_type = $jet_data['JE'][$dkey]['type'];
							$plugin = 'jetengine_'.$field_type;
							$imgformat = $jet_data['JE'][$dkey]['value_format'];
							$media_instance->store_image_ids($i=1);
							$get_gallery_id = $media_instance->image_meta_table_entry($line_number,$data_array, $pID, $field_name,$gallery, $hash_key, $plugin,'','','',$header_array, $value_array,$imgformat,'',$indexs);						
							if ( $get_gallery_id != '' ) {								
								if($jet_data['JE'][$dkey]['type'] == 'media'){
									$media_ids .= $get_gallery_id. ',';
								}
								elseif($jet_data['JE'][$dkey]['value_format'] == 'url'){
									global $wpdb;									
									$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");									
									$dir = wp_upload_dir();
									$gallery_ids .= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value. ',';
								}
								elseif($jet_data['JE'][$dkey]['value_format'] == 'both'){
									global $wpdb;
									$gallery_id1 ['id']= $get_gallery_id;
									
									$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
									$dir = wp_upload_dir();
									$gallery_id2['url']= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value;
									
									$galleryvalue[] = array_merge($gallery_id1,$gallery_id2);
									
									$gallery_ids=$galleryvalue;
								}
								else{
									$gallery_ids .= $get_gallery_id.',';
								}
							}
						} else {
							$galleryLen         = strlen( $gallery );
							$checkgalleryid     = intval( $gallery );
							$verifiedGalleryLen = strlen( $checkgalleryid );
							if ( $galleryLen == $verifiedGalleryLen ) {
								if($jet_data['JE'][$dkey]['type'] == 'media'){
									$media_ids .= $gallery. ',';
								}
								else{
									$gallery_ids .= $gallery. ',';
								}

							}
						}
						$indexs++;
					}					
					if(is_array($gallery_ids)){
						$gallery_id  = $gallery_ids;
					}
					if (!is_array($gallery_ids)) {
						$gallery_id = rtrim($gallery_ids,',');
					}
					
					if($jet_data['JE'][$dkey]['value_format'] == 'url'){
						global $wpdb;
						$media_id = $media_instance->media_handling( $dvalue, $pID);						
						$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
						$dir = wp_upload_dir();			
						//$media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;						
						if(!empty($get_media_fields[0]->meta_value)){
                            $media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
                        }        
                        else{
                            $media_id='';
                        }
					}
					elseif($jet_data['JE'][$dkey]['value_format'] == 'both'){
						global $wpdb;
						$media_id = $media_instance->media_handling( $dvalue, $pID);
						$media_ids1['id']=$media_id;
						$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
						$dir = wp_upload_dir();			
						if(!empty($get_media_fields[0]->meta_value)){
							$media_ids2['url'] = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
						}        
						else{
							$media_ids2['url']='';
						}
						$mediavalue= array_merge($media_ids1,$media_ids2);
						$media_id=array($mediavalue);
					}
					
					else{
						if(is_string($dvalue)) //Find image url or id
							$media_id = $media_instance->media_handling( $dvalue, $pID);
						else
							$media_id = $dvalue;
					}
					if($jet_data['JE'][$dkey]['type'] == 'media'){	
						$darray[$jet_data['JE'][$dkey]['name']] = $media_id;
					}
					else{
						$darray[$jet_data['JE'][$dkey]['name']] = $gallery_id;
					}
				}
				elseif($jet_data['JE'][$dkey]['type'] == 'datetime-local'){					
					$dateformat = 'Y-m-d\TH:m';
					if(!empty($dvalue)){
						$dt_var = trim($dvalue);						
						$datetime = str_replace('/', '-', "$dt_var");

						if($jet_data['JE'][$dkey]['is_timestamp']){
							if(is_numeric($datetime)){
								$date_time_of = $datetime;
							}
							else{
								$date_time_of = strtotime($datetime);
							}
							
						}else{														
							$date_time_of = $helpers_instance->validate_datefield($dt_var,$dkey,$dateformat,$line_number);						
						}
						$darray[$jet_data['JE'][$dkey]['name']] = $date_time_of;
					}
					else{
						$darray[$jet_data['JE'][$dkey]['name']] = '';
					}
				}
				elseif($jet_data['JE'][$dkey]['type'] == 'date'){					
					$dateformat = 'Y-m-d';
					if(!empty($dvalue)){
						$var = trim($dvalue);
						$date = str_replace('/', '-', "$var");
						
						if($jet_data['JE'][$dkey]['is_timestamp']){
							if(is_numeric($date)){
								$date_of = $date;
							}
							else{								
								$date_of = strtotime($date);
							}
						}else{							
							$date_of = $helpers_instance->validate_datefield($var,$dkey,$dateformat,$line_number);
						}
						$darray[$jet_data['JE'][$dkey]['name']] = $date_of;
					}
					else{
						$darray[$jet_data['JE'][$dkey]['name']] = '';
					}
				}
				elseif ($jet_data['JE'][$dkey]['type'] == 'time') {
					$var = trim($dvalue);
					$time = date('H:i', strtotime($var));
				
					if ($time == '00:00') {
						$darray[$jet_data['JE'][$dkey]['name']] = ''; // Set empty value
					} else {
						$darray[$jet_data['JE'][$dkey]['name']] = $time;
					}
				}
				elseif($jet_data['JE'][$dkey]['type'] == 'checkbox'){
					
					if($jet_data['JE'][$dkey]['is_array'] == 1){
						$arr = explode(',' , $dvalue);
						$darray[$jet_data['JE'][$dkey]['name']] = $arr;
					}
					else{
						$options = $jet_data['JE'][$dkey]['options'];
						$arr = [];
						$opt = [];
						$dvalexp = explode(',' , $dvalue);
						foreach($options as $option_key => $option_val){
							$arr[$option_val['key']] = 'false';
						}
						foreach($dvalexp as $dvalkey => $dvalueval){
							$dvalueval = trim($dvalueval);
							$keys = array_keys($arr);
							foreach($keys as $keys1){
								if($dvalueval == $keys1){
									$arr[$keys1] = 'true';
								}
							}

							//added new checkbox values
							if(!in_array($dvalueval, $keys)){
							
								//$get_meta_fields = $wpdb->get_results("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE slug = '$type' AND status IN ('publish','built-in')");
								$get_meta_fields = $wpdb->get_results( $wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name=%s",'jet_engine_meta_boxes'));

								if(isset($get_meta_fields[0])){
									$unserialized_meta = maybe_unserialize($get_meta_fields[0]->option_value);
					
									if(!empty($unserialized_meta)){
										foreach($unserialized_meta as $jet_keys => $jet_values){
											foreach($jet_values['meta_fields'] as $meta_keys => $meta_values){
												$count_jetvalues = 0;
												if($meta_values['type'] == 'checkbox' && $meta_values['name'] == $dkey){
													$count_jetvalues = count($meta_values['options']);
											
													$unserialized_meta[$jet_keys]['meta_fields'][$meta_keys]['options'][$count_jetvalues]['key'] = $dvalueval;
													$unserialized_meta[$jet_keys]['meta_fields'][$meta_keys]['options'][$count_jetvalues]['value'] = $dvalueval;
													$unserialized_meta[$jet_keys]['meta_fields'][$meta_keys]['options'][$count_jetvalues]['id'] = $meta_values['options'][$count_jetvalues - 1]['id'] + 1;	
												}
											}
										}
									
										update_option('jet_engine_meta_boxes', $unserialized_meta);
										$arr[$dvalueval] = 'true';
									}
								}		
							}
						}
						$darray[$jet_data['JE'][$dkey]['name']] = $arr;
					}
				}
				elseif($jet_data['JE'][$dkey]['type'] == 'select'){
					$dselect = [];
					if($jet_data['JE'][$dkey]['is_multiple'] == 0 || empty($jet_data['JE'][$dkey]['is_multiple'])){
						$darray[$jet_data['JE'][$dkey]['name']] = $dvalue;	
					}
					else{
						$exp = explode(',',$dvalue);
						foreach($exp as $exp_values){
							$dselect[] = trim($exp_values);
						}
						//$dselect = $exp;
						$darray[$jet_data['JE'][$dkey]['name']] = $dselect;
					}
				}
				elseif($jet_data['JE'][$dkey]['type'] == 'posts'){
					global $wpdb;
					if($jet_data['JE'][$dkey]['is_multiple'] == 0){
						$jet_posts = trim($dvalue);
						//$jet_posts = $wpdb->_real_escape($jet_posts);
						if(is_numeric($jet_posts)){
							$jet_posts_field_value = $jet_posts;
						}
						else{
							$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts}' AND post_status='publish'";
							$name = $wpdb->get_results($query);
							if (!empty($name)) {
								$jet_posts_field_value = $name[0]->id;
							}
						}
					}
					else{
						$jet_posts_exp = explode(',',trim($dvalue));
						$jet_posts_value = array();
						foreach($jet_posts_exp as $jet_posts_value){
							$jet_posts_value = trim($jet_posts_value);
							$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts_value}' AND post_status = 'publish' ORDER BY ID DESC";
							$multiple_id = $wpdb->get_results($query);
							$multiple_ids =$multiple_id[0];
							if(!$multiple_id){
								$jet_posts_field_value[]=$jet_posts_value;
							}
							else{
								$jet_posts_field_value[]=trim($multiple_ids->id);
							}
						}
					}
					$darray[$jet_data['JE'][$dkey]['name']] = $jet_posts_field_value;
				}
				else{
					if($jet_data['JE'][$dkey]['type'] != 'repeater'){
						$darray[$jet_data['JE'][$dkey]['name']] = $dvalue;
					}
				}
				$listTaxonomy = get_taxonomies();
				if($darray){
					if($type == 'Users'){
						foreach($darray as $mkey => $mval){
							update_user_meta($pID, $mkey, $mval);
						}
					}
					elseif(in_array($type, $listTaxonomy)){
						foreach($darray as $mkey => $mval){
							update_term_meta($pID, $mkey, $mval);
						}
					}
					else{
						foreach($darray as $mkey => $mval){
							update_post_meta($pID, $mkey, $mval);
						}
					}

				}
			}
		}
	}

    public function JetEngineFields($type){
		global $wpdb;	
		$jet_field = array();


		$get_meta_box_fields = $wpdb->get_results( $wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name=%s",'jet_engine_meta_boxes'));
		$unserialized_meta = maybe_unserialize($get_meta_box_fields[0]->option_value);
		$arraykeys = array_keys($unserialized_meta);

		foreach($arraykeys as $val){
			$values = explode('-',$val);
			$v = $values[1];
		}
		for($i=1 ; $i<=$v ; $i++){
			$meta['meta_fields'] = isset($unserialized_meta['meta-'.$i]['meta_fields']) ? $unserialized_meta['meta-'.$i]['meta_fields'] : '';
			$fields = $meta['meta_fields'];
			if(!empty($fields)){
				foreach($fields as $jet_key => $jet_value){
					$customFields["JE"][ $jet_value['name']]['label'] = $jet_value['title'];
					$customFields["JE"][ $jet_value['name']]['name']  = $jet_value['name'];
					$customFields["JE"][ $jet_value['name']]['type']  = $jet_value['type'];
					$customFields["JE"][ $jet_value['name']]['options'] = isset($jet_value['options']) ? $jet_value['options'] : '';
					$customFields["JE"][ $jet_value['name']]['is_multiple'] = isset($jet_value['is_multiple']) ? $jet_value['is_multiple'] : ' ' ;
					$customFields["JE"][ $jet_value['name']]['value_format'] = isset($jet_value['value_format']) ? $jet_value['value_format'] : '';
					$customFields["JE"][ $jet_value['name']]['is_array'] = isset($jet_value['is_array']) ? $jet_value['is_array'] : '';
				
					if($jet_value['type'] == 'date' || $jet_value['type'] == 'datetime-local'){
						$customFields["JE"][ $jet_value['name']]['is_timestamp'] = isset($jet_value['is_timestamp']) ? $jet_value['is_timestamp'] : '';
					}
				}
			}

		}	
		return $customFields;
	}
}
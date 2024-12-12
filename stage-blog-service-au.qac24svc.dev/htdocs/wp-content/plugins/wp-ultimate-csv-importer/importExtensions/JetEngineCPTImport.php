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

class JetEngineCPTImport {

	private static $instance = null;

	public static function getInstance() {		
		if (JetEngineCPTImport::$instance == null) {
			JetEngineCPTImport::$instance = new JetEngineCPTImport;
		}
		return JetEngineCPTImport::$instance;
	}

	function set_jet_engine_cpt_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key,$line_number){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		
		$extension_object = new ExtensionHandler;
		$import_as = $extension_object->import_post_types($type );
	
		$this->jet_engine_cpt_import_function($post_values,$import_as, $post_id, $mode, $hash_key,$line_number,$header_array,$value_array);
	}

    public function jet_engine_cpt_import_function($data_array, $type, $pID ,$mode, $hash_key,$line_number,$header_array,$value_array) 
	{
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$media_instance = MediaHandling::getInstance();
		if($type == 'WooCommerce Product'){
			$type = 'product';
		}
		$jet_data = $this->JetEngineCPTFields($type);
		foreach ($data_array as $dkey => $dvalue) {
			if(array_key_exists($dkey,$jet_data['JECPT'])){
				if ($jet_data['JECPT'][$dkey]['type'] === 'advanced-date') {
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

				if($jet_data['JECPT'][$dkey]['type'] == 'datetime-local'){
					$dateformat = 'Y-m-d\TH:m';
					if(!empty($dvalue)){
						$dt_var = trim($dvalue);
						$datetime = str_replace('/', '-', "$dt_var");

						if($jet_data['JECPT'][$dkey]['is_timestamp']){
							if(is_numeric($datetime)){
								$date_time_of = $datetime;
							}
							else{
								$date_time_of = strtotime($datetime);
							}
							
						}else{
							$date_time_of = $helpers_instance->validate_datefield($dt_var,$dkey,$dateformat,$line_number);
						}

						$darray[$jet_data['JECPT'][$dkey]['name']] = $date_time_of;
					}
					else{
						$darray[$jet_data['JECPT'][$dkey]['name']] = '';
					}
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'date'){
					$dateformat = 'Y-m-d';
					if(!empty($dvalue)){
						$var = trim($dvalue);
						$date = str_replace('/', '-', "$var");
						if($jet_data['JECPT'][$dkey]['is_timestamp']){
							if(is_numeric($date)){
								$date_of = $date;
							}
							else{
								$date_of = strtotime($date);
							}
							
						}else{
							$date_of = $helpers_instance->validate_datefield($var,$dkey,$dateformat,$line_number);
						}
						$darray[$jet_data['JECPT'][$dkey]['name']] = $date_of;
					}
					else{
						$darray[$jet_data['JECPT'][$dkey]['name']] = '';
					}
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'time'){
					$var = trim($dvalue);
					$time = date('H:i', strtotime($var));
					$darray[$jet_data['JECPT'][$dkey]['name']] = $time;
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'checkbox'){
					$dvalue = trim($dvalue);
					if($jet_data['JECPT'][$dkey]['is_array'] == 1){
						$dvalexp = explode(',' , $dvalue);
						$darray[$jet_data['JECPT'][$dkey]['name']] = $dvalexp;

					}
					else{
						$options = $jet_data['JECPT'][$dkey]['options'];
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
								$get_meta_fields = $wpdb->get_results("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE slug = '$type' AND status IN ('publish','built-in')");
								if(isset($get_meta_fields[0])){
									$unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
									$jet_engine_id = $get_meta_fields[0]->id;
								
									if(!empty($unserialized_meta)){
										foreach($unserialized_meta as $jet_keys => $jet_values){
											$count_jetvalues = 0;
											if($jet_values['type'] == 'checkbox' && $jet_values['name'] == $dkey){
												
												$count_jetvalues = count($jet_values['options']);
											
												$unserialized_meta[$jet_keys]['options'][$count_jetvalues]['key'] = $dvalueval;
												$unserialized_meta[$jet_keys]['options'][$count_jetvalues]['value'] = $dvalueval;
												$unserialized_meta[$jet_keys]['options'][$count_jetvalues]['id'] = $jet_values['options'][$count_jetvalues - 1]['id'] + 1;
												
											}
										}
									
										$serialized_meta = serialize($unserialized_meta);
										$wpdb->update( $wpdb->prefix . 'jet_post_types' , 
											array( 
												'meta_fields' => $serialized_meta,
											) , 
											array( 
												'id' => $jet_engine_id
											) 
										);

										$arr[$dvalueval] = 'true';
									}
								}		
							}
						}
						$darray[$jet_data['JECPT'][$dkey]['name']] = $arr;
					}
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'select'){
					$dselect = [];
					if($jet_data['JECPT'][$dkey]['is_multiple'] == 0){
						$darray[$jet_data['JECPT'][$dkey]['name']] = $dvalue;	
					}
					else{
						$exp = explode(',',$dvalue);
						foreach($exp as $exp_values){
							$dselect[] = trim($exp_values);
						}
						// $dselect = $exp;
						$darray[$jet_data['JECPT'][$dkey]['name']] = $dselect;
					}
				}
				else{
					if($jet_data['JECPT'][$dkey]['type'] != 'repeater'){
						$darray[$jet_data['JECPT'][$dkey]['name']] = $dvalue;
					}
				}
			}
		}

		if($darray){

			foreach($darray as $mkey => $mval){
				update_post_meta($pID, $mkey, $mval);
			}
		}
	}

    public function JetEngineCPTFields($type){
		global $wpdb;	
		$jet_field = array();
		$get_meta_fields = $wpdb->get_results("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE slug = '$type' AND status IN ('publish','built-in')");
		if(isset($get_meta_fields[0])){
			$unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
		}
		else{
			$unserialized_meta = '';
		}

		$customFields = [];
		if(is_array($unserialized_meta)){
			foreach($unserialized_meta as $jet_key => $jet_value){
				$customFields["JECPT"][ $jet_value['name']]['label'] = $jet_value['title'];
				$customFields["JECPT"][ $jet_value['name']]['name']  = $jet_value['name'];
				$customFields["JECPT"][ $jet_value['name']]['type']  = $jet_value['type'];
				$customFields["JECPT"][ $jet_value['name']]['options'] = isset($jet_value['options']) ? $jet_value['options'] : '';
				$customFields["JECPT"][ $jet_value['name']]['is_multiple'] = isset($jet_value['is_multiple']) ? $jet_value['is_multiple'] : '';
				$customFields["JECPT"][ $jet_value['name']]['is_array'] = isset($jet_value['is_array']) ? $jet_value['is_array'] : '';
				$customFields["JECPT"][ $jet_value['name']]['value_format'] = isset($jet_value['value_format']) ? $jet_value['value_format'] : '';
				
				if($jet_value['type'] == 'date' || $jet_value['type'] == 'datetime-local'){
					$customFields["JECPT"][ $jet_value['name']]['is_timestamp'] = isset($jet_value['is_timestamp']) ? $jet_value['is_timestamp'] : '';
				}
				$jet_field[] = $jet_value['name'];
			}
		}

		return $customFields;	
	}
	
}
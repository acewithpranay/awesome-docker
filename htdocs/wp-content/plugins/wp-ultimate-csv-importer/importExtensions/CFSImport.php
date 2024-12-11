<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class CFSImport {
    private static $cfs_instance = null;

    public static function getInstance() {
		
		if (CFSImport::$cfs_instance == null) {
			CFSImport::$cfs_instance = new CFSImport;
			return CFSImport::$cfs_instance;
		}
		return CFSImport::$cfs_instance;
    }
    function set_cfs_values($line_number,$header_array ,$value_array , $map, $post_id , $type,$hash_key){
		
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();	
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		
		$this->cfs_import_function($line_number,$post_values, $post_id,$hash_key);

    }

    public function cfs_import_function ($line_number,$data_array, $pID,$hash_key) {
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$media_instance = MediaHandling::getInstance();
		$cfs_data = $this->CFSFields();
		foreach ($data_array as $dkey => $dvalue) {
			if(array_key_exists($dkey,$cfs_data['CFS'])){
				if($cfs_data['CFS'][$dkey]['type'] == 'hyperlink'){
					$linksfields = explode('|', $dvalue);
					$linksarr['url'] = $linksfields[0];
					$linksarr['text'] = $linksfields[1];
					$linksarr['target'] = $linksfields[2];
					$darray[$cfs_data['CFS'][$dkey]['name']] = $linksarr;
				}
				elseif($cfs_data['CFS'][$dkey]['type'] == 'file'){
					$file_type = $this->get_remote_file_mime_type($dvalue);
					if (strpos($file_type, 'image/') === 0) {
						$media_instance->store_image_ids($i=1);
						$darray[$cfs_data['CFS'][$dkey]['name']] = $media_instance->image_meta_table_entry($line_number, '', $pID, $cfs_data['CFS'][$dkey]['name'], $dvalue, $hash_key, 'cfs', '', '');
					} else{
						$darray[$cfs_data['CFS'][$dkey]['name']] = $media_instance->media_handling($dvalue, $pID);
					}
				}elseif($cfs_data['CFS'][$dkey]['type'] == 'select'){
					if( strpos($dvalue, ',') !== false )
					{
						$multifields = explode(',', $dvalue);
						foreach($multifields as $mk => $mv){
							$meta_id = add_post_meta($pID, $cfs_data['CFS'][$dkey]['name'], $mv);
							$this->insert_cfs_values($cfs_data,$pID,$meta_id,$cfs_data['CFS'][$dkey]['name']);
						}
					}else{
						$darray[$cfs_data['CFS'][$dkey]['name']] = $dvalue;
					}
				}elseif($cfs_data['CFS'][$dkey]['type'] == 'relationship' && $cfs_data['CFS'][$dkey]['parent_id'] == 0){
					$relations = explode(',', $dvalue);
					foreach($relations as $rk => $rv){
						$relationid = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_title = %s and post_type != %s",$rv,'revision'));
						$meta_id = add_post_meta($pID, $cfs_data['CFS'][$dkey]['name'], $relationid[0]);
						$this->insert_cfs_values($cfs_data,$pID,$meta_id,$cfs_data['CFS'][$dkey]['name']);
					}
				}
				elseif($cfs_data['CFS'][$dkey]['type'] == 'term'){
					$relationterms = explode(',', $dvalue);
					foreach($relationterms as $rtk => $rtv){
						$term = get_term_by('name',$rtv,'category');
						$termid = $term->term_id;
						$meta_id = add_post_meta($pID, $cfs_data['CFS'][$dkey]['name'], $termid);
						$this->insert_cfs_values($cfs_data,$pID,$meta_id,$cfs_data['CFS'][$dkey]['name']);
					}
				}elseif($cfs_data['CFS'][$dkey]['type'] == 'user'){
					$users = explode(',', $dvalue);
					foreach($users as $uk => $uv){
						$userdata = $helpers_instance->get_from_user_details($uv);
						$meta_id = add_post_meta($pID, $cfs_data['CFS'][$dkey]['name'], $userdata['user_id']);
						$this->insert_cfs_values($cfs_data,$pID,$meta_id,$cfs_data['CFS'][$dkey]['name']);
					}
				}
				else if($cfs_data['CFS'][$dkey]['type'] != 'loop' && $cfs_data['CFS'][$dkey]['parent_id'] == 0){
					$darray[$dkey] = $dvalue;
				}
				
			
				global $wpdb;
				$csfFields = $cfs_data['CFS'];
				foreach($csfFields  as $values){
					if($values['type'] == 'loop'){
						$parentLoopId = $values['fieldid'];
						$parentLoopName = $values['name'];
					}
					if(!empty($parentLoopId)){
						if($parentLoopId == $cfs_data['CFS'][$dkey]['parent_id'] && $values['parent_id'] != 0){
							$childKeyName = $values['name'];
							$childFieldname = $values['type'];
							$childParentId = $values['parent_id'];



							$childFieldId = $values['fieldid'];
							if(!empty($childKeyName)){
							   if($cfs_data['CFS'][$dkey]['type'] == $childFieldname){
								   if($childFieldname =='relationship'){
									   $dataArray = explode('|', $dvalue);
									   $increment = 0;
									   foreach ($dataArray as $relationshipData) {
										   $relatedSingle = explode(',', $relationshipData);
										   foreach ($relatedSingle as $relate) {
											   $meta_id = add_post_meta($pID, $childKeyName, $relate);
											   $hierarchy = $parentLoopId . ':' . $increment . ':' . $childFieldId;
											   
											   $this->insert_cfs_loop_values($childFieldId, $meta_id, $pID, $parentLoopId, $hierarchy);
										   }
										   $increment++; // Increment after each set of $relationshipData
									   }
								   }else if($childFieldname =='textarea'){
									   $dataArray = explode('|', $dvalue);
									   $increment = 0;
									   foreach($dataArray as $metaValue => $meta){
										   $meta_id = add_post_meta($pID, $childKeyName, $meta);
										   $hierarchy = $parentLoopId.':'.$increment.':'.$childFieldId;
										   $increment++;
										   $this->insert_cfs_loop_values($childFieldId, $meta_id, $pID, $parentLoopId, $hierarchy);
									   }
								   }
								   else if($childFieldname =='hyperlink'){
									   $dataArray = explode(',', $dvalue);
									   $increment = 0;
									   foreach($dataArray as $metaValue => $meta){
										   $urlArray = explode('|', $meta);
										   $linksarr['url'] = $urlArray[0];
										   $linksarr['text'] = $urlArray[1];
										   $linksarr['target'] = $urlArray[2];
										   $meta_id = add_post_meta($pID, $childKeyName, $linksarr);
										   $hierarchy = $parentLoopId.':'.$increment.':'.$childFieldId;
										   $increment++;
										   $this->insert_cfs_loop_values($childFieldId, $meta_id, $pID, $parentLoopId, $hierarchy);
									   }
								   }else if($childFieldname =='date'){
									   $dataArray = explode('|', $dvalue);
									   $increment = 0;
									   foreach($dataArray as $metaValue => $meta){
									   $convertedDate = date('Y-m-d', strtotime($meta));
									   $meta_id = add_post_meta($pID, $childKeyName, $convertedDate);
									   $hierarchy = $parentLoopId.':'.$increment.':'.$childFieldId;
									   $increment++;
									   $this->insert_cfs_loop_values($childFieldId, $meta_id, $pID, $parentLoopId, $hierarchy);
									   }
								   }else if($childFieldname =='color' || $childFieldname =='true_false' || $childFieldname =='select' || $childFieldname =='term' || $childFieldname =='user'){
									   $dataArray = explode('|', $dvalue);
									   $increment = 0;
									   foreach($dataArray as $metaValue => $meta){

										   $meta_id = add_post_meta($pID, $childKeyName, $meta);
										   $hierarchy = $parentLoopId.':'.$increment.':'.$childFieldId;
										   $increment++;
										   $this->insert_cfs_loop_values($childFieldId, $meta_id, $pID, $parentLoopId, $hierarchy);
									   }
								   }else if ($childFieldname == 'text'){
									   $dataArray = explode('|', $dvalue);
									   $increment = 0;
									   foreach($dataArray as $metaValue => $meta){
										   $meta_id = add_post_meta($pID, $childKeyName, $meta);
										   $hierarchy = $parentLoopId.':'.$increment.':'.$childFieldId;
										   $increment++;
										   $this->insert_cfs_loop_values($childFieldId, $meta_id, $pID, $parentLoopId, $hierarchy);
									   }
								   }
							   }
						   }
					   }
					}
			
				}
			}
		}
		
		if($darray){
			foreach($darray as $mkey => $mval){
				$metaid = update_post_meta($pID, $mkey, $mval);
				$this->insert_cfs_values($cfs_data,$pID,$metaid,$mkey);

				}
		}

	}
	public function get_remote_file_mime_type($url) {
		$headers = get_headers($url, 1);
		if ($headers !== false && isset($headers['Content-Type'])) {
			if (is_array($headers['Content-Type'])) {
				return $headers['Content-Type'][0]; // Sometimes headers might return an array
			}
			return $headers['Content-Type'];
		}
		return '';
	}
	public function insert_cfs_loop_values($childFieldId, $meta_id, $pID, $parentLoopId, $hierarchy){
		global $wpdb;
		$wpdb->insert($wpdb->prefix.'cfs_values',
			array('field_id' => $childFieldId,
				  'meta_id' => $meta_id,
				  'post_id' => $pID,
				  'hierarchy' => $hierarchy,
				  'base_field_id' => $parentLoopId
			),   
			array('%s','%s','%s')
		);
	}

	public function insert_cfs_values($cfs_data,$pID,$metaid,$mkey) {
		global $wpdb;
		$wpdb->insert($wpdb->prefix.'cfs_values',
			array('field_id' => $cfs_data['CFS'][$mkey]['fieldid'],
			      'meta_id' => $metaid,
			      'post_id' => $pID,
			),
			array('%s','%s','%s')
		);
	}

	public function CFSFields(){
		global $wpdb;
		$customFields = $cfs_field = array();
		$get_cfs_groups = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_type = %s and post_status = %s", 'cfs', 'publish'),ARRAY_A);	
		$group_id_arr = '';
		foreach ( $get_cfs_groups as $item => $group_rules ) {
			$get_id[] = $group_rules['ID'];			
		}
		$group_id_arr = !empty($get_id) ? implode(',',$get_id) : "";
		if($group_id_arr != '') {			
			// Get available CFS fields based on the import type and group id
			$get_cfs_fields = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id IN ($group_id_arr) and meta_key ='cfs_fields'", ARRAY_A);
		}
		// Available CFS fields
		if (!empty($get_cfs_fields)) {
			foreach ($get_cfs_fields as $key => $value) {
				$get_cfs_field = @unserialize($value['meta_value']);
				foreach($get_cfs_field as $fk => $fv){
					$customFields["CFS"][$fv['name']]['label'] = $fv['label'];
					$customFields["CFS"][$fv['name']]['name'] = $fv['name'];
					$customFields["CFS"][$fv['name']]['type'] = $fv['type'];
					$customFields["CFS"][$fv['name']]['fieldid'] = $fv['id'];
					$customFields["CFS"][$fv['name']]['parent_id'] = $fv['parent_id'];
					$cfs_field[] = $fv['name'];
				}
			}
		}
		return $customFields;
	}

}
<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class PolylangImport {
	private static $polylang_instance = null;

	public static function getInstance() {

		if (PolylangImport::$polylang_instance == null) {
			PolylangImport::$polylang_instance = new PolylangImport;
			return PolylangImport::$polylang_instance;
		}
		return PolylangImport::$polylang_instance;
	}
	function set_polylang_values($header_array ,$value_array , $map, $post_id , $type){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();	
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		
		$this->polylang_import_function($post_values,$type, $post_id);
			
	}

	function polylang_import_function($data_array, $importas,$pId) {
		global $wpdb;
		$term_id = $checkid = "";
		$code = trim($data_array['language_code']);				 
		$language = $wpdb->get_results("select term_id,description from {$wpdb->prefix}term_taxonomy where taxonomy ='language'");				
		$language_id = $wpdb->get_results($wpdb->prepare("select term_taxonomy_id from {$wpdb->prefix}term_relationships WHERE object_id = %d",$pId));
		$listTaxonomy = get_taxonomies();
		$lang_list = pll_languages_list();
		if (in_array($importas, $listTaxonomy)) {
			if(empty($code) || !in_array($code,$lang_list)){
				$code=pll_default_language();
			}
			pll_set_term_language($pId, $code);
			$arr = pll_get_term_translations($pId);
			$translated_titles = explode(',',$data_array['translated_taxonomy_title']);
			foreach($translated_titles as $translated_title){
				$translated_post_id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name ='$translated_title'");
				$get_language = pll_get_term_language($translated_post_id);
				$arr[$get_language] =$translated_post_id;
				pll_save_term_translations( $arr );
			}
		}
		else{
			if(empty($code) || !in_array($code,$lang_list)){
				$code=pll_default_language();
			}
			pll_set_post_language($pId, $code);
			$arr = pll_get_post_translations($pId);
			$translated_titles = explode(',',$data_array['translated_post_title']);
			if($importas == 'WooCommerce Product Variations'){
				$translated_titles = explode('|',$data_array['translated_post_title']);
			}
			else{
				$translated_titles = explode(',',$data_array['translated_post_title']);
			}
			foreach($translated_titles as $translated_title){
				$translated_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$translated_title' and post_status='publish'");
				if(!empty($translated_post_id)){
					$get_language = pll_get_post_language($translated_post_id);
					$arr[$get_language] =$translated_post_id;
					pll_save_post_translations( $arr );
				}

			}
		}	 
		// 	 foreach($language_id as $key=>$lang_ids){				
		// 		$taxonomy = $wpdb->get_results($wpdb->prepare("select taxonomy from {$wpdb->prefix}term_taxonomy where term_taxonomy_id = %d",$lang_ids->term_taxonomy_id));
		// 		if(!empty($taxonomy)) {
		// 			$language_name=$taxonomy[0];
		// 			$lang_name=$language_name->taxonomy;				
		// 		}
		// 		else {
		// 			$lang_name = "";
		// 		}
		// 		if($lang_name == 'language'){										
		// 			$wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}term_relationships WHERE object_id = %d and term_taxonomy_id = %d ",$pId,$lang_ids->term_taxonomy_id));
		// 		}
		// 	 }
			
		// 	 foreach($language as $langkey => $langval){
		// 		 $description=unserialize($langval->description);
		// 		 $descript=explode('_',$description['locale']);
		// 		 $languages=$descript[0];
		// 		 if($languages == $code){
		// 			 $term_id=$langval->term_id;
		// 		 }
		// 	 }

		// 	 if(!empty($term_id)) {
		// 	 $wpdb->insert($wpdb->prefix.'term_relationships',array(
		// 	 'term_taxonomy_id'          => $term_id,
		// 	 'object_id'       => $pId
		// 	   ),
		// 	   array(
		// 	 '%s',
		// 	 '%s'
		// 	   ) 
		// 	 );
		// 	}			
		// 	 //$get_term=$wpdb->get_results($wpdb->prepare("select term_id from {$wpdb->prefix}terms where slug like %s ",'%-'.$code));			 			 
		// 	 $get_term=$wpdb->get_results($wpdb->prepare("select term_id from {$wpdb->prefix}terms where slug like %s ",$code));			 			 
		// 	 foreach($get_term as $keys =>$values){
		// 			 $id = $values->term_id;
		// 		 $wpdb->insert($wpdb->prefix.'term_relationships',array(
		// 			 'term_taxonomy_id'          => $id,
		// 			 'object_id'       => $pId
		// 		   ),
		// 		   array(
		// 			 '%s',
		// 			 '%s'
		// 		   ) 
		// 		 );
		// 	 }						 
		// if($data_array['language_code']){
			
		// 	$translatepost=isset($data_array['translated_post_title']) ? $data_array['translated_post_title'] : "";			
		// 	$child=$wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_title = %s and post_status != %s order by ID desc",$translatepost,'trash'));						
        //         $result_of_check = $wpdb->get_results("select description,term_id from {$wpdb->prefix}term_taxonomy where taxonomy='post_translations' ");
		// 		$array=json_decode(json_encode($result_of_check),true);
		// 		$trans_post_id = !empty($child) ? $child[0]->ID : "";

		// 		$languageid = $wpdb->get_results($wpdb->prepare("select term_id from {$wpdb->prefix}terms where slug= %s",$code));				

		// 		$lang_id = !empty($languageid) ? $languageid[0]->term_id : "";	
		// 		if(!empty($lang_id))	{							
		// 			$langcount = $wpdb->get_results($wpdb->prepare("select count from {$wpdb->prefix}term_taxonomy where term_id= %d",$lang_id));
		// 			$termcount=$langcount[0]->count;
		// 			$termcount = $termcount + 1;
		// 			$wpdb->update( $wpdb->term_taxonomy , array( 'count' => $termcount  ) , array( 'term_id' => $lang_id ) );
		// 		}

		// 		foreach($array as $res_key => $res_val){				   
		// 		   $get_term_id = $array[$res_key]['term_id'];
		// 		   $description = unserialize($array[$res_key]['description']);
		// 		   $values = is_array($description)? array_values($description): array(); 
		// 		   if(is_array($values)){  
		// 		   if (!empty($trans_post_id) && in_array($trans_post_id,$values)) {
		// 			   $checkid = $get_term_id;
		// 		   	}
		// 			}
		// 		}  
		// 		if($checkid){
		// 			$language=$wpdb->get_results("select term_id,description from {$wpdb->prefix}term_taxonomy where taxonomy ='language'");
		// 			$wpdb->insert($wpdb->prefix.'term_relationships',array(
		// 				'term_taxonomy_id'          => $checkid,
		// 				'object_id'       => $pId
		// 			  ),
		// 			  array(
		// 				'%s',
		// 				'%s'
		// 			  ) 
		// 			); 				 					
					
		// 			$result=$wpdb->get_results($wpdb->prepare("select description from {$wpdb->prefix}term_taxonomy where term_id = %d",$checkid));			
		// 			$description=unserialize($result[0]->description);					
		// 			foreach($description as $desckey =>$descval){  

		// 				//insert with update 
		// 				$array2= array($code => $pId);
		// 				$descript=array_merge($description,$array2);
		// 				$count = count($descript);
		// 				$description_data = serialize($descript);
		// 				$wpdb->update( $wpdb->term_taxonomy , array( 'description' => $description_data  ) , array( 'term_id' => $checkid ) );
		// 				$wpdb->update( $wpdb->term_taxonomy , array( 'count' => $count  ) , array( 'term_id' => $checkid ) );
		// 		    }
					
		// 		}
		// 		else{
		// 			global $wpdb;
		// 			$term_name=uniqid('pll_');
		// 			$terms=wp_insert_term($term_name,'post_translations');
		// 			$term_id=$terms['term_id'];
		// 			$term_tax_id=$terms['term_taxonomy_id'];
				 
		// 			$language=$wpdb->get_results("select term_id,description from {$wpdb->prefix}term_taxonomy where taxonomy ='language'");
				
		// 			$wpdb->insert($wpdb->prefix.'term_relationships',array(
		// 				'term_taxonomy_id'          => $term_tax_id,
		// 				'object_id'       => $pId
		// 			  ),
		// 			  array(
		// 				'%s',
		// 				'%s'
		// 			  ) 
		// 			); 				 										

		// 			$taxonomyid = $wpdb->get_results($wpdb->prepare("select term_taxonomy_id from {$wpdb->prefix}term_relationships where object_id = %d",$trans_post_id));
					
		// 			foreach($taxonomyid as $key => $taxo_id){
		// 				$tid = $taxo_id->term_taxonomy_id;						
		// 				$get_details = $wpdb->get_results($wpdb->prepare("select description,taxonomy from {$wpdb->prefix}term_taxonomy where term_taxonomy_id = %d",$tid));
						
		// 				if(!empty($get_details) && $get_details[0]->taxonomy == 'language'){														
		// 					$description=unserialize($get_details[0]->description);
		// 					$descript=explode('_',$description['locale']);
		// 					$language = array_key_exists(0,$descript) ? $descript[0] : "";
		// 					if(!empty($language)) {
		// 					$array = array($language => $trans_post_id);
		// 					$post_description=array_merge($array,array($code => $pId));
		// 					$count=count($post_description);
		// 					$description_data=serialize($post_description);
		// 					$wpdb->update( $wpdb->term_taxonomy , array( 'description' => $description_data  ) , array( 'term_id' => $term_id ) );
		// 					$wpdb->update( $wpdb->term_taxonomy , array( 'count' => $count  ) , array( 'term_id' => $term_id ) );
		// 					}
		// 				}
		// 			}
		// 		}

		// }
	}
}
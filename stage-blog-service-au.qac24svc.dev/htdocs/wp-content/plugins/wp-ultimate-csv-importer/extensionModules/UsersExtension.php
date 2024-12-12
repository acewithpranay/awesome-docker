<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class UsersExtension extends ExtensionHandler{
		private static $instance = null;

    public static function getInstance() {
		
				if (UsersExtension::$instance == null) {
                    UsersExtension::$instance = new UsersExtension;
				}
				return UsersExtension::$instance;
    }

    /**
	* Provides Users fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data){
        $import_type = $data;
        
        $response = [];
        if(is_plugin_active('woocommerce/woocommerce.php')){
             $billing_fields = array(
                'Billing First Name' => 'billing_first_name',
                'Billing Last Name' => 'billing_last_name',
                'Billing Company' => 'billing_company',
                'Billing Address1' => 'billing_address_1',
                'Billing Address2' => 'billing_address_2',
                'Billing City' => 'billing_city',
                'Billing PostCode' => 'billing_postcode',
                'Billing State' => 'billing_state',
                'Billing Country' => 'billing_country',
                'Billing Phone' => 'billing_phone',
                'Billing Email' => 'billing_email',
                'Shipping First Name' => 'shipping_first_name',
                'Shipping Last Name' => 'shipping_last_name',
                'Shipping Company' => 'shipping_company',
                'Shipping Address1' => 'shipping_address_1',
                'Shipping Address2' => 'shipping_address_2',
                'Shipping City' => 'shipping_city',
                'Shipping PostCode' => 'shipping_postcode',
                'Shipping State' => 'shipping_state',
                'Shipping Country' => 'shipping_country',
                'API Consumer Key' => 'woocommerce_api_consumer_key',
                'API Consumer Secret' => 'woocommerce_api_consumer_secret',
                'API Key Permissions' => 'woocommerce_api_key_permissions',
                'Shipping Region' => '_wpsc_shipping_region' ,
                'Billing Region' => '_wpsc_billing_region',
                'Cart' => '_wpsc_cart'
            );				
        }
        $billing_fields = isset($billing_fields) ? $billing_fields :'';
        $billing_value = $this->convert_static_fields_to_array($billing_fields);
        $response['billing_and_shipping_information'] = $billing_value;
        
        if(is_plugin_active( 'wp-members/wp-members.php')){ 
            $wp_members_fields = $this->custom_fields_by_wp_members();
            $response['custom_fields_wp_members'] = $wp_members_fields;       
        }
        if(is_plugin_active( 'members/members.php')){
            $members_fields = $this->custom_fields_by_members();
            if(!empty($members_fields)){
                $response['custom_fields_members'] = $members_fields;        
            }
            
        } 
        // if(is_plugin_active( 'ultimate-member/ultimate-member.php')){
        //     $members_fields = $this->custom_fields_by_ultimate_member();
        //     if(!empty($members_fields)){
        //         $response['custom_ultimate_members'] =  null;     
        //     }
            
        // } 
		return $response;	
    }

    public function custom_fields_by_wp_members () {
		$WPMemberFields = array();
        $get_WPMembers_fields = get_option('wpmembers_fields');
       
        $search_array = array('Choose a Username', 'First Name', 'Last Name', 'Email', 'Confirm Email', 'Website', 'Biographical Info', 'Password', 'Confirm Password', 'Terms of Service');

		if (is_array($get_WPMembers_fields) && !empty($get_WPMembers_fields)) {
			foreach ($get_WPMembers_fields as $get_fields) {
                foreach($search_array as $search_values){
                    if(is_array($get_fields)){   
                        if(in_array($search_values , $get_fields)){
                            unset($get_fields);
                        }
                    }
                }
                if(!empty($get_fields[2])){
                    $WPMemberFields['WPMEMBERS'][$get_fields[2]]['label'] = $get_fields[1];
                    $WPMemberFields['WPMEMBERS'][$get_fields[2]]['name'] = $get_fields[2];
                }
            }
        }
        $wp_mem_fields = $this->convert_fields_to_array($WPMemberFields);
        return $wp_mem_fields;
    }

    public function custom_fields_by_members () {
		$MemberFields = array();
		$MemberFields['MULTIROLE']['multi_user_role']['label'] = 'Multi User Role';
        $MemberFields['MULTIROLE']['multi_user_role']['name'] = 'multi_user_role';
        $mem_fields = $this->convert_fields_to_array($MemberFields);
		return $mem_fields;
    }
    
    // public function custom_fields_by_ultimate_member () {
	// 	$WPUltimateMember = array();
	// 	$get_WPUltimateMember = get_option('um_fields');
	// 	if(is_array($get_WPUltimateMember) && !empty($get_WPUltimateMember)) {
	// 		foreach($get_WPUltimateMember as $get_fields) {
	// 			$WPUltimateMember['ULTIMATEMEMBER'][$get_fields['metakey']]['label'] = $get_fields['label'];
	// 			$WPUltimateMember['ULTIMATEMEMBER'][$get_fields['metakey']]['name'] = $get_fields['metakey'];
	// 		}
    //     }
    //     $ultimate_member_fields = $this->convert_fields_to_array($WPUltimateMember);
	// 	return $ultimate_member_fields;
	// }
    /**
	* Users extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type ){
		if($import_type == 'Users'){
            return true;
        }
	}
        
 }
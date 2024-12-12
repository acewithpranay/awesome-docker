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

class LifterLmsImport
{
	private static $lifterlms_instance = null;

	public static function getInstance()
	{
		if (LifterLmsImport::$lifterlms_instance == null) {
			LifterLmsImport::$lifterlms_instance = new LifterLmsImport;
			return LifterLmsImport::$lifterlms_instance;
		}
		return LifterLmsImport::$lifterlms_instance;
    }
    
    public function set_lifterlms_values($header_array, $value_array, $map, $post_id, $type, $mode){
        $post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map, $header_array, $value_array);


		$this->lifterlms_values_import($post_values, $post_id, $type, $mode);
    }

    public function lifterlms_values_import($post_values, $post_id, $type, $mode){
        global $wpdb;	

            $lifter_course_setting_array = [];
            $lifter_course_setting_array['_llms_instructors'] = isset($post_values['_llms_instructors']) ? $post_values['_llms_instructors'] :'';
            $lifter_course_setting_array['_llms_sales_page_content_type'] = isset($post_values['_llms_sales_page_content_type']) ? $post_values['_llms_sales_page_content_type'] : '' ;
            $lifter_course_setting_array['_llms_sales_page_content_page_id'] = isset($post_values['_llms_sales_page_content_page_id']) ? $post_values['_llms_sales_page_content_page_id'] : '' ;
            $lifter_course_setting_array['_llms_sales_page_content_url'] = isset($post_values['_llms_sales_page_content_url']) ? $post_values['_llms_sales_page_content_url'] : '' ;
            $lifter_course_setting_array['_llms_length'] = isset($post_values['_llms_length']) ? $post_values['_llms_length'] : '';
            $lifter_course_setting_array['_llms_post_course_difficulty'] = isset($post_values['_llms_post_course_difficulty']) ? $post_values['_llms_post_course_difficulty'] : '';
            $lifter_course_setting_array['_llms_video_embed'] = isset($post_values['_llms_video_embed']) ? $post_values['_llms_video_embed'] : '';
            $lifter_course_setting_array['_llms_tile_featured_video'] = isset($post_values['_llms_tile_featured_video']) ? $post_values['_llms_tile_featured_video'] : '';
            $lifter_course_setting_array['_llms_audio_embed'] = isset($post_values['_llms_audio_embed']) ? $post_values['_llms_audio_embed'] : '' ;
            $lifter_course_setting_array['_llms_content_restricted_message'] = isset($post_values['_llms_content_restricted_message']) ? $post_values['_llms_content_restricted_message'] : '' ;
            $lifter_course_setting_array['_llms_enrollment_period'] = isset($post_values['_llms_enrollment_period']) ? $post_values['_llms_enrollment_period'] : '' ;
            $lifter_course_setting_array['_llms_enrollment_start_date'] = isset($post_values['_llms_enrollment_start_date']) ? $post_values['_llms_enrollment_start_date'] : '';
            $lifter_course_setting_array['_llms_enrollment_end_date'] = isset($post_values['_llms_enrollment_end_date']) ? $post_values['_llms_enrollment_end_date'] : '' ;
            $lifter_course_setting_array['_llms_enrollment_opens_message'] = isset($post_values['_llms_enrollment_opens_message']) ? $post_values['_llms_enrollment_opens_message'] : '';
            $lifter_course_setting_array['_llms_enrollment_closed_message'] = isset($post_values['_llms_enrollment_closed_message']) ? $post_values['_llms_enrollment_closed_message'] : '';
            $lifter_course_setting_array['_llms_time_period'] = isset($post_values['_llms_time_period']) ? $post_values['_llms_time_period'] : '';
            $lifter_course_setting_array['_llms_start_date'] = isset($post_values['_llms_start_date']) ? $post_values['_llms_start_date'] : '';
            $lifter_course_setting_array['_llms_end_date'] = isset($post_values['_llms_end_date']) ? $post_values['_llms_end_date'] : '';
            $lifter_course_setting_array['_llms_course_opens_message'] = isset($post_values['_llms_course_opens_message']) ? $post_values['_llms_course_opens_message'] : '';
            $lifter_course_setting_array['_llms_course_closed_message'] = isset($post_values['_llms_course_closed_message']) ? $post_values['_llms_course_closed_message'] : '';
            $lifter_course_setting_array['_llms_has_prerequisite'] = isset($post_values['_llms_has_prerequisite']) ? $post_values['_llms_has_prerequisite'] : '';
            $lifter_course_setting_array['_llms_prerequisite'] = isset($post_values['_llms_prerequisite']) ? $post_values['_llms_prerequisite'] : '';
            $lifter_course_setting_array['_llms_prerequisite_track'] = isset($post_values['_llms_prerequisite_track']) ? $post_values['_llms_prerequisite_track'] : '';
            $lifter_course_setting_array['_llms_enable_capacity'] = isset($post_values['_llms_enable_capacity']) ? $post_values['_llms_enable_capacity'] : '';
            $lifter_course_setting_array['_llms_capacity'] = isset($post_values['_llms_capacity']) ? $post_values['_llms_capacity'] : '';
            $lifter_course_setting_array['_llms_capacity_message'] = isset($post_values['_llms_capacity_message']) ? $post_values['_llms_capacity_message'] : '';
            $lifter_course_setting_array['_llms_reviews_enabled'] = isset($post_values['_llms_reviews_enabled']) ? $post_values['_llms_reviews_enabled'] : '';
            $lifter_course_setting_array['_llms_display_reviews'] = isset($post_values['_llms_display_reviews']) ? $post_values['_llms_display_reviews'] : '';
            $lifter_course_setting_array['_llms_num_reviews'] = isset($post_values['_llms_num_reviews']) ? $post_values['_llms_num_reviews'] : '';
            $lifter_course_setting_array['_llms_multiple_reviews_disabled'] = isset($post_values['_llms_multiple_reviews_disabled']) ? $post_values['_llms_multiple_reviews_disabled'] : '';
                        
            foreach ($lifter_course_setting_array as $course_key => $course_value) {
                if($course_key == '_llms_instructors'){
                    $data =array();
                   $inst_value = explode('|',$course_value);
                   foreach($inst_value as $ins){
                       $arr =array();
                         $instructor = explode(',',$ins);
                           $arr['label'] = 'Author';
                           $arr['visibility'] = $instructor[0];
                           $user_name =$instructor[1];
                           $user_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE display_name='$user_name'");
                           $arr['id'] = $user_id;
                           $arr['name'] = $user_name;
                          
                          
                           array_push($data,$arr);
                           
                   }
                   update_post_meta($post_id, $course_key, $data);
                 
               }
               else{
                   update_post_meta($post_id, $course_key, $course_value);
               }

                
            }
		

        if($type == 'lesson' || $type == 'llms_quiz' || $type == 'llms_coupon'){

            if($type == 'lesson'){
                LifterLmsImport::$lifterlms_instance->insert_lesson_details($get_section_id, $post_id, $post_values, $mode);
            }
            if($type == 'llms_quiz'){
                LifterLmsImport::$lifterlms_instance->insert_quiz_details($type,$get_section_id, $post_id, $post_values, $mode);
            }  
            if($type == 'llms_coupon'){
                LifterLmsImport::$lifterlms_instance->insert_coupon_details($type,$get_section_id, $post_id, $post_values, 0, $mode, 'new');
            }
        }
   
        if($type == 'llms_review'){
            LifterLmsImport::$lifterlms_instance->insert_review_details($post_id, $post_values, $mode);
        }
	}
	
    public function insert_lesson_details($inserted_section_id, $lesson_post_id, $post_values, $mode){
        global $wpdb;
        if($mode == 'Insert'){
            if(isset($inserted_section_id)){
                LifterLmsImport::$lifterlms_instance->insert_to_lifterlms_section_items($inserted_section_id, $lesson_post_id, 'lesson');
            }
        }
        if($mode == 'Update'){
            if(isset($inserted_section_id)){ 
                LifterLmsImport::$lifterlms_instance->insert_to_lifterlms_section_items($inserted_section_id, $lesson_post_id, 'lesson');
            }
        }

        $lesson_meta_array['_llms_reviews_enabled'] = isset($post_values['_llms_reviews_enabled']) ? $post_values['_llms_reviews_enabled'] : '';
        $lesson_meta_array['_llms_display_reviews'] = isset($post_values['_llms_display_reviews']) ? $post_values['_llms_display_reviews'] : '';
        $lesson_meta_array['_llms_num_reviews'] = isset($post_values['_llms_num_reviews']) ? $post_values['_llms_num_reviews'] : '';
        $lesson_meta_array['_llms_multiple_reviews_disabled'] = isset($post_values['_llms_multiple_reviews_disabled']) ? $post_values['_llms_multiple_reviews_disabled'] : '';
        $lesson_meta_array['_llms_video_embed'] = isset($post_values['_llms_video_embed']) ? $post_values['_llms_video_embed'] : '';
        $lesson_meta_array['_llms_audio_embed'] = isset($post_values['_llms_audio_embed']) ? $post_values['_llms_audio_embed'] : '';
        $lesson_meta_array['_llms_free_lesson'] = isset($post_values['_llms_free_lesson']) ? $post_values['_llms_free_lesson'] : '';
        $lesson_meta_array['_llms_has_prerequisite'] = isset($post_values['_llms_has_prerequisite']) ? $post_values['_llms_has_prerequisite'] : '';
        $lesson_meta_array['_llms_prerequisite'] = isset($post_values['_llms_prerequisite']) ? $post_values['_llms_prerequisite'] : '';
        $lesson_meta_array['_llms_drip_method'] = isset($post_values['_llms_drip_method']) ? $post_values['_llms_drip_method'] : '';
        $lesson_meta_array['_llms_days_before_available'] = isset($post_values['_llms_days_before_available']) ? $post_values['_llms_days_before_available'] : '';
        $lesson_meta_array['_llms_date_available'] = isset($post_values['_llms_date_available']) ? $post_values['_llms_date_available'] : '';
        $lesson_meta_array['_llms_time_available'] = isset($post_values['_llms_time_available']) ? $post_values['_llms_time_available'] : '';
        $lesson_meta_array['_llms_require_passing_grade'] = isset($post_values['_llms_require_passing_grade']) ? $post_values['_llms_require_passing_grade'] : '';
        $lesson_meta_array['_thumbnail_id'] = isset($post_values['_thumbnail_id']) ? $post_values['_thumbnail_id'] : '';
        $lesson_meta_array['_llms_order'] = isset($post_values['_llms_order']) ? $post_values['_llms_order'] : '';
        $lesson_meta_array['_llms_parent_course'] = isset($post_values['_llms_parent_course']) ? $post_values['_llms_parent_course'] : '';
        $lesson_meta_array['_llms_parent_section'] = isset($post_values['_llms_parent_section']) ? $post_values['_llms_parent_section'] : '';
        $lesson_meta_array['_llms_require_assignment_passing_grade'] = isset($post_values['_llms_require_assignment_passing_grade']) ? $post_values['_llms_require_assignment_passing_grade'] : '';
        $lesson_meta_array['_llms_points'] = isset($post_values['_llms_points']) ? $post_values['_llms_points'] : '';
        $lesson_meta_array['_llms_quiz_enabled'] = isset($post_values['_llms_quiz_enabled']) ? $post_values['_llms_quiz_enabled'] : '';
        $lesson_meta_array['_llms_lesson_id'] = isset($post_values['_llms_lesson_id']) ? $post_values['_llms_lesson_id'] : '';
        $lesson_meta_array['_llms_allowed_attempts'] = isset($post_values['_llms_allowed_attempts']) ? $post_values['_llms_allowed_attempts'] : '';
        $lesson_meta_array['_llms_limit_attempts'] = isset($post_values['_llms_limit_attempts']) ? $post_values['_llms_limit_attempts'] : '';
        $lesson_meta_array['_llms_limit_time'] = isset($post_values['_llms_limit_time']) ? $post_values['_llms_limit_time'] : '';
        $lesson_meta_array['_llms_passing_percent'] = isset($post_values['_llms_passing_percent']) ? $post_values['_llms_passing_percent'] : '';
        $lesson_meta_array['_llms_show_correct_answer'] = isset($post_values['_llms_show_correct_answer']) ? $post_values['_llms_show_correct_answer'] : '';
        $lesson_meta_array['_llms_time_limit'] = isset($post_values['_llms_time_limit']) ? $post_values['_llms_time_limit'] : '';
        $lesson_meta_array['_llms_quiz'] = isset($post_values['_llms_quiz']) ? $post_values['_llms_quiz'] : '';
        $lesson_meta_array['_llms_free_lesson'] = isset($post_values['_llms_free_lesson']) ? $post_values['_llms_free_lesson'] : '';

        foreach ($lesson_meta_array as $lesson_key => $lesson_value) {
            update_post_meta($lesson_post_id, $lesson_key, $lesson_value);
        }
    }

    public function insert_quiz_details($type,$inserted_section_id, $quiz_post_id, $post_values, $mode){
        global $wpdb;

        if($mode == 'Update'){
            $check_for_same_section_id = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}lifterlms_section_items WHERE item_id = $quiz_post_id AND item_type = 'llms_quiz' ");
            if(!empty($check_for_same_section_id) && $inserted_section_id == $check_for_same_section_id){
                $get_section_item_id = $wpdb->get_var("SELECT section_item_id FROM {$wpdb->prefix}lifterlms_section_items WHERE section_id = $check_for_same_section_id AND item_id = $quiz_post_id AND item_type = 'llmsquiz' ");
                LifterLmsImport::$lifterlms_instance->update_to_lifterlms_section_items($inserted_section_id, $quiz_post_id, 'llms_quiz', $get_section_item_id );
            }
        }
        
        if($mode == 'Insert'){
            if(!empty($inserted_section_id)){
                LifterLmsImport::$lifterlms_instance->insert_to_lifterlms_section_items($inserted_section_id, $quiz_post_id, 'llms_quiz');
            }
        }
                                
        $quiz_meta_array['_llms_duration'] = isset($post_values['_llms_duration']) ? $post_values['_llms_duration'] : '10 minute';
        $quiz_meta_array['_llms_minus_points'] = isset($post_values['_llms_minus_points']) ? $post_values['_llms_minus_points'] : 0;
        $quiz_meta_array['_llms_minus_skip_questions'] = isset($post_values['_llms_minus_skip_questions']) ? $post_values['_llms_minus_skip_questions'] : 'no';
        $quiz_meta_array['_llms_passing_grade'] = isset($post_values['_llms_passing_grade']) ? $post_values['_llms_passing_grade'] : 80;
        $quiz_meta_array['_llms_retake_count'] = isset($post_values['_llms_quiz_retake_count']) ? $post_values['_llms_quiz_retake_count'] : 0;                        
        $quiz_meta_array['_llms_instant_check'] = isset($post_values['_llms_instant_check']) ? $post_values['_llms_instant_check'] : 'no';
        $quiz_meta_array['_llms_negative_marking'] = isset($post_values['_llms_negative_marking']) ? $post_values['_llms_negative_marking'] : 'no';
        $quiz_meta_array['_llms_pagination'] = isset($post_values['_llms_pagination']) ? $post_values['_llms_pagination'] : 1;
        $quiz_meta_array['_llms_show_correct_review'] = isset($post_values['_llms_show_correct_review']) ? $post_values['_llms_show_correct_review'] : 'yes';
        $quiz_meta_array['_llms_review'] = isset($post_values['_llms_review']) ? $post_values['_llms_review'] : 'yes';
        
        foreach ($quiz_meta_array as $quiz_key => $quiz_value) {
            update_post_meta($quiz_post_id, $quiz_key, $quiz_value);
        }

        if(isset($post_values['question_title'])){
            $get_question_titles = explode(',', $post_values['question_title']);
            $get_question_descriptions = explode(',', $post_values['question_description']);

            $get_all_question_titles = $wpdb->get_results("SELECT post_title FROM {$wpdb->prefix}posts WHERE post_type = 'llms_question' AND post_status = 'publish' ", ARRAY_A);
            $all_questions_title = array_column($get_all_question_titles, 'post_title');
           
            $temp = 0;
            foreach($get_question_titles as $question_titles){
            
                if($mode == 'Insert'){
                    if(in_array($question_titles, $all_questions_title)){
                        $question_post_id = $wpdb->get_var("SELECT ID from {$wpdb->prefix}posts WHERE post_title = '$question_titles' AND post_type = 'llms_question' AND post_status = 'publish' ");       
                        
                        $post_values['quiz_id'] = $quiz_post_id;
                        $check_assigned_to_quiz = $wpdb->get_var("SELECT quiz_question_id FROM {$wpdb->prefix}lifterlms_quiz_questions WHERE question_id = $question_post_id  ");
                        if(empty($check_assigned_to_quiz)){
                            LifterLmsImport::$lifterlms_instance->insert_coupon_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, 'existing');
                        }

                    }else{
                        $question_description = isset($get_question_descriptions[$temp]) ? $get_question_descriptions[$temp] : '';
                
                        $question_post_id = LifterLmsImport::$lifterlms_instance->insert_post($question_titles, $question_description, 'llms_question');
                        $post_values['quiz_id'] = $quiz_post_id;
                        LifterLmsImport::$lifterlms_instance->insert_coupon_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, 'existing');
                    }
                }

                if($mode == 'Update'){  
                    if(in_array($question_titles, $all_questions_title)){
                        $question_post_id = $wpdb->get_var("SELECT ID from {$wpdb->prefix}posts WHERE post_title = '$question_titles' AND post_type = 'llms_question' AND post_status = 'publish' ");       
                        $post_values['quiz_id'] = $quiz_post_id;
                        $check_assigned_to_quiz = $wpdb->get_var("SELECT quiz_question_id FROM {$wpdb->prefix}lifterlms_quiz_questions WHERE question_id = $question_post_id  ");
                        if(empty($check_assigned_to_quiz)){
                            LifterLmsImport::$lifterlms_instance->insert_coupon_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, 'existing');
                        }
                    }else{
                        $question_description = isset($get_question_descriptions[$temp]) ? $get_question_descriptions[$temp] : '';
                        $question_post_id = LifterLmsImport::$lifterlms_instance->insert_post($question_titles, $question_description, 'llms_question');
                        $post_values['quiz_id'] = $quiz_post_id;
                        LifterLmsImport::$lifterlms_instance->insert_coupon_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, 'existing');
                    }
                }  

                $temp++;
            }
        }    
    }

    public function insert_coupon_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, $condition){
        global $wpdb;

        $question_meta_array['_llms_enable_trial_discount'] = isset($post_values['_llms_enable_trial_discount']) ? $post_values['_llms_enable_trial_discount'] : '';
        $question_meta_array['_llms_trial_amount'] = isset($post_values['_llms_trial_amount']) ? $post_values['_llms_trial_amount'] : '';
        $question_meta_array['_llms_coupon_courses'] = isset($post_values['_llms_coupon_courses']) ? $post_values['_llms_coupon_courses'] : '';
        $question_meta_array['_llms_coupon_membership'] = isset($post_values['_llms_coupon_membership']) ? $post_values['_llms_coupon_membership'] : '';
        $question_meta_array['_llms_coupon_amount'] = isset($post_values['_llms_coupon_amount']) ? $post_values['_llms_coupon_amount'] : '';
        $question_meta_array['_llms_usage_limit'] = isset($post_values['_llms_usage_limit']) ? $post_values['_llms_usage_limit'] : '';
        $question_meta_array['_llms_discount_type'] = isset($post_values['_llms_discount_type']) ? $post_values['_llms_discount_type'] : '';
        $question_meta_array['_llms_description'] = isset($post_values['_llms_description']) ? $post_values['_llms_description'] : '';
        $question_meta_array['_llms_expiration_date'] = isset($post_values['_llms_expiration_date']) ? $post_values['_llms_expiration_date'] : '';
        $question_meta_array['_llms_plan_type'] = isset($post_values['_llms_plan_type']) ? $post_values['_llms_plan_type'] : '';

        foreach ($question_meta_array as $question_key => $question_value) {
            if($question_key == '_llms_coupon_courses'){
                $course_data = explode('|',$question_value);

update_post_meta($question_post_id, $question_key, $course_data);
            }
            else{
                update_post_meta($question_post_id, $question_key, $question_value);
            }
            
        }

        if(isset($post_values['quiz_id'])){
            $quiz_id = $post_values['quiz_id'];
            $question_order = 1;

            $get_question_order = $wpdb->get_var("SELECT question_order FROM {$wpdb->prefix}lifterlms_quiz_questions WHERE quiz_id = $quiz_id ORDER BY quiz_question_id DESC LIMIT 1");
            if(!empty($get_question_order)){
                $question_order = $get_question_order + 1;
            }

            if($mode == 'Insert'){
                LifterLmsImport::$lifterlms_instance->insert_to_lifterlms_quiz_questions($quiz_id, $question_post_id, $question_order);
            }

            if($mode == 'Update'){
              
                LifterLmsImport::$lifterlms_instance->insert_to_lifterlms_quiz_questions($quiz_id, $question_post_id, $question_order);
                
                if($condition == 'new'){
                    if(!empty($inserted_section_id)){
                        $check_for_same_section_id = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}lifterlms_section_items WHERE item_id = $quiz_id AND item_type = 'llms_quiz' ");
                        if(!empty($check_for_same_section_id) && $inserted_section_id == $check_for_same_section_id){
                            $get_section_item_id = $wpdb->get_var("SELECT section_item_id FROM {$wpdb->prefix}lifterlms_section_items WHERE section_id = $check_for_same_section_id AND item_id = $quiz_id AND item_type = 'llms_quiz' ");
                           
                            LifterLmsImport::$lifterlms_instance->update_to_lifterlms_section_items($inserted_section_id, $quiz_id, 'llms_quiz', $get_section_item_id);
                        }
                       
                    }
                }
            }

            if(!empty($inserted_section_id)){
                $check_assigned_to_course = $wpdb->get_var("SELECT section_item_id FROM {$wpdb->prefix}lifterlms_section_items WHERE section_id = $inserted_section_id AND item_id = $quiz_id AND item_type = 'llms_quiz' ");
                $check_assigned_to_another_course = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}lifterlms_section_items WHERE item_id = $quiz_id AND item_type = 'llms_quiz' ");
                
                if(empty($check_assigned_to_course) && empty($check_assigned_to_another_course)){
                    LifterLmsImport::$lifterlms_instance->insert_to_lifterlms_section_items($inserted_section_id, $quiz_id, 'llms_quiz');
                }
            }
        }

        else{
            if(isset($post_values['question_options'])){  
                $get_separate_options = explode('->', $post_values['question_options']);
                foreach($get_separate_options as $option_values){
                    $get_title_options = explode('|', $option_values);
                    $wpdb->insert( 
                        "{$wpdb->prefix}lifterlms_question_answers", 
                        array("question_id" => $question_post_id, "title" => $get_title_options[0], "is_true"=>$get_title_options[1]),
                        array('%d', '%s','%s')
                    );
                }
            }
        }
    }

    public function insert_review_details($order_id, $post_values, $mode){
    
        global $wpdb;

        $review_meta_array['_thumbnail_id'] = isset($post_values['_thumbnail_id']) ? $post_values['_thumbnail_id'] : '';
        $review_meta_array['_llms_reviews_enabled'] = isset($post_values['_llms_reviews_enabled']) ? $post_values['_llms_reviews_enabled'] : '';
        $review_meta_array['_llms_display_reviews'] = isset($post_values['_llms_display_reviews']) ? $post_values['_llms_display_reviews'] : '';
        $review_meta_array['_llms_num_reviews'] = isset($post_values['_llms_num_reviews']) ? $post_values['_llms_num_reviews'] : '';
        $review_meta_array['_llms_multiple_reviews_disabled'] = isset($post_values['_llms_multiple_reviews_disabled']) ? $post_values['_llms_multiple_reviews_disabled'] : '';

        foreach($review_meta_array as $order_key => $order_value){
            update_post_meta($order_id, $order_key, $order_value);
        }	
    }

    public function insert_to_lifterlms_section_items($inserted_section_id, $post_id, $type){
        global $wpdb;
        $wpdb->insert( 
            "{$wpdb->prefix}lifterlms_section_items", 
            array("section_id" => $inserted_section_id, "item_id" => $post_id, "item_type" => "$type"),
            array('%d', '%d', '%s')
        );
    }

    public function update_to_lifterlms_section_items($inserted_section_id, $post_id, $type, $get_section_item_id){
        global $wpdb;
        $wpdb->update( 
            $wpdb->prefix.'lifterlms_section_items', 
            array('section_id' => $inserted_section_id,'item_id' => $post_id, "item_type" => "$type"),
            array( 'section_item_id' => $get_section_item_id )
        );
    }

    public function insert_to_lifterlms_quiz_questions($quiz_id, $question_post_id, $question_order){
        global $wpdb;
        $wpdb->insert( 
            "{$wpdb->prefix}lifterlms_quiz_questions", 
            array("quiz_id" => $quiz_id, "question_id" => $question_post_id, "question_order" => $question_order),
            array('%d', '%d', '%d')
        );
    }

    public function insert_post($post_title, $post_content, $post_type){
        $post_array['post_title'] = $post_title;
        $post_array['post_content'] = $post_content;
        $post_array['post_type'] = $post_type;
        $post_array['post_status'] = 'publish';

        $post_id = wp_insert_post($post_array);
        return $post_id;
    }

    public function update_post($post_title, $post_content, $post_type, $id){
        $update_array['post_title'] = $post_title;
        $update_array['post_content'] = $post_content;
        $update_array['post_type'] = $post_type;
        $update_array['post_status'] = 'publish';
        $update_array['ID'] = $id;

        wp_update_post($update_array);
    }
}

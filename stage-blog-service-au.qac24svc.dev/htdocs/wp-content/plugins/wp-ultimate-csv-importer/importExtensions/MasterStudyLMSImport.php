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
class MasterStudyLMSImport
{
	private static $stm_instance = null;

	public static function getInstance()
	{
		if (MasterStudyLMSImport::$stm_instance == null) {
			MasterStudyLMSImport::$stm_instance = new MasterStudyLMSImport;
			return MasterStudyLMSImport::$stm_instance;

		}
		return MasterStudyLMSImport::$stm_instance;
    }

    public function set_stm_values($header_array, $value_array, $map, $post_id, $type, $mode){
        $post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map, $header_array, $value_array);

		$this->stm_values_import($post_values, $post_id, $type, $mode);
    }
    public function stm_values_import($post_values, $post_id, $type, $mode){
        global $wpdb;	

		if($type == 'stm-courses'){
          
            $course_setting_array = []; 

            $dates = explode(",", $post_values['status_dates']);
            $timestamps = array();
            foreach ($dates as $date) {
                $timestamp = strtotime($date);
                if ($timestamp !== false) {
                  $timestamps[] = $timestamp;
                }
              }
         

            $course_setting_array['featured'] = isset($post_values['featured']) ? $post_values['featured'] : 1000;
            $course_setting_array['views'] = isset($post_values['views']) ? $post_values['views'] : 0;
            $course_setting_array['level'] = isset($post_values['level']) ? $post_values['level'] : 0;
            $course_setting_array['duration_info'] = isset($post_values['duration_info']) ? $post_values['duration_info'] : 'no';
            $course_setting_array['video_duration'] = isset($post_values['video_duration']) ? $post_values['video_duration'] : '';
            $course_setting_array['price'] = isset($post_values['price']) ? $post_values['price'] : 'yes';
            $course_setting_array['sale_price'] = isset($post_values['sale_price']) ? $post_values['sale_price'] : 'yes';
            $course_setting_array['status'] = isset($post_values['status']) ? $post_values['status'] : 0;
            $course_setting_array['status_dates'] = isset($post_values['status_dates']) ? implode(',', array_map(function($timestamp) { return $timestamp * 1000; }, $timestamps)) : '';
            $course_setting_array['status_dates_end'] = isset($post_values['status_dates_end']) ? strtotime($post_values['status_dates_end']) * 1000: '';
            $course_setting_array['status_dates_start'] = isset($post_values['status_dates_start']) ? strtotime($post_values['status_dates_start']) * 1000: '';

            $faq_string = isset($post_values['faq']) ? $post_values['faq'] : '';
            $faq_array = array();
            if (!empty($faq_string)) {
              $faq_items = explode('|', $faq_string);
              foreach ($faq_items as $faq_item) {
                $faq = array();
                $faq_parts = explode(',', $faq_item);
                foreach ($faq_parts as $faq_part) {
                  $faq_part_parts = explode(':', $faq_part);
                  $faq[trim($faq_part_parts[0])] = trim($faq_part_parts[1]);
                }
                $faq_array[] = $faq;
              }
            }
            $course_setting_array['faq'] = json_encode($faq_array);


            $course_files_pack_string = isset($post_values['course_files_pack']) ? $post_values['course_files_pack'] : '';
            $course_files_pack_array = array();
            if (!empty($course_files_pack_string)) {
              $course_files = explode('|', $course_files_pack_string);
              foreach ($course_files as $course_file) {
                $file_parts = explode(',', $course_file);
                $file_name_parts = explode(':', $file_parts[0]);
                $course_files_pack_array[] = array('course_files_label' => trim($file_name_parts[1]), 'course_files' => '');
              }
            }
            $course_setting_array['course_files_pack'] = json_encode($course_files_pack_array);


            $course_setting_array['expiration_course'] = isset($post_values['expiration_course']) ? $post_values['expiration_course'] : '';
            $course_setting_array['not_membership'] = isset($post_values['not_membership']) ? $post_values['not_membership'] : '';
            $course_setting_array['end_time'] = isset($post_values['end_time']) ? $post_values['end_time'] : '';
            $course_setting_array['announcement'] = isset($post_values['announcement']) ? $post_values['announcement'] : '';
            $course_setting_array['current_students'] = isset($post_values['current_students']) ? $post_values['current_students'] : '';
            $quiz_id = isset($post_values['quiz_id']) ? trim(str_replace('|', ',', $post_values['quiz_id']), ',') : '';
            $lesson_id = isset($post_values['lesson_id']) ? trim(str_replace('|', ',', $post_values['lesson_id']), ',') : '';
            $quiz_name = isset($post_values['quiz_name']) ? trim(str_replace('|', ',', $post_values['quiz_name']), ',') : '';
            $lesson_name = isset($post_values['lesson_name']) ? trim(str_replace('|', ',', $post_values['lesson_name']), ',') : '';

                
            if (!empty($quiz_id)) {
                $course_setting_array['quiz_id'] = $quiz_id;
            }
            
            if (!empty($lesson_id)) {
                $course_setting_array['lesson_id'] = $lesson_id;
            }

            if (!empty($quiz_name)) {
                $course_setting_array['quiz_name'] = $quiz_name;
            }
            
            if (!empty($lesson_name)) {
                $course_setting_array['lesson_name'] = $lesson_name;
            }

            $curriculum = isset($post_values['curriculum']) ? $post_values['curriculum'] : '';
            $lesson_array = explode(',', $lesson_id);
            $quiz_array = explode(',', $quiz_id);
            $curriculum_array = explode(',', $curriculum);
            $new_curriculum_array = array();


            for ($i = 0; $i < count($curriculum_array); $i++) {
                $new_curriculum_array[] = $curriculum_array[$i];
                $new_curriculum_array[] = $lesson_array[$i];
                $new_curriculum_array[] = $quiz_array[$i];
              }
              
              $new_curriculum = implode(',', $new_curriculum_array);

              $course_setting_array['curriculum'] = $new_curriculum;


            foreach ($course_setting_array as $course_key => $course_value) {

                update_post_meta($post_id, $course_key, $course_value);

            }
            
		} 


        if($type == 'stm-lessons' || $type == 'stm-quizzes' || $type == 'stm-questions'){
            if($type == 'stm-lessons'){
                MasterStudyLMSImport::$stm_instance->insert_lesson_details($get_section_id, $post_id, $post_values, $mode);
            }
            if($type == 'stm-quizzes'){
                MasterStudyLMSImport::$stm_instance->insert_quiz_details($type,$get_section_id, $post_id, $post_values, $mode);
            }  
            if($type == 'stm-questions'){
                MasterStudyLMSImport::$stm_instance->insert_question_details($type,$get_section_id, $post_id, $post_values, 0, $mode, 'new');
            }
        }
   
        if($type == 'stm-orders'){
            MasterStudyLMSImport::$stm_instance->insert_order_details($post_id, $post_values, $mode);
        }


	}
	
    public function insert_lesson_details($inserted_section_id, $lesson_post_id, $post_values, $mode){
        global $wpdb;
    
        if(isset($post_values['duration'])){
            update_post_meta($lesson_post_id, 'duration', $post_values['duration']);
        }
        if(isset($post_values['preview'])){
            update_post_meta($lesson_post_id, 'preview', $post_values['preview']);
        }
        if(isset($post_values['lesson_excerpt'])){
            update_post_meta($lesson_post_id, 'lesson_excerpt', $post_values['lesson_excerpt']);
        }
        if (isset($post_values['lesson_files_pack'])) {
            $pack_values = array();
            $pack_parts = explode(',', $post_values['lesson_files_pack']);
        
            foreach ($pack_parts as $part) {
                $key_value = explode(':', $part);
                $key = trim($key_value[0]);
                $value = trim($key_value[1]);
        
                if ($key == 'closed_tab') {
                    $pack_values[$key] = ($value == 1) ? true : false;
                } elseif ($key == 'lesson_files_label') {
                    $pack_values[$key] = rtrim($value, ';');
                }
            }
        
            update_post_meta($lesson_post_id, 'lesson_files_pack', json_encode(array($pack_values)));
        }
        
        
        
        if(isset($post_values['_thumbnail_id'])){
            update_post_meta($lesson_post_id, '_thumbnail_id', $post_values['_thumbnail_id']);
        }
        if(isset($post_values['type'])){
            update_post_meta($lesson_post_id, 'type', $post_values['type']);
        }
        if(isset($post_values['video_type'])){
            update_post_meta($lesson_post_id, 'video_type', $post_values['video_type']);
        }
        if(isset($post_values['lesson_youtube_url'])){
            update_post_meta($lesson_post_id, 'lesson_youtube_url', $post_values['lesson_youtube_url']);
        }
        if(isset($post_values['presto_player_idx'])){
            update_post_meta($lesson_post_id, 'presto_player_idx', $post_values['presto_player_idx']);
        }
        if(isset($post_values['lesson_video'])){
            update_post_meta($lesson_post_id, 'lesson_video', $post_values['lesson_video']);
        }
        if(isset($post_values['lesson_video_poster'])){
            update_post_meta($lesson_post_id, 'lesson_video_poster', $post_values['lesson_video_poster']);
        }
        if(isset($post_values['lesson_video_width'])){
            update_post_meta($lesson_post_id, 'lesson_video_width', $post_values['lesson_video_width']);
        }
        if(isset($post_values['lesson_shortcode'])){
            update_post_meta($lesson_post_id, 'lesson_shortcode', $post_values['lesson_shortcode']);
        }
        if(isset($post_values['lesson_embed_ctx'])){
            update_post_meta($lesson_post_id, 'lesson_embed_ctx', $post_values['lesson_embed_ctx']);
        }
        if(isset($post_values['lesson_stream_url'])){
            update_post_meta($lesson_post_id, 'lesson_stream_url', $post_values['lesson_stream_url']);
        }
        if(isset($post_values['lesson_vimeo_url'])){
            update_post_meta($lesson_post_id, 'lesson_vimeo_url', $post_values['lesson_vimeo_url']);
        }
        if(isset($post_values['lesson_ext_link_url'])){
            update_post_meta($lesson_post_id, 'lesson_ext_link_url', $post_values['lesson_ext_link_url']);
        }

    }

    public function insert_quiz_details($type,$inserted_section_id, $quiz_post_id, $post_values, $mode){
        global $wpdb;
       
                                
        $quiz_meta_array['duration'] = isset($post_values['duration']) ? $post_values['duration'] : '10 minute';
        $quiz_meta_array['lesson_excerpt'] = isset($post_values['lesson_excerpt']) ? $post_values['lesson_excerpt'] : 0;
        $quiz_meta_array['quiz_style'] = isset($post_values['quiz_style']) ? $post_values['quiz_style'] : 'no';
        $quiz_meta_array['correct_answer'] = isset($post_values['correct_answer']) ? $post_values['correct_answer'] : 80;
        $quiz_meta_array['passing_grade'] = isset($post_values['passing_grade']) ? $post_values['passing_grade'] : 0;                        
        $quiz_meta_array['re_take_cut'] = isset($post_values['re_take_cut']) ? $post_values['re_take_cut'] : 'no';
        $quiz_meta_array['random_questions'] = isset($post_values['random_questions']) ? $post_values['random_questions'] : 'no';
        $quiz_meta_array['questions'] = isset($post_values['questions']) ? $post_values['questions'] : 1;
        
        foreach ($quiz_meta_array as $quiz_key => $quiz_value) {
            update_post_meta($quiz_post_id, $quiz_key, $quiz_value);
        }   

      
    }

    public function insert_question_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, $condition){
        global $wpdb;
        $temp=0;
        if(isset($post_values['type'])){
            $stm_questions_type = explode(',',$post_values['type']);
        }
        if(isset($post_values['question_explanation'])){
            $stm_questions_explanation = explode(',',$post_values['question_explanation']);
        }
        if (isset($post_values['answers'])) {
            $answers = $post_values['answers'];
            $answers_array = explode('|', trim($answers, '|'));
            $result_array = array();
            $i = 0;
            foreach ($answers_array as $answer) {
                $answer_array = explode(',', $answer);
                $result_array[$i]['text'] = $answer_array[0];
                $result_array[$i]['isTrue'] = $answer_array[1];
                $i++;
            }
            $question_meta_array['answers'] = $result_array;
        }
        
        
        
        if(isset($post_values['question'])){
            $stm_questions = explode(',',$post_values['question']);
        }
        if(isset($post_values['question_hint'])){
            $stm_questions_hint = explode(',',$post_values['question_hint']);
        }
        if(isset($post_values['question_view_type'])){
            $stm_questions_view_type = explode(',',$post_values['question_view_type']);
        }
        if(isset($post_values['image'])){
            $stm_questions_image = explode(',',$post_values['image']);
        }


        $question_meta_array['type'] = isset($stm_questions_type[$temp]) ? $stm_questions_type[$temp] : 'true_or_false';
        $question_meta_array['question_explanation'] = isset($stm_questions_explanation[$temp]) ? $stm_questions_explanation[$temp] : NULL;
        $question_meta_array['question'] = isset($stm_questions[$temp]) ? $stm_questions[$temp] : NULL;
        $question_meta_array['question_hint'] = isset($stm_questions_hint[$temp]) ? $stm_questions_hint[$temp] : NULL;
        $question_meta_array['question_view_type'] = isset($stm_questions_view_type[$temp]) ? $stm_questions_view_type[$temp] : NULL;
        $question_meta_array['image'] = isset($stm_questions_image[$temp]) ? $stm_questions_image[$temp] : NULL;


        foreach ($question_meta_array as $question_key => $question_value) {
          update_post_meta($question_post_id, $question_key, $question_value);


        }
      

    }

    public function insert_order_details($order_id, $post_values, $mode){
    
        global $wpdb;
        $order_key = strtoupper( uniqid( 'ORDER' ) );

        $order_meta_array = [];
     

        $order_meta_array['status'] = isset($post_values['status']) ? $post_values['status'] : '';
  

        foreach($order_meta_array as $order_key => $order_value){
            update_post_meta($order_id, $order_key, $order_value);
        }

    }

    
}
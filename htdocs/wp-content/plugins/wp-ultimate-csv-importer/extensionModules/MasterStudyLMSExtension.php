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

class MasterStudyLMSExtension extends ExtensionHandler{
    private static $instance = null;

    public static function getInstance() {        
        if (MasterStudyLMSExtension::$instance == null) {
            MasterStudyLMSExtension::$instance = new MasterStudyLMSExtension;
        }
        return MasterStudyLMSExtension::$instance;
    }



    public function processExtension($data){   
        $mode = isset($_POST['Mode']) ? sanitize_text_field($_POST['Mode']) : ""; 
        $import_type = $data;

        $response = [];
        //$import_type = $this->import_type_as($import_type);
        if(is_plugin_active('masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php')){   
            if($import_type == 'stm-courses'){
                $masterstudy_meta_fields = array(
                    'Duration Info' => 'duration_info',
                    'Features' => 'featured',
                    'Views' => 'views',
                    'Level' => 'level',
                    'Current students' => 'current_students',
                    'Duration info' => 'duration_info',
                    'Video duration' => 'video_duration',
                    'Price' => 'price',
                    'Sale_price' => 'sale_price',
                    'Status' => 'status', 
                    'Status dates' => 'status_dates',
                    'Status dates end' => 'status_dates_end',
                    'Status dates start' => 'status_dates_start',
                    'Faq'=>'faq',   
                    'Expiration_course'=>'expiration_course',  
                    'Not membership'=>'not_membership',  
                    'End time'=>'end_time',  
                    'Announcement'=>'announcement', 
                    'Course files_pack'=>'course_files_pack',  

                );
                $masterstudy_section_meta_fields = array(
                    'Curriculum' => 'curriculum',
                    'Lesson Name' => 'lesson_name',
                    'Quiz Name' => 'quiz_name',   
                    'Quiz Id' => 'quiz_id',  
                    'Lesson Id' => 'lesson_id',                            
                      );    


                      if($mode == 'Insert'){

                        unset($learn_section_meta_fields['Lesson Id']);
                        unset($learn_section_meta_fields['Quiz Id']);                     

                            }

           }

            if($import_type == 'stm-lessons'){            
                $masterstudy_meta_fields = array(
                            'Type' => 'type',
                            'Duration' => 'duration',  
                            'Preview' => 'preview', 
                            'Lesson Excerpt' => 'lesson_excerpt',
                            'Thumbnail id' => '_thumbnail_id',
                            'Video type' => 'video_type',
                            'Lesson youtube url' => 'lesson_youtube_url',
                            'Presto player idx' => 'presto_player_idx',
                            'Lesson video' => 'lesson_video',
                            'Lesson video_poster' => 'lesson_video_poster',
                            'Lesson video_width' => 'lesson_video_width',
                            'Lesson shortcode' => 'lesson_shortcode',
                            'Lesson embed_ctx' => 'lesson_embed_ctx',
                            'Lesson stream_url' => 'lesson_stream_url',
                            'Lesson vimeo_url' => 'lesson_vimeo_url',
                            'Lesson ext_link_url' => 'lesson_ext_link_url',
                            'Lesson files pack' => 'lesson_files_pack'

                        );

            }
            if($import_type == 'stm-quizzes'){            
                $masterstudy_meta_fields = array(
                            'Duration' => 'duration',
                            'Thumbnail_id ' => 'thumbnail_id ',
                            'Lesson Excerpt' => 'lesson_excerpt',
                            'Quiz style' => 'quiz_style',
                            'Correct answer' => 'correct_answer',
                            'Passing grade' => 'passing_grade',
                            'Re take cut' => 're_take_cut',
                            'Random_questions' => 'random_questions',
                            'Questions' => 'questions',
                        );

                if($mode == 'Insert'){
                    unset($masterstudy_meta_fields['Question Id']);
                }
            }
            if($import_type == 'stm-questions'){           
                $masterstudy_meta_fields = array(
                            'Type' => 'type',
                            'Answers' => 'answers',
                            'Question explanation' => 'question_explanation',
                            'Question' => 'question',
                            'Question hint' => 'question_hint',
                            'question view type' => 'question_view_type',
                            'Image' => 'image'
                        );

            }
            if($import_type == 'stm-orders'){
                $masterstudy_meta_fields = array(
                    'Status' => 'status',     
    
                );

            }
        }
        $masterstudy_meta_fields_line = $this->convert_static_fields_to_array($masterstudy_meta_fields);

        if($data == 'stm-courses'){
            $masterstudy_section_meta_fields_line = $this->convert_static_fields_to_array($masterstudy_section_meta_fields);

            $response['course_settings_fields_stm'] = $masterstudy_meta_fields_line; 

            $response['curriculum_settings_fields_stm'] = $masterstudy_section_meta_fields_line;  


        }
        if($data == 'stm-lessons'){
            $response['lesson_settings_fields_stm'] = $masterstudy_meta_fields_line; 
        }
        if($data == 'stm-quizzes'){
            $response['quiz_settings_fields_stm'] = $masterstudy_meta_fields_line; 
        }  
        if($data == 'stm-questions'){
            $response['question_settings_fields_stm'] = $masterstudy_meta_fields_line; 
        }  
        if($data == 'stm-orders'){
            $response['order_settings_fields_stm'] = $masterstudy_meta_fields_line; 

        } 
		return $response;
			
    }

    public function extensionSupportedImportType($import_type ){
        if(is_plugin_active('masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php')){
            if($import_type == 'stm-courses' || $import_type == 'stm-lessons' || $import_type == 'stm-quizzes' || $import_type == 'stm-questions' || $import_type == 'stm-orders') { 
                return true;

            }else{
                return false;
            }
        }
	}
}   
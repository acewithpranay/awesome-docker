<?php

class awesome_wp_utils{
	
static function pippin_excerpt_by_id($post, $length = 20, $tags = '<a><em><strong>', $extra = '') {
	//php8amit		
	/*
	 * Gets the excerpt of a specific post ID or object
	 * @param - $post - object/int - the ID or object of the post to get the excerpt of
	 * @param - $length - int - the length of the excerpt in words
	 * @param - $tags - string - the allowed HTML tags. These will not be stripped out
	 * @param - $extra - string - text to append to the end of the excerpt
	 */

	if (is_int($post)) {
		// get the post object of the passed ID
		$post = get_post($post);
	} elseif (!is_object($post)) {
		return false;
	}

	if (has_excerpt($post->ID)) {
		$the_excerpt = $post->post_excerpt;
		return apply_filters('the_content', $the_excerpt);
	} else {
		$the_excerpt = $post->post_content;
	}

	$the_excerpt = strip_shortcodes(strip_tags($the_excerpt), $tags);
	$the_excerpt = preg_split('/\b/', $the_excerpt, $length * 2 + 1);
	$excerpt_waste = array_pop($the_excerpt);
	$the_excerpt = implode($the_excerpt);
	$the_excerpt .= $extra;

	return apply_filters('the_content', $the_excerpt);
}

static function the_content_filter($content){
	//php8amit		
		global $wp_embed;
		$has_blocks = has_blocks($content);	
		
		if($has_blocks){ 
			$content = do_blocks($content);
		}
				
		$content = wptexturize($content);
		$content = convert_smilies($content);
		if(!$has_blocks){ //workaround for stray closing p tags in GT blocks.
			$content = wpautop($content);
		}
		$content = shortcode_unautop($content);
		$content = prepend_attachment($content);
		$content = wp_filter_content_tags($content);
		$content = str_replace(']]>', ']]&gt;', $content);
		$content = $wp_embed->autoembed( $content );
		
		return $content;
	}

static function register_module($post_type,$sing_name,$pl_name,$desc='',$supports=null){
	//php8amit		
		if(!$supports)$supports = array('title','editor','revisions');
		
		$capabilities = array(
			"edit_post"=>"develop_for_awesomeui",
			"read_post"=>"develop_for_awesomeui",
			"delete_post"=>"develop_for_awesomeui",
			"edit_posts"=>"develop_for_awesomeui",
			"edit_others_posts"=>"develop_for_awesomeui",
			"publish_posts"=>"develop_for_awesomeui",
			"read_private_posts"=>"develop_for_awesomeui",
			"delete_posts"=>"develop_for_awesomeui"
			
		);
		
		register_post_type($post_type, array(
			'label' => $pl_name,
			'description' => $desc,
			'public' =>false,
			'show_in_nav_menus'=>false,
			'show_ui' => true,
			'show_in_menu' => false,
			'delete_with_user'    => false,
			'capability_type'			=> 'post',
			'capabilities' => $capabilities,
			'hierarchical' => false,
			'query_var' => false,
			'rewrite' => false,
			'supports' => $supports,
			'labels' => array (
				  'name' => $pl_name,
				  'singular_name' => $sing_name,
				  'menu_name' => $pl_name,
				  'add_new' => 'Create '.$sing_name,
				  'add_new_item' => 'Add New '.$sing_name,
				  'new_item' => 'New '.$sing_name,
				  'edit' => 'Edit '.$sing_name,
				  'edit_item' => 'Edit '.$sing_name,
				  'view' => 'View '.$sing_name,
				  'view_item' => 'View '.$sing_name,
				  'search_items' => 'Search '.$pl_name,
				  'not_found' => 'No '.$sing_name.' Found',
				  'not_found_in_trash' => 'No '.$sing_name.' Found in Trash'
				)
			) 
		);
	}
	

	
}
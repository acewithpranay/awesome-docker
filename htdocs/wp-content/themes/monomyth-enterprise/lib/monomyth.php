<?php
/*
* This file initialises all the libraries included in the theme and does all the basic setup that are needed. 
*
*
*/

//enqueue required basic scripts and styles -- bootstrap css, js and app and js
//if ( !defined('MM_PRODUCTION') )
//	define('MM_PRODUCTION', false);
	
// clean ups taken from roots and bones theme framework

require( 'clean-up.php' ); 
require( 'nice-search.php' ); 
require( 'relative-urls.php' ); 
require( 'admin-cleanup.php' ); 


add_action( 'enqueue_block_editor_assets', function() {
  wp_enqueue_style( 'awesome-css', site_url('site-skin/css/awesome-css'), false, '1.0', 'all' );
  wp_enqueue_style( 'awesome-editor-styles', site_url('site-skin/css/editor-styles'), false, '1.0', 'all' );

},1 );

// launching this stuff after theme setup
add_action( 'after_setup_theme','monomyth_theme_support' );

function monomyth_theme_support(){

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'jquery-cdn' );
	add_theme_support( 'title-tag' );
	/* Adds core WordPress HTML5 support. */
	add_theme_support( 'html5', array( 'caption', 'comment-form', 'comment-list', 'gallery', 'search-form' ) );
	/* Make text widgets shortcode aware. */
	add_filter( 'widget_text', 'do_shortcode' );

	/* Don't strip tags on single post titles. */
	remove_filter( 'single_post_title', 'strip_tags' );

	// wp menus
	add_theme_support( 'menus' );
	
	
	
	// Register wp_nav_menu() menus (http://codex.wordpress.org/Function_Reference/register_nav_menus)
	register_nav_menus(array(
		'primary_navigation' => __('Primary Navigation', 'monomyth'),
	));

}

function monomyth_widgets_init() {
  // Sidebars
  register_sidebar(array(
    'name'          => __('Primary', 'monomyth'),
    'id'            => 'sidebar-primary',
    'before_widget' => '<section class="widget %1$s %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>',
  ));

  register_sidebar(array(
    'name'          => __('Footer', 'monomyth'),
    'id'            => 'sidebar-footer',
    'before_widget' => '<section class="widget %1$s %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>',
  ));

}
add_action('widgets_init', 'monomyth_widgets_init');

function monomyth_scripts() {
  global $wp_styles;
  global $wp_scripts;
  global $monomyth_options;

	 wp_enqueue_style('monomyth_ie', get_template_directory_uri() . '/assets/ie.css', false, null);
	 $wp_styles->add_data( 'monomyth_ie', 'conditional', 'lt IE 10' ); // add conditional wrapper around ie stylesheet
  // jQuery is loaded using the same method from HTML5 Boilerplate:
  // Grab Google CDN's latest jQuery with a protocol relative URL; fallback to local if offline
  // It's kept in the header instead of footer to avoid conflicts with plugins.
  if (!is_admin() && current_theme_supports('jquery-cdn')) {
    wp_deregister_script('jquery');
    //wp_register_script('jquery', 'https://cdn.loantap.in/jquery/1.11.3/jquery.min.js', array(), null, false);
    //wp_register_script('jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js', array(), null, false);
    wp_register_script('jquery', false, array(), null, false);	  
  }

  if (is_single() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }

  wp_enqueue_script('jquery');
  
  
}
add_action('wp_enqueue_scripts', 'monomyth_scripts', 100);




function add_filters($tags, $function) {
  foreach($tags as $tag) {
    add_filter($tag, $function);
  }
}

//--- theme activation ---
function monomyth_theme_activation_action(){

}
add_action('admin_init','monomyth_theme_activation_action');


function monomyth_less_path(){
	return get_template_directory().'/assets/css-cache';
}
add_filter('wp_less_cache_path','monomyth_less_path');


function monomyth_less_url(){
	return get_template_directory_uri().'/assets/css-cache';
}
add_filter('wp_less_cache_url','monomyth_less_url');

// Add thumbnail class to thumbnail links
function monomyth_add_class_attachment_link( $html ) {
    $postid = get_the_ID();
    $html = str_replace( '<a','<a class="thumbnail"',$html );
    return $html;
}
add_filter( 'wp_get_attachment_link', 'monomyth_add_class_attachment_link', 10, 1 );

add_filter( 'clean_url', function( $url )
{
    if(strpos( $url, 'bootstrap.min.js' )) {
		// Must be a ', not "!
		return "$url' defer='defer";
	}
	// not our file
    return $url;
}, 11, 1 );


if (!is_admin()) {
	// default URL format
	$query_string= isset($_SERVER['QUERY_STRING'])? $_SERVER['QUERY_STRING']:'';
	if (preg_match('/author=([0-9]*)/i', $query_string)){ 
		wp_die('forbidden');
	}
	
	$request_uri = isset($_SERVER['REQUEST_URI'])? $_SERVER['REQUEST_URI']:'';
	$author = isset($_REQUEST['author'])? $_REQUEST['author']:'';
	
	if(preg_match('/(wp-comments-post)/', $request_uri) === 0 && !empty($author) ) {
	   /*  openlog('wordpress('.$_SERVER['HTTP_HOST'].')',LOG_NDELAY|LOG_PID,LOG_AUTH);
		syslog(LOG_INFO,"Attempted user enumeration from {$_SERVER['REMOTE_ADDR']}");
		closelog(); */
		wp_die('forbidden');
    }
	add_filter('redirect_canonical', 'shapeSpace_check_enum', 10, 2);
}

function shapeSpace_check_enum($redirect, $request) {
	// permalink URL format
	if (preg_match('/\?author=([0-9]*)(\/*)/i', $request)) die();
	else return $redirect;
}


add_filter( 'rest_endpoints', function( $endpoints ){
    // if user is logged in don't do any thing.
    if (is_user_logged_in() ) {
      return $endpoints;
    }

    if ( isset( $endpoints['/wp/v2/users'] ) ) {
        unset( $endpoints['/wp/v2/users'] );
    }
    if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
});

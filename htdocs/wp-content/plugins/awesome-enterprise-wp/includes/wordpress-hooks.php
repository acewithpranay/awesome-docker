<?php

	//add wp actions
	add_action('plugins_loaded','Monoframe::setup_constants',1);
	add_action('plugins_loaded','awesome_flow::env_setup',2);
	add_action('init','apps_setup_wp::wp_init',10);
	add_action('init','awesome_flow::init',11);
	add_action('admin_init','apps_setup_wp::admin_init',1);
	add_action( 'parse_request', 'awesome_flow::app_takeover' );


	add_action('generate_rewrite_rules', 'apps_setup_wp::app_slug_rewrite');
	add_filter( 'post_type_link', 'apps_setup_wp::fix_app_slug', 10, 3 );
	
	add_action('wp_head', 'awesome_flow::head');
	add_action('wp_footer', 'awesome_flow::footer');
	add_filter( 'nav_menu_css_class', 'apps_setup_wp::nav_menu_css_class', 10, 3 );
	
	add_action('wp_login', 'awesome_auth::wp_vession_login',10, 2);
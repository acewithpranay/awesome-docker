<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="utf-8">
	<?php // Google Chrome Frame for IE ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no"/>
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<link href="https://cdn.getawesomestudio.com" rel="preconnect" crossorigin>
<!--
 __          __  _____                   _                                        
 \ \        / / |  __ \                 | |                                       
  \ \  /\  / /  | |__) |   ___     ___  | |_   ___        ___    ___    _ __ ___  
   \ \/  \/ /   |  ___/   / _ \   / _ \ | __| / __|      / __|  / _ \  | '_ ` _ \ 
    \  /\  /    | |      | (_) | |  __/ | |_  \__ \  _  | (__  | (_) | | | | | | |
     \/  \/     |_|       \___/   \___|  \__| |___/ (_)  \___|  \___/  |_| |_| |_|
-->
	<?php
	if(aw2_library::get('settings.opt-favicon.exists')){?>
	<link rel="icon" href="<?php
		echo aw2_library::get('settings.opt-favicon');
	 ?>"> 
	<?php
	}
	if(aw2_library::get('settings.opt-favicon.exists')){
		?>
	<!--[if IE]>
		<link rel="shortcut icon" href="<?php
		echo aw2_library::get('settings.opt-favicon');
	 ?>">	
	<?php 	
	}
	?>
	<![endif]-->
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
	<?php 
	
	wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="background_ovelay"></div>
<?php

$app=&aw2_library::get_array_ref('app');

$module='header';
if(basename( get_page_template() )==='page-landing.php') $module='landing-page-header';

$post_type=AWESOME_CORE_POST_TYPE; 
if(isset($app['collection']['config']['post_type']) && \aw2_library::post_exists($module,$app['collection']['config']['post_type'])){
	$post_type=$app['collection']['config']['post_type'];
}	

echo \aw2_library::module_run(['post_type'=>$post_type],$module);
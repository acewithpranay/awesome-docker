<?php
/**
   Template Name: Default Template For Content
 * The template for displaying all pages.
 */

get_header(); ?>
<div class="container-fluid no-padding">
<div class="content row no-gutters">
	<main class="main  col-lg-12 col-md-12 col-sm-12 col-xs-12" role="main">
		<?php
		
		$post_type=AWESOME_CORE_POST_TYPE;
		$module ='';
	
		if(\aw2_library::post_exists('default-page-layout',$post_type)){
			$module = 'default-page-layout';
		}
		
		if(!empty($module))		
			echo \aw2_library::module_run(['post_type'=>$post_type],$module);
		else
			echo '<em>default-page-layout</em> is missing.';

		?>
	</main><!-- /.main -->
</div><!-- /.content -->
</div><!--#container -->
<?php get_footer(); 
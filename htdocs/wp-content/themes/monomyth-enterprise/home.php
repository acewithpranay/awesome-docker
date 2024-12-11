<?php
/**
 * The template for displaying all pages.
 */

get_header(); ?>
<div class="container-fluid no-padding">
<div class="content row no-gutters">
	<main class="main  col-lg-12 col-md-12 col-sm-12 col-xs-12" role="main">
	<?php 	
		
		$post_type=AWESOME_CORE_POST_TYPE;
		$module ='';
	
		if(\aw2_library::post_exists('blog-content-layout',$post_type)){
			$module = 'blog-content-layout';
		} else if(\aw2_library::post_exists('archive-content-layout',$post_type)) {
			$module = 'archive-content-layout';
		}
		
		if(!empty($module))		
			echo \aw2_library::module_run(['post_type'=>$post_type],$module);
		else
			echo '<em>blog-content-layout/archive-content-layout</em> is missing in core.';
		
		
	?>
	</main><!-- /.main -->
</div><!-- /.content -->
</div><!--#container -->
<?php get_footer(); 
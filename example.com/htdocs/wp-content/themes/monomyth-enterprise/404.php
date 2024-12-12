<?php
/**
 * The template for displaying 404 page.
 */

get_header(); ?>
<div class="container-fluid no-padding">
	<div class="content row no-gutters">
		<main class="main col-sm-12 col-xs-12 " role="main">
			<?php
		 
			$post_type=AWESOME_CORE_POST_TYPE;
			$module ='404-layout';
			
			if(\aw2_library::post_exists('404-layout',$post_type)) {
				echo \aw2_library::module_run(['post_type'=>$post_type],$module);
			} 
			else
				echo '<em>404-layout</em> is missing.';
	
			?>
		</main><!-- /.main -->
	</div><!-- /.content -->
</div>

<?php get_footer(); ?>
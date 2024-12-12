<?php
/**
 * The template for displaying search results page.
 */

get_header(); ?>
<div class="container-fluid no-padding">
	<div class="content row no-gutters">
		<main class="main col-sm-12 col-xs-12 " role="main">
		<?php
			aw2_library::set('search_term',get_search_query());
			
			if(\aw2_library::post_exists('search',AWESOME_CORE_POST_TYPE)){
				echo \aw2_library::module_run(['post_type'=>AWESOME_CORE_POST_TYPE],'search');
			}	
			else{
				echo 'search module does not exists in '.AWESOME_CORE_POST_TYPE;
			}
	
		?>
			
		</main><!-- /.main -->
	</div><!-- /.content -->
</div>

<?php get_footer(); ?>
<?php
/**
 * The template for displaying all pages.
 */

get_header(); ?>
<div class="container-fluid no-padding">
	<div class="content row no-gutters">
		<div class="main col-sm-12 col-xs-12" >
			<?php
		
			$single_post_type = get_query_var('post_type');

			$app=&aw2_library::get_array_ref('app');
			aw2_library::set('current_post',$post);
			
			$post_type='';
			$module ='single-content-layout';
			
			
			while ( have_posts() ) : the_post();		
				
				if(\aw2_library::post_exists($single_post_type.'-single-content-layout',$app['collection']['config']['post_type'])){
					$module=$single_post_type.'-single-content-layout';
					$post_type=$app['collection']['config']['post_type'];
				}
				else if(\aw2_library::post_exists($single_post_type.'-single-content-layout',AWESOME_CORE_POST_TYPE)){
					$module=$single_post_type.'-single-content-layout';
					$post_type=AWESOME_CORE_POST_TYPE;
				}
				else if(\aw2_library::post_exists($module,$app['collection']['config']['post_type'])){
					$post_type=$app['collection']['config']['post_type'];
				}
				else if(\aw2_library::post_exists($module,AWESOME_CORE_POST_TYPE)){
					$post_type=AWESOME_CORE_POST_TYPE;
				}
					
				if(!empty($post_type))		
					echo \aw2_library::module_run(['post_type'=>$post_type],$module);
				else
					echo '<em>single-content-layout</em> is missing.';
				
			endwhile; // end of the loop. ?>
		</div><!-- /.main -->
	</div><!-- /.content -->
</div>
<?php get_footer(); 

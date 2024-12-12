<?php
/**
 * The template for displaying all pages.
 */

get_header(); ?>
<div class="container-fluid no-padding">
	<div class="content row no-gutters">
		<main class="main col-md-12 col-xs-12" role="main">
			<?php 
				
			$app=&aw2_library::get_array_ref('app');
			
			$post_type='';
			$module ='';
						
			
			
			if(\aw2_library::post_exists('archive-content-layout',AWESOME_CORE_POST_TYPE)){
					$post_type=AWESOME_CORE_POST_TYPE;
					$module='archive-content-layout';
			}
			
			if(\aw2_library::post_exists('archive-content-layout',$app['collection']['config']['post_type'])){

				$post_type=$app['collection']['config']['post_type'];
				$module='archive-content-layout';
			}
			
			if(is_post_type_archive( ))
			{
				$active_post_type = get_query_var('post_type');
				aw2_library::set('current_archive_name',$active_post_type);

				if(\aw2_library::post_exists($active_post_type . '-archive-content-layout',AWESOME_CORE_POST_TYPE)){
					
					$module=$active_post_type . '-archive-content-layout';
					$post_type=AWESOME_CORE_POST_TYPE; 
					
				}
			}
			else if(is_tax())
			{
				$tax = $wp_query->get_queried_object();
				aw2_library::set('default_taxonomy',$tax->taxonomy);
				aw2_library::set('default_term_slug',$tax->slug);
				aw2_library::set('current_archive_name',$tax->name);
				aw2_library::set('default_term_id',$tax->term_id);

				if(\aw2_library::post_exists($tax->taxonomy  . '-archive-content-layout',AWESOME_CORE_POST_TYPE)){
					$post_type=AWESOME_CORE_POST_TYPE;
					$module=$tax->taxonomy  . '-archive-content-layout';
				}
			}
			else if(is_category()){
				$cat = get_category( get_query_var( 'cat' ) );
				aw2_library::set('default_taxonomy','category');
				aw2_library::set('default_term_slug',$cat->slug);
				aw2_library::set('current_archive_name',$cat->name);
				aw2_library::set('default_term_id',$cat->term_id);
		
				if(\aw2_library::post_exists($cat->slug  . '-archive-content-layout',AWESOME_CORE_POST_TYPE)){
					$post_type=AWESOME_CORE_POST_TYPE;
					$module=$cat->slug  . '-archive-content-layout';
				}
			}
			else if( is_tag()){
				
				$tax = $wp_query->get_queried_object();
				aw2_library::set('default_taxonomy',$tax->taxonomy);
				aw2_library::set('default_term_slug',$tax->slug);
				aw2_library::set('current_archive_name',$tax->name);
				aw2_library::set('default_term_id',$tax->term_id);
				
				
				if(\aw2_library::post_exists($tax->taxonomy  . '-archive-content-layout',AWESOME_CORE_POST_TYPE)){
					$post_type=AWESOME_CORE_POST_TYPE;
					$module=$tax->taxonomy  . '-archive-content-layout';
				}
			}
			else if( is_author()){
			
				if(get_query_var('author_name')) :
					$curauth = get_user_by('slug', get_query_var('author_name'));
				else :
					$curauth = get_userdata(get_query_var('author'));
				endif;

				aw2_library::set('current_author_id',$curauth->ID);
				aw2_library::set('current_author_slug',$curauth->user_login);
				aw2_library::set('current_author_name',$curauth->display_name);
				aw2_library::set('current_author',$curauth);

				if(\aw2_library::post_exists('author-archive-content-layout',AWESOME_CORE_POST_TYPE)){
					$post_type=AWESOME_CORE_POST_TYPE;
					$module='author-archive-content-layout';
				}
			}
			else if( is_date()){
	
				if(\aw2_library::post_exists('date-archive-content-layout',AWESOME_CORE_POST_TYPE)){
					$post_type=AWESOME_CORE_POST_TYPE;
					$module='date-archive-content-layout';
				}
			}

			if(!empty($post_type) && !empty($module)){
				echo \aw2_library::module_run(['post_type'=>$post_type],$module);
			}
			else
				echo '<em> archive-content-layout </em> is missing.';
				
			?>
		</main><!-- /.main -->
	</div><!-- /.content -->
</div>
<?php get_footer(); 



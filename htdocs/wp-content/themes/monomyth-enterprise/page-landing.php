<?php
/**
 Template Name: Landing Page
 * The template for displaying landing pages you have to create separate header 'landing-page-header' & footer 'landing-page-footer' module in awesome core.
 */

get_header(); ?>
<div class="container-fluid no-padding">
<div class="content row no-gutters">
	<main class="main  col-lg-12 col-md-12 col-sm-12 col-xs-12" role="main">
		<?php while ( have_posts() ) : the_post();?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="entry-content">
				<?php the_content(); ?>
			</div><!-- .entry-content -->
			<footer class="entry-footer"></footer>
		</article><!-- #post-## -->
		<?php endwhile; // end of the loop. ?>
	</main><!-- /.main -->
</div><!-- /.content -->
</div><!--#container -->
<?php 
get_footer(); 
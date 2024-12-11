<?php
/**
 Template Name: Blank Page - No Page Title
 * The template for displaying all pages.
 */

get_header(); ?>
<div class="container-fluid no-padding">
<div class="content row no-gutters">
	<main class="main  col-lg-12 col-md-12 col-sm-12 col-xs-12" role="main">
		<?php while ( have_posts() ) : the_post();?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="entry-content">
				<?php the_content(); ?>
				<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . __( 'Pages:', '_s' ),
						'after'  => '</div>',
					) );
				?>
			</div><!-- .entry-content -->
		</article><!-- #post-## -->

		<?php endwhile; // end of the loop. ?>
	</main><!-- /.main -->
</div><!-- /.content -->
</div><!--#container -->
<?php get_footer(); ?>
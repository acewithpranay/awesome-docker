<?php
/**
 Template Name: Page With Default Sidebar
 * The template for displaying all pages.
 */

get_header(); ?>
<div class="container no-padding">
<div class="content row no-gutters">
	<main class="main  col-lg-8 col-md-8 col-sm-8 col-xs-12" role="main">
		<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<?php the_content(); ?>
				<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . __( 'Pages:', '_s' ),
						'after'  => '</div>',
					) );
				?>
			</div><!-- .entry-content -->
			<footer class="entry-footer">
			<?php edit_post_link('Edit', '<span class="edit-link">', '</span>' ); ?>
			</footer>
		</article><!-- #post-## -->

		<?php endwhile; // end of the loop. ?>
	</main><!-- /.main -->
	<aside class="sidebar  col-lg-4 col-md-8 col-sm-8 col-xs-12" role="complementary">
		<?php get_sidebar(); ?>
	</aside><!-- /.sidebar -->

</div><!-- /.content -->
</div><!--#container -->
<?php get_footer(); ?>
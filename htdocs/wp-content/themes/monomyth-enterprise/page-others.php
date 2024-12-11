<?php
/**
 Template Name: Other normal pages
 * The template for displaying all pages.
 */

get_header(); ?>
<div class="container-fluid brand-white-bg is-relative" style='z-index:2'>
<div class="content row justify-content-center">
	<main class="main col-12 col-sm-8 " role="main">
		<?php while ( have_posts() ) : the_post();  ?>
		<div class='gap-5'></div>
		<article id="post-<?php the_ID(); ?>" class='post-<?php the_ID(); ?> brand-white-bg pad-y-6 pad-x-6' style='1px solid #cfd8dc'>
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
		<div class='gap-5'></div>
		<?php endwhile; // end of the loop. ?>
	</main><!-- /.main -->

</div><!-- /.content -->
</div><!--#container -->
<?php get_footer(); ?>
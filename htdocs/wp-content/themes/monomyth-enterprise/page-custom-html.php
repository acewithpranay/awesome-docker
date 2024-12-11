<?php
/**
 Template Name: Page for Custom HTML
 * The template for full HTML.
 */
while ( have_posts() ) : the_post(); aw2_library::set('current_post',$post);
the_content();
endwhile;

<?php
namespace aw2\acf_blocks;

add_action('init','aw2\acf_blocks\setup_acf_blocks',1);
add_action('acf/init', 'aw2\acf_blocks\register_acf_blocks',15);



if ( version_compare( $GLOBALS['wp_version'], '5.8-alpha-1', '<' ) ) {
    add_filter( 'block_categories', 'aw2\acf_blocks\add_block_category', 10, 2 );
} else {
    add_filter( 'block_categories_all', 'aw2\acf_blocks\add_block_category', 10, 2 );
}


function setup_acf_blocks(){
	\awesome_flow::run_core('gutenberg-blocks');
}


function register_acf_blocks(){
	$gutenberg_blocks=&\aw2_library::get_array_ref('gutenberg_blocks');
	if( function_exists('acf_register_block_type') ){
		  
		  foreach($gutenberg_blocks as $key=>$block){
		    acf_register_block_type($block);
			 unset($gutenberg_blocks[$key]);
		  }
		  
		 
	}
}


 
/**
 * Adding a new (custom) block category.
 *
 * @param   array                   $block_categories       Array of categories for block types.
 * @param   WP_Block_Editor_Context $block_editor_context   The current block editor context.
 */
function add_block_category( $block_categories, $block_editor_context ) {
    return array_merge(
        array(
            array(
                'slug'  => 'awesome-gt-blocks',
                'title' => __( 'Awesome UI Blocks', 'text-domain' ),
                'icon' =>'schedule'
            ),
        ),
		$block_categories
    );
}

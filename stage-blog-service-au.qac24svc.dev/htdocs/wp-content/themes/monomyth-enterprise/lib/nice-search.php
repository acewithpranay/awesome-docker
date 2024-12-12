<?php
/**
 * Redirects search results from /?s=query to /search/query/, converts %20 to +
 *
 * @link http://txfx.net/wordpress-plugins/nice-search/
 */
function soil_nice_search_redirect() {
  global $wp_rewrite;
  if (!isset($wp_rewrite) || !is_object($wp_rewrite) || !$wp_rewrite->using_permalinks()) {
    return;
  }

  $search_base = $wp_rewrite->search_base;
  if (is_search() && !is_admin() && strpos($_SERVER['REQUEST_URI'], "/{$search_base}/") === false) {
    $post_type='';
    $order_by="";
	 if(isset($_REQUEST['post_type']) && is_array($_REQUEST['post_type'])){
			$post_type= '?'.http_build_query(array('post_type'=>$_REQUEST['post_type']));
	 }
   if(isset($_REQUEST['orderby'])){
    $order_by="?";  
    if(!empty($post_type)) $order_by="&";
      $order_by .= http_build_query(array('orderby'=>$_REQUEST['orderby']));
  }
    
    wp_redirect(home_url("/{$search_base}/" . urlencode(get_query_var('s'))));
    exit();
  }
}
add_action('template_redirect', 'soil_nice_search_redirect');

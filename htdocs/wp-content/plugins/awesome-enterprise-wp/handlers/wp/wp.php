<?php
namespace aw2\wp;

\aw2_library::add_service('wp.signon','Sign in a User',['namespace'=>__NAMESPACE__]);


function signon($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)== false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'username' =>null,
	'password'=>null,
	), $atts) );
	
	$creds = array();
	$creds['user_login'] = $username;
	$creds['user_password'] = $password;
	$user = wp_signon( $creds, false );
	
	$return_value='yes';
	if ( is_wp_error($user) )
		$return_value='no';
	else
		wp_set_current_user($user->ID);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('wp.menu','Get Menu',['namespace'=>__NAMESPACE__]);


function menu($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)== false)return;
	
	$args=\aw2_library::get_clean_args($content);
	$return_value='';
	$return_value=wp_nav_menu( $args );
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('wp.get','Get various WordPress related values.',['func'=>'_wget','namespace'=>__NAMESPACE__]);

function _wget($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)== false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'default'=>''
	), $atts, 'wp.get' ) );
	
	if(empty($main)) return 'error: You must have main attribute to get the data with wp.get shortcode';
	if(is_array($main))return 'error: array was passed to get';
	if(is_object($main))return 'error: object was passed to get';
	
	
	$pieces=explode('.',$main); // to support [wp.get option.xyz.y /] 
	$return_value='';
	$action = array_shift($pieces);
	
	$aw2wp_get=new aw2wp_get($action,$atts,$content,$pieces);
	if($aw2wp_get->status==false){
		return \aw2_library::get('errors');
	}
	
	$return_value = $aw2wp_get->run();
	if($return_value==='')$return_value=$default;
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

class aw2wp_get{ 
	
	public $action=null;
	public $pieces=null;
	public $atts=null;
	public $content=null;
	public $status=false;
	public $value='';
	
	function __construct($action,$atts,$content=null,$main_piece=null){
     if (method_exists($this, $action)){
		$this->action=$action;
		$this->pieces=$main_piece;
		$this->atts=$atts;
		$this->content=is_null($content)?'':trim($content);
		$this->status=true;
	 }
	}
	
	function run(){
     if (method_exists($this, $this->action)){
		
		$this->value = call_user_func(array($this, $this->action));
		
		if(!is_array($this->pieces))
			$this->pieces=array();
		
		while(count($this->pieces)>0) {
			if(is_object($this->value)){
				\aw2_library::resolve_object($this);
			}	
			elseif(is_array($this->value) ){
				\aw2_library::resolve_array($this);
			}
			elseif(is_string($this->value) || is_bool($this->value) || is_numeric($this->value)){
				$this->value = \aw2_library::resolve_string($this);
			}
			else{
				$this->value='';
				$this->pieces=array();
			}
		}

		return $this->value;
	 }	
     else
		\aw2_library::set_error('Register Method does not exist'); 
	}
	
	function att($el,$default=null){
		if(array_key_exists($el,$this->atts))
			return $this->atts[$el];
		return $default;
	}

	function args(){
		if($this->content==null || $this->content==''){
			$return_value=array();	
		}
		else{
			$json=\aw2_library::clean_specialchars($this->content);
			$json=\aw2_library::parse_shortcode($json);		
			$return_value=json_decode($json, true);
			if(is_null($return_value)){
				\aw2_library::set_error('Invalid JSON' . $this->content); 
				$return_value=array();	
			}
		}

		$arg_list = func_get_args();
		foreach($arg_list as $arg){
			if(array_key_exists($arg,$this->atts))
				$return_value[$arg]=$this->atts[$arg];
		}
			return $return_value;
	}
	
	
	function image_alt(){
		
		if(empty( $this->att('attachment_id')) && empty($this->att('post_id'))){
			\aw2_library::set_error('Either one of attachment_id and post_id is required'); 
			return '';
		}
		
		$attachment_id = $this->att('attachment_id');
		if(empty($attachment_id)) {
			$attachment_id=get_post_thumbnail_id( $this->att('post_id') ); 
		}
		
		return trim(strip_tags( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ));;
	}


	function attachment(){
		$attachment_id = $this->att('attachment_id'); 
		if(empty( $attachment_id )){
			\aw2_library::set_error('attachment_id is required'); 
			return '';
		}
		
		$return_value=array();
		$return_value['name']=get_the_title($attachment_id);
		$return_value['url']=wp_get_attachment_url($attachment_id);
		$return_value['alt'] = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true);
		$return_value['path']  = get_attached_file( $attachment_id);
		$return_value['meta']  = wp_get_attachment_metadata( $attachment_id);
		
		return $return_value;
	}

	function breadcrumb(){
		
		$sep = $this->att('seperator');
		if(empty($sep))
			$sep = '&raquo;';
			
		$show_home = $this->att('show_home');
		if(empty($show_home))
			$show_home = 'yes';
			
		$value = "<div class='breadcrumb'>".get_breadcrumb($o->atts['main_menu_slug'], $sep,$show_home)."</div>";
		return $value;
	}	
	
	function next_post(){
		
		$post_id=$this->att('post_id');
		
		if(empty($post_id)){
			\aw2_library::set_error('post_id is required'); 
			return '';
		}
		$in_same_cat=false;
		
		if(!empty($this->att('in_same_cat')) && strtolower($this->att('in_same_cat'))=='true'){
			$in_same_cat=true;
		}
		
		$out="id";
		
		if(!empty($this->main_piece))
			$out=$this->main_piece;
			
		//get_{$adjacent}_post_where
		$all=false;
		if(!empty($this->att('take_all_post'))){
			 $all=true;
		}
		 
		
		 // Get a global post reference since get_adjacent_post() references it
		global $post,$wpdb;

		// Store the existing post object for later so we don't lose it
		$oldGlobal = $post;

		// Get the post object for the specified post and place it in the global variable
		$post = get_post( $post_id );

		// Get the post object for the previous post
		$next_post = get_adjacent_post($all,$in_same_cat,'',false);

		// Reset our global object
		$post = $oldGlobal;

		if ( '' == $next_post ) {
			$next_post_id = 0;
		}

		$return_value = $next_post->ID;
		
		if($out == 'url'){
			$return_value = \get_permalink($next_post);
		}
		
		if($out == 'slug'){
			$return_value = $next_post->post_name;
		}
		
		return $return_value;
	}

	function prev_post(){
		$post_id=$this->att('post_id');
		
		if(empty($post_id)){
			\aw2_library::set_error('post_id is required'); 
			return '';
		}
		$in_same_cat=false;
		
		if(!empty($this->att('in_same_cat')) && strtolower($this->att('in_same_cat'))=='true'){
			$in_same_cat=true;
		}
		
		$out="id";
		
		if(!empty($this->main_piece))
			$out=$this->main_piece;
			
		//get_{$adjacent}_post_where
		$all=false;
		if(!empty($this->att('take_all_post'))){
			 $all=true;
		}
		
		 // Get a global post reference since get_adjacent_post() references it
		global $post;

		// Store the existing post object for later so we don't lose it
		$oldGlobal = $post;

		// Get the post object for the specified post and place it in the global variable
		$post = get_post( $post_id );
		
		
		// Get the post object for the previous post
		$prev_post = get_adjacent_post($all,$in_same_cat,'',true);

		// Reset our global object
		$post = $oldGlobal;

		if ( '' == $prev_post ) {
			$next_post_id = 0;
		}

		$return_value = $prev_post->ID;
		
		if($out == 'url'){
			$return_value = get_permalink($prev_post);
		}
		
		if($out == 'slug'){
			$return_value = $prev_post->post_name;
		}
		
		return $return_value;
	}

	function sidebar(){
		/**
		 * 
		$main_piece=array_shift($this->pieces);	
		if(empty($main_piece)){
			\aw2_library::set_error('the format is sidebar.<sidebar_id or name> to get the sidebar'); 
			return '';
		}
		$output = '';
		ob_start();
			dynamic_sidebar( $main_piece );
		$output = ob_get_clean();

		return $output;
		 */
		$this->main_piece = array_shift($this->pieces);
		if(empty($this->main_piece)){
			\aw2_library::set_error('the format is sidebar.<sidebar_id or name> to get the sidebar'); 
			return '';
		}
		// Bail out, if there is no sidebar registered with given ID.
		if ( ! is_active_sidebar( $this->main_piece ) ) {
			return NULL;
		}
		$output = '';
		ob_start();
			dynamic_sidebar( $this->main_piece );
			$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	function sideload_media(){
		//php8amit		
		$url		= 	$this->att('url');
		$post_id	= $this->att('post_id');
		
		if(empty($url)){
			\aw2_library::set_error('url attribute is missing.'); 
			return '';
		}
		
		if(empty($post_id)){
			\aw2_library::set_error('sideload media requires post_id.'); 
			return '';
		}
		
		
		$return	=	(!empty($this->att('return'))) ? $this->att('return') : "src";
		
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		
		$output = media_sideload_image($url, $post_id,'',$return);

		return $output;
	}
	
	function option(){
		
		if(empty($this->pieces)){
			\aw2_library::set_error('the format is option.<key> to get the value'); 
			return '';
		}

		$option=array_shift($this->pieces);
		$value=get_option( $option );
		
		if($value===false){
			\aw2_library::set_error('Option '. $option.' dose not exists'); 
			$value='';
		}	
			
		return $value;
		
	}
	
	function attachment_url(){
		$attachment_id = $this->att('attachment_id'); 
		if(empty( $attachment_id )){
			\aw2_library::set_error('attachment_id is required'); 
			return '';
		}
		
		$size=(!empty($this->att('size')))?$this->att('size'):'thumbnail';
			
		$img=wp_get_attachment_image_src($attachment_id, $size );
		if($img === false){
			\aw2_library::set_error('No image is available'); 
			return '';
		}
		
		return $img[0]; 
	}
	
	function wpdb(){
		global $wpdb;
		return $wpdb;
	}
	function post(){
		global $post;
		return $post;
	}
	function wp_query(){
		global $wp_query;
		return $wp_query;
	}
	function current_user(){
		return wp_get_current_user();
	}
	function term_link(){
		$slug=$this->att('slug');
		if(empty($term_id)){
			\aw2_library::set_error('slug of term is required'); 
			return '';
		}
		
		$taxonomy=$this->att('taxonomy');
		if(empty($term_meta_key)){
			\aw2_library::set_error('taxonomy is required'); 
			return '';
		}
		
		return get_term_link($slug, $taxonomy );
	}
	function term_meta(){
		$term_id=$this->att('term_id');
		if(empty($term_id)){
			\aw2_library::set_error('term_id is required'); 
			return '';
		}
		
		$term_meta_key=$this->att('key');
		if(empty($term_meta_key)){
			\aw2_library::set_error('key is required'); 
			return '';
		}

		return get_term_meta($term_id, $term_meta_key, true);
	}
	function nonce(){
		$this->main_piece = array_shift($this->pieces);
		if(empty($this->main_piece)){
			\aw2_library::set_error('the format is nounce.<key> to get the value'); 
			return '';
		}
		
		return wp_create_nonce($this->main_piece) . '::' . $this->main_piece;
	}
	function denonce(){
		$this->main_piece = array_shift($this->pieces);
		if(empty($this->main_piece)){
			\aw2_library::set_error('the format is denonce.<key> to get the value'); 
			return '';
		}
		
		$a=\explode('::',$this->main_piece);
		if(count($a)==2){
			$returnvalue=wp_verify_nonce( $a[0], $a[1]);
			if($returnvalue==false)
				$value='error';
			else
				$value=$a[1];
		}
		else{
			$value='no';
		}
		return $value;
		
	}
	function taxonomy_term_list(){
		
		$post_id=$this->att('post_id');
		
		if(empty($post_id)){
			\aw2_library::set_error('post_id is required'); 
			return '';
		}
		
		//get all taxonomies attached to the post
		$result=array();
		$all_tax=get_post_taxonomies($post_id);	
		//for each taxonomy get the applied terms
		$field=$this->att('fields');
		
		if(empty($field)){
			$field='all';
		}
		
		foreach($all_tax as $tax){
			
			$result[$tax]= wp_get_post_terms( $post_id, $tax,  array("fields" => $field) );
		}

		return $result;
	}
}

function get_breadcrumb($theme_location = 'main', $separator = ' &raquo; ', $show_home = 'yes') {
		
		$items = wp_get_nav_menu_items($theme_location);
		_wp_menu_item_classes_by_context( $items ); // Set up the class variables, including current-classes
		$crumbs = array();
		
		if($show_home === 'yes')
			$crumbs[] = '<a href="'.get_option('home').'">Home</a> ';
		
		$i=0;
		foreach($items as $item) {
			if ($item->current === true) {
				$crumbs[] = "$item->title";
			}elseif (($item->current_item_ancestor === true || $item->current_item_parent === true) && $item->current === false){
				$crumbs[] = "<a href=\"{$item->url}\" title=\"{$item->title}\">{$item->title}</a>";
			}
			$i++;
		}
		$separator="<span class='separator'>".$separator."</span>";
		if($i==0){
			
			$crumbstxt='<a href="'.get_option('home').'">Home</a> '.$separator;
			if (is_author())
			{
				
				$crumbstxt.="<a href='".get_author_posts_url( get_the_author_meta( 'ID' ) )."'>".get_the_author_meta('display_name')."</a>";
				
			}else{
				
				if($post->post_parent) {
					$parent_id = $post->post_parent;
					$crumbs = array();
					$e=0;
					
					while ($parent_id) 
					{
						$page = get_page($parent_id);
						$crumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
						$parent_id = $page->post_parent;
						$e++;
					}
					if($e!=0){
						return implode($separator, $crumbs);
					}
				}
				
				if (is_category() || is_single()) 
				{
					$the_cat = get_the_category();
					$d=0;
					$catlinkarr="";
					foreach($the_cat as $k => $v)
					{
						$category_link = get_category_link( $v->cat_ID );
						if($d==0){
							$catlinkarr.= '<a href="'.$category_link.'">'.$v->name.'</a>';
						}else{
							$catlinkarr.= ' & <a href="'.$category_link.'">'.$v->name.'</a>';
						}
						
						
						$d++;
					}
					
					
					$crumbstxt.=$catlinkarr;
					if (is_single()) {
						$crumbstxt.=" ".$separator." ".the_title('', '', false);
					}
				} elseif (is_page()) {
					$crumbstxt.=the_title('', '', false);
				}
				
				
			}
				

				return $crumbstxt;
			
			
		}else{
			return implode($separator, $crumbs);
		}
		
	}



function get_adjacent_post( $all=false, $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ) {
		//php8amit		
		global $wpdb;

		if ( ( ! $post = get_post() ) || ! taxonomy_exists( $taxonomy ) )
			return null;

		$current_post_date = $post->post_date;

		$join = '';
		$where = '';
		$adjacent = $previous ? 'previous' : 'next';

		if ( $in_same_term || ! empty( $excluded_terms ) ) {
			if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) {
				// back-compat, $excluded_terms used to be $excluded_terms with IDs separated by " and "
				if ( false !== strpos( $excluded_terms, ' and ' ) ) {
					_deprecated_argument( __FUNCTION__, '3.3.0', sprintf( __( 'Use commas instead of %s to separate excluded terms.' ), "'and'" ) );
					$excluded_terms = explode( ' and ', $excluded_terms );
				} else {
					$excluded_terms = explode( ',', $excluded_terms );
				}

				$excluded_terms = array_map( 'intval', $excluded_terms );
			}

			if ( $in_same_term ) {
				$join .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
				$where .= $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

				if ( ! is_object_in_taxonomy( $post->post_type, $taxonomy ) )
					return '';
				$term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

				// Remove any exclusions from the term array to include.
				$term_array = array_diff( $term_array, (array) $excluded_terms );
				$term_array = array_map( 'intval', $term_array );

				if ( ! $term_array || is_wp_error( $term_array ) )
					return '';

				$where .= " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
			}

			/**
			 * Filters the IDs of terms excluded from adjacent post queries.
			 *
			 * The dynamic portion of the hook name, `$adjacent`, refers to the type
			 * of adjacency, 'next' or 'previous'.
			 *
			 * @since 4.4.0
			 *
			 * @param string $excluded_terms Array of excluded term IDs.
			 */
			$excluded_terms = apply_filters( "get_{$adjacent}_post_excluded_terms", $excluded_terms );

			if ( ! empty( $excluded_terms ) ) {
				$where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( ',', array_map( 'intval', $excluded_terms ) ) . ') )';
			}
		}

		// 'post_status' clause depends on the current user.
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();

			$post_type_object = get_post_type_object( $post->post_type );
			if ( empty( $post_type_object ) ) {
				$post_type_cap    = $post->post_type;
				$read_private_cap = 'read_private_' . $post_type_cap . 's';
			} else {
				$read_private_cap = $post_type_object->cap->read_private_posts;
			}

			/*
			 * Results should include private posts belonging to the current user, or private posts where the
			 * current user has the 'read_private_posts' cap.
			 */
			$private_states = get_post_stati( array( 'private' => true ) );
			$where .= " AND ( p.post_status = 'publish'";
			foreach ( (array) $private_states as $state ) {
				if ( current_user_can( $read_private_cap ) ) {
					$where .= $wpdb->prepare( " OR p.post_status = %s", $state );
				} else {
					$where .= $wpdb->prepare( " OR (p.post_author = %d AND p.post_status = %s)", $user_id, $state );
				}
			}
			if($all){
				$where .= " OR p.post_status = 'pending'";
				$where .= " OR p.post_status = 'draft'";
			}
			$where .= " )";
		} else {
			$where .= " AND p.post_status = 'publish'";
		}

		$op = $previous ? '<' : '>';
		$order = $previous ? 'DESC' : 'ASC';

		/**
		 * Filters the JOIN clause in the SQL for an adjacent post query.
		 *
		 * The dynamic portion of the hook name, `$adjacent`, refers to the type
		 * of adjacency, 'next' or 'previous'.
		 *
		 * @since 2.5.0
		 * @since 4.4.0 Added the `$taxonomy` and `$post` parameters.
		 *
		 * @param string  $join           The JOIN clause in the SQL.
		 * @param bool    $in_same_term   Whether post should be in a same taxonomy term.
		 * @param array   $excluded_terms Array of excluded term IDs.
		 * @param string  $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
		 * @param WP_Post $post           WP_Post object.
		 */
		$join = apply_filters( "get_{$adjacent}_post_join", $join, $in_same_term, $excluded_terms, $taxonomy, $post );

		/**
		 * Filters the WHERE clause in the SQL for an adjacent post query.
		 *
		 * The dynamic portion of the hook name, `$adjacent`, refers to the type
		 * of adjacency, 'next' or 'previous'.
		 *
		 * @since 2.5.0
		 * @since 4.4.0 Added the `$taxonomy` and `$post` parameters.
		 *
		 * @param string $where          The `WHERE` clause in the SQL.
		 * @param bool   $in_same_term   Whether post should be in a same taxonomy term.
		 * @param array  $excluded_terms Array of excluded term IDs.
		 * @param string $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
		 * @param WP_Post $post           WP_Post object.
		 */
		$where = apply_filters( "get_{$adjacent}_post_where", $wpdb->prepare( "WHERE p.post_date $op %s AND p.post_type = %s $where", $current_post_date, $post->post_type ), $in_same_term, $excluded_terms, $taxonomy, $post );

		/**
		 * Filters the ORDER BY clause in the SQL for an adjacent post query.
		 *
		 * The dynamic portion of the hook name, `$adjacent`, refers to the type
		 * of adjacency, 'next' or 'previous'.
		 *
		 * @since 2.5.0
		 * @since 4.4.0 Added the `$post` parameter.
		 *
		 * @param string $order_by The `ORDER BY` clause in the SQL.
		 * @param WP_Post $post    WP_Post object.
		 */
		$sort  = apply_filters( "get_{$adjacent}_post_sort", "ORDER BY p.post_date $order LIMIT 1", $post );

		$query = "SELECT p.ID FROM $wpdb->posts AS p $join $where $sort";
		$query_key = 'adjacent_post_' . md5( $query );
		$result = wp_cache_get( $query_key, 'counts' );
		if ( false !== $result ) {
			if ( $result )
				$result = get_post( $result );
			return $result;
		}

		$result = $wpdb->get_var( $query );
		if ( null === $result )
			$result = '';

		wp_cache_set( $query_key, $result, 'counts' );

		if ( $result )
			$result = get_post( $result );

		return $result;
	}

function sideload_file($url, $post_id){
	//php8amit		
		if ( !$url || !$post_id ) return new WP_Error('missing', "Need a valid URL and post ID...");
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        // Download file to temp location, returns full server path to temp file, ex; /home/user/public_html/mysite/wp-content/26192277_640.tmp
        $tmp = download_url( $url );
     
        // If error storing temporarily, unlink
        if ( is_wp_error( $tmp ) ) {
            @unlink($file_array['tmp_name']);   // clean up
            $file_array['tmp_name'] = '';
            return $tmp; // output wp_error
        }
     
        preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);    // fix file filename for query strings
        $url_filename = basename($matches[0]);                                                  // extract filename from url for title
        $url_type = wp_check_filetype($url_filename);                                           // determine file type (ext and mime/type)
     
        // assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
        $file_array['tmp_name'] = $tmp;                                                         // full server path to temp file
 
        $file_array['name'] = $url_filename;
     
        // required libraries for media_handle_sideload
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
     
        // do the validation and storage stuff
        $att_id = media_handle_sideload( $file_array, $post_id, null );
     
        // If error storing permanently, unlink
        if ( is_wp_error($att_id) ) {
            @unlink($file_array['tmp_name']);   // clean up
            return $att_id; // output wp_error
        }
          
        return $att_id;
	}

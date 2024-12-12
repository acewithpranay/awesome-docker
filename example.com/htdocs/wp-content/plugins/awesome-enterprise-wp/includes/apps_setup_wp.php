<?php 

class apps_setup_wp{
	

	static function wp_init(){
		\aw2\debug\flow(['main'=>'WP Init Started']);	
		
		self::register_default_cpts();
		\aw2\debug\flow(['main'=>'Default CPTs Registered']);	
		
		self::register_app_cpts();
		\aw2\debug\flow(['main'=>'App CPTs Registered']);	
		
		
		self::register_service_cpts();
		\aw2\debug\flow(['main'=>'Service CPTs Registered']);	
		
		awesome_flow::run_core('register');
		\aw2\debug\flow(['main'=>'Custom CPTs Registered']);			
		if(is_admin())return;
		
	}

	static function manage_cache(){
		
		$nginx_purge_url = add_query_arg( array( 'nginx_helper_action' => 'purge', 'nginx_helper_urls' => 'all' ) ); 
		
		$nginx_nonced_url = wp_nonce_url( $nginx_purge_url, 'nginx_helper-purge_all' );
		$global_nonced_url = wp_nonce_url( admin_url('admin.php?page=awesome-studio-cache&awesome_purge=global'), 'global_nonced-purge_all' );
		$session_nonced_url = wp_nonce_url(admin_url('admin.php?page=awesome-studio-cache&awesome_purge=session'), 'session_nonced-purge_all' );
		
		echo '<div class="wrap ">'; 
		echo '<h2>Manage Awesome Cache</h2><hr>';
		echo "<a href='".$global_nonced_url."' class='page-title-action'>Purge Global Cache (Modules & Taxonomy etc)</a> <br /><br />"; //11       	
		echo "<a href='".$nginx_nonced_url."' class='page-title-action'>Purge NGINX Cache</a> <br /><br />";
		echo "<a href='".$session_nonced_url."' class='page-title-action'>Purge Session Cache (Search. OTP & self expiry)</a> <br /><br />";//12
		echo '<form  action="'.wp_nonce_url(admin_url('admin.php?page=awesome-studio-cache&awesome_purge=redis_cache'), 'redis_nonced-purge').'" method="post">
				<label for="redis_db">Redis DB Number</label>
				<input type="text" id="redis_db" name="redis_db" />
				<input type="submit" />
			 </form> ';
		
		echo '</div>';	
	}
	

	static function purge_cache(){
		if ( !isset( $_REQUEST['awesome_purge'] ) )
				return;

			if ( !current_user_can( 'manage_options' ) )
				wp_die( 'Sorry, you do not have the necessary privileges to edit these options.' );

			$action = $_REQUEST['awesome_purge'];

			if ( $action == 'done' ) {
				//add_action( 'admin_notices', array( &$this, 'show_notice' ) );
				//add_action( 'network_admin_notices', array( &$this, 'show_notice' ) );
				return;
			}
			
			switch ( $action ) {
				case 'global':
					check_admin_referer( 'global_nonced-purge_all' );
					\aw2\global_cache\flush(null,null,null);
					break;
				case 'session':
					check_admin_referer( 'session_nonced-purge_all' );
					\aw2\session_cache\flush(null,null,'');
					break;
				case 'redis_cache':
					check_admin_referer( 'redis_nonced-purge' );
					$redis_db = intval($_POST['redis_db']);

					$redis = \aw2_library::redis_connect($redis_db);
					$redis->flushdb();
					break;
			}
			
			wp_redirect( esc_url_raw( add_query_arg( array( 'awesome_purge' =>'done' ) ) ) );
	}
	
	
	static function show_app_pages($app){
		
		if('root' != $app['slug']){
			rights_options_page($app);
		}else{
			echo '<div class="wrap ">';        	
			echo 'Not Yet Implemented';
			echo '</div>';
		}
	}
	
	static function app_slug_rewrite($wp_rewrite) {
    	
		$rules = array();
		
		$registered_apps=&aw2_library::get_array_ref('apps');
		foreach($registered_apps as $key => $app){
			if(!isset($app['collection']['pages']['post_type']))
				continue;
			
			$rules[$app['slug'] . '/?$'] = 'index.php?pagename=home&post_type='.$app['collection']['pages']['post_type'];
		}	
		
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	
	}
		
	static function fix_app_slug( $post_link, $post, $leavename ) {
 		//now apps show list show up in the menu to make it easy to add to nav menu
		if ( 'aw2_app' != $post->post_type || 'publish' != $post->post_status ) {
			return $post_link;
		}
		
		$post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
		return $post_link;
	}
	
	static function nav_menu_css_class( $classes , $item, $args){
		//ensures currect classes in menu if app is set
		$current_app_id=aw2_library::get('app.post_id');
		
		if($current_app_id == $item->object_id && $item->current_item_parent === false){
			$classes[] = 'current-menu-item';
		}
		
		return $classes;
	}
	
	//supporting functions
	static function do_not_include_template($template){
		return false;//do not include any thing
	}

		
	static function register_service_cpts(){
		
		$handlers=&aw2_library::get_array_ref('handlers');
		
		foreach($handlers as $key => $handler){
			if(!isset($handler['post_type']))
				continue;
			
			if(isset($handler['@service']) && $handler['@service'] === true){
				//$service_post_type[] =  $handler['post_type'];
				if(!post_type_exists( $handler['post_type'] ))
					//self::register_cpt($handler['post_type'],$handler['service_label'],'',false);
					\awesome_wp_utils::register_module($handler['post_type'],$handler['service_label'],$handler['service_label'],'service');
			}	
		}
		
	}
	
	static function register_default_cpts(){
			
		register_post_type(AWESOME_CORE_POST_TYPE, array(
			'label' => 'Core',
			'description' => '',
			'public' => false,
			'show_in_nav_menus'=>false,
			'show_ui' => true,
			'show_in_menu' => false,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'hierarchical' => true,
			'menu_icon'   => 'dashicons-align-right',
			'menu_position'   => 26,
			'rewrite' => false,
			'delete_with_user' => false,
			'query_var' => true,
			'supports' => array('title','editor','excerpt','revisions','custom-fields'),
			'labels' => array (
			  'name' => 'Core',
			  'singular_name' => 'Core',
			  'menu_name' => 'Core',
			  'add_new' => 'Add Core',
			  'add_new_item' => 'Add New Core',
			  'edit' => 'Edit',
			  'edit_item' => 'Edit Core',
			  'new_item' => 'New Core',
			  'view' => 'View Core',
			  'view_item' => 'View Core',
			  'search_items' => 'Search Core',
			  'not_found' => 'No Core Found',
			  'not_found_in_trash' => 'No Core Found in Trash',
			  'parent' => 'Parent Core',
			)
		)); 
		
		register_post_type(AWESOME_APPS_POST_TYPE, array(
			'label' => 'Local Apps',
			'public' => false,
			'show_in_nav_menus'=>true,
			'show_ui' => true,
			'show_in_menu' => false,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'hierarchical' => false,
			'query_var' => false,
			'menu_icon'=>'dashicons-archive',
			'supports' => array('title','editor','revisions','thumbnail','custom-fields'),
			'rewrite' => false,
			'delete_with_user' => false,
			'labels' => array (
				  'name' => 'Local Apps',
				  'singular_name' => 'Local App',
				  'menu_name' => 'Local Apps',
				  'add_new' => 'Create New App',
				  'add_new_item' => 'Add New Local App',
				  'new_item' => 'New Local App',
				  'edit' => 'Edit Local App',
				  'edit_item' => 'Edit Local App',
				  'view' => 'View Local App',
				  'view_item' => 'View Local App',
				  'search_items' => 'Search Local Apps',
				  'not_found' => 'No Local App Found',
				  'not_found_in_trash' => 'No Local App Found in Trash'
				)
			) 
		);
		
	}
	static function register_app_cpts(){
	
				
		$registered_apps=&aw2_library::get_array_ref('apps');
		
		foreach($registered_apps as $key => $app){
			foreach($app['collection'] as $collection_name => $collection){
				$supports='';
				$hierarchical=false;
				$public=false;
				$slug=null;
				if($collection_name == 'config'){
					$supports = array('title','editor','revisions','custom-fields');
					
				if(!post_type_exists( $collection['post_type'] ))
					\awesome_wp_utils::register_module($collection['post_type'],ucwords($app['name'] . ' ' . rtrim($collection_name,'s')) , ucwords($app['name'] . ' ' . $collection_name),'config',$supports );
					
				}
				
				if($collection_name == 'pages'){
					$hierarchical=true;
					$public=true;
					$slug=$key;
					$supports='';
					
					if(!post_type_exists( $collection['post_type'] ))
						self::register_cpt($collection['post_type'],$collection_name,$app['name'],$public,$supports,$hierarchical,$slug);
				}	
				
				if($collection_name == 'modules'){
					if(!post_type_exists( $collection['post_type'] ))
						\awesome_wp_utils::register_module($collection['post_type'],ucwords($app['name'] . ' ' . rtrim($collection_name,'s')) , ucwords($app['name'] . ' ' . $collection_name),'modules' );

				}
				

	
				if(isset($collection['post_type']) && !post_type_exists( $collection['post_type'] ))
					self::register_cpt($collection['post_type'],$collection_name,$app['name'],$public,$supports,$hierarchical,$slug);
			}
			
		}
	}
	

	static function admin_init(){
		awesome_flow::run_core('backend-init');
		self::purge_cache();
	}
	
	
	
	static function register_cpt($post_type,$name,$app_name='',$public,$supports=null,$hierarchical=false,$slug=null){
		
		if(empty($supports)|| !is_array($supports))
			$supports = array('title','editor','revisions','thumbnail');
		
		if($slug==null)$slug=$post_type;
		
		$name =ucwords($name);
		$app_name =ucwords($app_name);
		
		register_post_type($post_type, array(
			'label' => $name,
			'description' => '',
			'public' => $public,
			'show_in_nav_menus'=>false,
			'show_ui' => true,
			'show_in_menu' => false,
			'capability_type' => 'page',
			'delete_with_user'    => false,
			'map_meta_cap' => true,
			'hierarchical' => $hierarchical,
			'query_var' => true,
			'rewrite' => array("slug"=>$slug,'with_front'=>false),
			'supports' => $supports,
			'labels' => array (
				  'name' => $app_name.' '.$name,
				  'singular_name' => $app_name.' '.rtrim($name,'s'),
				  'add_new_item' => 'Add New '.$app_name.' '.rtrim($name,'s'),
				  'edit_item' => 'Edit '.$app_name.' '.rtrim($name,'s'),
				  'new_item' => 'New '.$app_name.' '.rtrim($name,'s'),
				  'view_item' => 'View '.$app_name.' '.rtrim($name,'s'),
				  'search_items' => 'Search '.$app_name.' '.$name,
				  'not_found' => 'No '.$app_name.' '.$name.' Found',
				  'not_found_in_trash' => 'No '.$app_name.' '.$name.' Found in Trash',
				)
			) 
		);
	}
	
	
}
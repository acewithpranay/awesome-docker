<?php


function awesome_export_html( $args = array() ) {
	global $wpdb, $post;

	$defaults = array(
		'activity'    => 'all',
		'filename'     => '',
		'start_date' => false,
		'end_date'   => false
	);
	$args     = wp_parse_args( $args, $defaults );

	/**
	 * Fires at the beginning of an export, before any headers are sent.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args An array of export arguments.
	 */

	$post_types = array();
	
	$base_export_folder='code-export';
	
	switch($args['activity']){
		case "all":
			$post_types = Monoframe::get_awesome_post_type();
			$post_types=array_unique($post_types);
			break;
	
		case "selected":
			if(isset($_REQUEST['services'])){
				foreach($_REQUEST['services'] as $service){
					$post_types[]=$service;
				}
			}
				
			if(isset($_REQUEST['apps'])){
				$registered_apps=&aw2_library::get_array_ref('apps');
				
				foreach($_REQUEST['apps'] as $selected_app){
					if(isset($registered_apps[$selected_app])){
						foreach($registered_apps[$selected_app]['collection'] as $collection_name => $collection){
							if($collection_name == 'posts')
								continue;
							if(isset($collection['post_type']))
							$post_types[]=$collection['post_type'];
						}
					}
					
				}
			}
			break;
			
		case "services":
			$handlers=&aw2_library::get_array_ref('handlers');
		
			foreach($handlers as $key => $handler){
				if(!isset($handler['post_type']))
					continue;
				
				if(AWESOME_CORE_POST_TYPE == $handler['post_type'])
					continue;
				
				if(isset($handler['service']) && strtolower($handler['service']) === 'yes'){
					$post_types[] =  $handler['post_type'];
				} 
				elseif(isset($handler['@service']) && $handler['@service'] === true){
					$post_types[] =  $handler['post_type'];
				}	
			}	
			break;
		case "apps":
				
			$registered_apps=&aw2_library::get_array_ref('apps');
			foreach ($registered_apps as $app){
				
				foreach($app['collection'] as $collection_name => $collection){
					if($collection_name == 'posts')
						continue;
					if(isset($collection['post_type']))
					$post_types[]=$collection['post_type'];
				}
			}
	}

	$esses      = array_fill( 0, count( $post_types ), '%s' );



	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	$where = $wpdb->prepare( "{$wpdb->posts}.post_type IN (" . implode( ',', $esses ) . ')', $post_types );


	$where .= " AND {$wpdb->posts}.post_status != 'auto-draft'";
	
	$join = '';
		
	if ( $args['author'] ) {
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_author = %d", $args['author'] );
	}

	if ( $args['start_date'] ) {
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_date >= %s", gmdate( 'Y-m-d', strtotime( $args['start_date'] ) ) );
	}

	if ( $args['end_date'] ) {
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_date < %s", gmdate( 'Y-m-d', strtotime( '+1 month', strtotime( $args['end_date'] ) ) ) );
	}
	
		// Grab a snapshot of post IDs, just in case it changes during the export.
	$post_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} $join WHERE $where" );

		
		
	if ( $post_ids ) {
		/**
		 * @global WP_Query $wp_query WordPress Query object.
		 */
		global $wp_query;

		// Fake being in the loop.
		$wp_query->in_the_loop = true;
		
		$base_path=dirname(ABSPATH);
		$date_folder = date('Ymd-His');
		$collection_base_directory= $base_path . '/'.$base_export_folder.'/'. $date_folder . '/';
		$module_list=array();
		$settings_list=array();
		
		// Fetch 20 posts at a time rather than loading the entire table into memory.
		while ( $next_posts = array_splice( $post_ids, 0, 20 ) ) {
			$where = 'WHERE ID IN (' . join( ',', $next_posts ) . ')';
			$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} $where" );
			
			// Begin Loop.
			
			foreach ( $posts as $post ) {
				$collection_directory = $collection_base_directory. $post->post_type;
				if (!file_exists($collection_directory)) {
					mkdir($collection_directory, 0777, true);
				}
				setup_postdata( $post );
				$file = $collection_directory . '/' . $post->post_name . '.module.html';
				file_put_contents($file,$post->post_content);
				$module_list[$post->post_type]['modules'][]= $post->post_name;
				
				if($post->post_name==='settings'){
					$settings_list[$post->post_type]= $post->ID;
				}
			}
		}	

		foreach($post_types as $post_type){
			//create modules list
			if(isset($module_list[$post_type])){
				$collection_directory = $collection_base_directory. $post_type;
				$file = $collection_directory . '/modules.json'; 
				$module_json = json_encode($module_list[$post_type]);
				file_put_contents($file,$module_json );
			}
			
			//create default settings json
			if(isset($settings_list[$post_type])){
				$settings_json =array();
				$collection_directory = $collection_base_directory. $post_type;
				$file = $collection_directory . '/settings.json'; 
				
				$sql="select meta_key,meta_value from  wp_postmeta  where post_id='" . $settings_list[$post_type] . "'";
				$results =aw2_library::get_results($sql);
				
				foreach($results as $result){
					$settings_json[$result['meta_key']]=$result['meta_value'];
				}
				$settings_json = json_encode($settings_json);
				file_put_contents($file,$settings_json );
			}
		}
		
		$file_name = $args['filename'].$date_folder.'.tar.gz';
		//now let's zip and then force download
		$cmd='tar -zcf '.$base_path . '/'.$base_export_folder.'/'.$file_name.'  -C '.$base_path . '/'.$base_export_folder.'/'.' '.$date_folder;
		//echo $cmd;
		shell_exec($cmd);
		
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		header( 'Content-Type: application/octet-stream; charset=' . get_option( 'blog_charset' ), true );
		echo file_get_contents($base_path . '/'.$base_export_folder.'/'.$file_name);
	}
}

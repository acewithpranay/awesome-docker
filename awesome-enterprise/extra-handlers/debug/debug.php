<?php
namespace aw2\debug;


\aw2_library::add_service('debug','Debug Library',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('debug.ignore','Ignore what is inside',['namespace'=>__NAMESPACE__]);

function ignore($atts,$content=null,$shortcode){
	return;
}

\aw2_library::add_service('debug.setup','Debug Setup',['namespace'=>__NAMESPACE__]);
function setup($atts,$content=null,$shortcode=null){
		\aw2_library::set('debug_config.active','no');

		if(!isset($_COOKIE['debug_output']) || $_COOKIE['debug_output']==='no' )return;
		if(!current_user_can('develop_for_awesomeui')){
			if(!isset($_COOKIE['debug_ticket']))return;
			$t=\aw2\session_ticket\get(["main"=>$_COOKIE['debug_ticket']],null,null);
			if(!$t || !$t['debug_ticket'])return;
		}		
		\aw2_library::set('debug_config.active','yes');
		
		\aw2_library::set('debug_config.starttime',microtime(true));
		
		foreach($_COOKIE as $key=>$value){
			if(\aw2_library::startsWith($key,'debug_')){
				\aw2_library::set('debug_config.' . substr($key, 6),$value);
			}	
		}
		if(\aw2_library::get('debug_config.wp_queries')==='yes'){
			define( 'SAVEQUERIES', true );
		}		
}

function diff_time($start=null){
	if(!$start)$start=\aw2_library::get('debug_config.starttime');
	// Get the difference between start and end in microseconds, as a float value
	$diff = microtime(true) - $start;

	// Break the difference into seconds and microseconds
	$sec = intval($diff);
	$micro = $diff - $sec;

	// Format the result as you want it
	// $final will contain something like "00:00:02.452"
	$final = $sec . str_replace('0.', '.', sprintf('%.3f', $micro));
	return $final;	
}

\aw2_library::add_service('debug.z','Add z for app',['namespace'=>__NAMESPACE__]);
function z($atts,$content=null,$shortcode=null){
	if(!\aw2_library::get('debug_config.z'))return;
	$app=\aw2_library::get_array_ref('app');
	if(!isset($app['collection']['modules']['post_type']))return;
	$post_type=$app['collection']['modules']['post_type'];

	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
	if(isset($app['active']['module'])){
		\aw2_library::get_post_from_slug($app['active']['module'],$post_type,$post);
		if($post){
			$str = '<a target=_blank href="' . SITE_URL . "/wp-admin/post.php?action=edit&post=" . $post->ID .'">Edit Current Module(' . $post->post_name .')</a><br>';
			\aw2\service\run(['service'=>$service . '.html','channel'=>'z'],$str);
		}
	}
	
		$str = '<a target=_blank href="' . SITE_URL . "/wp-admin/edit.php?post_type=" . $post_type .'">Posts Archive</a><br>';
		\aw2\service\run(['service'=>$service . '.html','channel'=>'z'],$str);

		$str = '<br><a target=_blank href="' . SITE_URL . "/wp-admin/post-new.php?post_type=" . $post_type .'">Add New</a><br>';
		\aw2\service\run(['service'=>$service . '.html','channel'=>'z'],$str);
	
	
	$args=array(
		'post_type' => $post_type,
		'post_status'=>'publish',
		'posts_per_page'=>500,
		'no_found_rows' => true, // counts posts, remove if pagination required
		'update_post_term_cache' => false, // grabs terms, remove if terms required (category, tag...)
		'update_post_meta_cache' => false, // grabs post meta, remove if post meta required	
		'orderby'=>'title',
		'order'=>'ASC'
	);	

	$str='';	
	$results = new \WP_Query( $args );
	$my_posts=$results->posts;

	foreach ($my_posts as $obj){
		$str .= '<a target=_blank href="' . SITE_URL . "wp-admin/post.php?post=" . $obj->ID  . "&action=edit" .'">' . $obj->post_title . '(' . $obj->ID . ')</a>' . '<br>';
	}

	if(empty($service))return;
		\aw2\service\run(['service'=>$service . '.html','channel'=>'z'],$str);
	}


\aw2_library::add_service('debug.wp_queries','WP Queries',['namespace'=>__NAMESPACE__]);
function wp_queries($atts,$content=null,$shortcode=null){
	if(!\aw2_library::get('debug_config.wp_queries'))return;
	global $wpdb;

	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
	foreach($wpdb->queries as $query){
			$html=\aw2_library::dump_debug(
			[
				[
					'type'=>'html',
					'value'	=>$query[0]
				]
			]		
			,
			"Query: " . $query[1]
			);
		\aw2\service\run(['service'=>$service . '.html','channel'=>'query'],$html);
	
	}
	
		
}

\aw2_library::add_service('debug.query','Output one query',['namespace'=>__NAMESPACE__]);
function query($atts,$content=null,$shortcode=null){
	extract(\aw2_library::shortcode_atts( array(
	'main'=>'',
	'start'=>''
	), $atts, 'dump' ) );

	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
			$html=\aw2_library::dump_debug(
			[
				[
					'type'=>'html',
					'value'	=>$main
				]
			]		
			,
			"Query: Time Taken:" . diff_time($start)
			);
		\aw2\service\run(['service'=>$service . '.html','channel'=>'query'],$html);
		
}


\aw2_library::add_service('debug.flow','Flow',['namespace'=>__NAMESPACE__]);
function flow($atts,$content=null,$shortcode=null){
	if(!\aw2_library::get('debug_config.flow'))return;	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	), $atts, 'dump' ) );
	
	//get service to call
	$service=\aw2_library::get('debug_config.output');

	$html=\aw2_library::dump_debug(
	[]		
	,
	$main . ': time:' . diff_time()
	);
	
	\aw2\service\run(['service'=>$service . '.html','channel'=>'flow'],$html);
	
}
	
\aw2_library::add_service('debug.module','Flow',['namespace'=>__NAMESPACE__]);	
function module($atts,$content=null,$shortcode=null){
	if(!\aw2_library::get('debug_config.module'))return;	
	extract(\aw2_library::shortcode_atts( array(
	'template'=>'',
	'start'=>''
	), $atts, 'dump' ) );

	//get service to call
	$service=\aw2_library::get('debug_config.output');

	$html=\aw2_library::dump_debug(
	[
		[
			'type'=>'html',
			'value'	=>"Template:: $template"
		],
	
		[
			'type'=>'html',
			'value'	=>"Module Array"
		]
//		,
//		[
//			'type'=>'arr',
//			'value'	=>\aw2_library::get('module')
//		]
	]		
	,
	"Module: " . \aw2_library::$stack['module']['collection']['post_type'] . '::' . \aw2_library::$stack['module']['slug'] . ' Time Taken:' . diff_time($start)
	);
	\aw2\service\run(['service'=>$service . '.html','channel'=>'flow'],$html);
	
	}
	

	
\aw2_library::add_service('debug.dump','Dump something to messages',['namespace'=>__NAMESPACE__]);		
function dump($atts,$content=null,$shortcode=null)
{
	if(\aw2_library::get('debug_config.active')!=='yes')return;	
	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>'',
	'expandlevel'=>1,
	'title'=>null
	), $atts, 'dump' ) );	

	$arr[0]['type']='arr';
	$arr[0]['value']=$main;
	
	$html=\aw2_library::dump_debug($arr,$title);
	\aw2\service\run(['service'=>$service . '.html','channel'=>'messages'],$html);


}


\aw2_library::add_service('debug.html','Dump something to messages',['namespace'=>__NAMESPACE__]);		
function html($atts,$content=null,$shortcode=null)
{
	if(\aw2_library::get('debug_config.active')!=='yes')return;	
	//get service to call
	$service=\aw2_library::get('debug_config.output');
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>'',
	'expandlevel'=>1,
	'title'=>null
	), $atts, 'dump' ) );	

	$arr[0]['type']='html';
	$arr[0]['value']=$main;
	
	$html=\aw2_library::dump_debug($arr,$title);
	
	\aw2\service\run(['service'=>$service . '.html','channel'=>'messages'],$html);
	

}

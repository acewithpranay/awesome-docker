<?php 
namespace aw2\debug_cache;

\aw2_library::add_service('debug_cache.set_access_post_type','Set the Debug Cache',['namespace'=>__NAMESPACE__]);
function set_access_post_type($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'post_type'=>null,
	'fields'=>array()
	), $atts) );
	
	if(!$post_type)return 'Invalid Post Type';	
	if(empty($fields))return 'Missing fields data';	

	$redis = \aw2_library::redis_connect(REDIS_DATABASE_DEBUG_CACHE);
	$key = 'post_type:'.$post_type;
	
	foreach($fields as $k=>$value){
		$redis->hset($key, $k,$value);
	}
	$redis->hIncrBy($key, 'count', 1);
	
	return;
}

\aw2_library::add_service('debug_cache.set_access_module','Set the Debug Cache',['namespace'=>__NAMESPACE__]);

function set_access_module($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'post_type'=>null,
	'module'=>null,
	'fields'=>array()
	), $atts) );
	
	if(!$post_type)return 'Invalid Post Type';	
	if(!$module)return 'Missing Module Slug';	
	if(empty($fields))return 'Missing fields data';	
	
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_DEBUG_CACHE);
	
	$key = 'module:'.$post_type.'#'.$module;
	
	foreach($fields as $k=>$value){
		$redis->hset($key, $k,$value);
	}
	$redis->hIncrBy($key, 'count', 1);
	
	return;
}


\aw2_library::add_service('debug_cache.set_access_app','Set the Debug Cache',['namespace'=>__NAMESPACE__]);

function set_access_app($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'app'=>null,
	'fields'=>array()
	), $atts) );
	
	if(!$app)return 'App slug is missing';	
	if(empty($fields))return 'Missing fields data';	
	
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_DEBUG_CACHE);
	
	$key = 'app:'.$app;
	
	foreach($fields as $k=>$value){
		$redis->hset($key, $k,$value);
	}
	$redis->hIncrBy($key, 'cnt', 1);
	if(defined('SET_ENV_CACHE') && SET_ENV_CACHE)$redis->hIncrBy($key, 'cache_cnt', 1);
	
	return;
}



\aw2_library::add_service('debug_cache.getkeys','Get the Debug Cache',['namespace'=>__NAMESPACE__]);
function getkeys($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>null
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_DEBUG_CACHE);
	
	$return_value = $redis->keys($main);
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('debug_cache.hget','Get the Debug Cache',['namespace'=>__NAMESPACE__]);
function hget($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'field'=>null
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	//if(!$field)return 'Invalid field';
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_DEBUG_CACHE);
		
	$return_value='';
	if($redis->exists($main)){
		if($field)
			$return_value = $redis->hget($main,$field);
		else
			$return_value = $redis->hGetAll($main);
	}	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('debug_cache.flush','Flush the Debug Cache',['namespace'=>__NAMESPACE__]);
function flush($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	//Connect to Redis and store the data
		$redis = \aw2_library::redis_connect(REDIS_DATABASE_DEBUG_CACHE);
	$redis->flushdb() ;
}


function exists($atts,$content=null,$shortcode=null){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>null,
	'prefix'=>'',
	), $atts) );
	
	if(!$main)return 'Main must be set';		
	if($prefix)$main=$prefix . $main;
	//Connect to Redis and store the data
	$redis = \aw2_library::redis_connect(REDIS_DATABASE_DEBUG_CACHE);
		
	$return_value=false;
	if($redis->exists($main))$return_value = true;
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


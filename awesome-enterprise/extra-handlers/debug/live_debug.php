<?php
namespace aw2\live_debug;


/*
	if LIVE_DEBUG===yes then live_debug.activate
*/


//define('LIVE_DEBUG', 'yes');

function event_raise($atts=null,$content=null,$shortcode=null){}



\aw2_library::add_service('live_debug.activate','Setup Debugging Env',['namespace'=>__NAMESPACE__]);
function activate($atts=null,$content=null,$shortcode=null){
	\aw2_library::set('@live_debug.active','yes');
}

\aw2_library::add_service('live_debug.deactivate','Setup Debugging Env',['namespace'=>__NAMESPACE__]);
function deactivate($atts=null,$content=null,$shortcode=null){
	\aw2_library::set('@live_debug.active','no');
}

\aw2_library::add_service('live_debug.active','Setup Debugging Env',['namespace'=>__NAMESPACE__]);
function active($atts=null,$content=null,$shortcode=null){
	return \aw2_library::get('@live_debug.active');
}


\aw2_library::add_service('live_debug.is_active','Setup Debugging Env',['namespace'=>__NAMESPACE__]);
function is_active($atts=null,$content=null,$shortcode=null){
	if(\aw2_library::get('@live_debug.active')==="yes") return true;
	return false;
}



\aw2_library::add_service('live_debug.setup.cookie','Setup Debugging Env',['func'=>'setup_cookie','namespace'=>__NAMESPACE__]);
function setup_cookie($atts=null,$content=null,$shortcode=null){
	
	if(IS_WP && is_admin()) return;
	
	if(isset($_COOKIE['live_debug'])){
		$ticket_id = $_COOKIE['live_debug'];
		$ticket= \aw2\session_ticket\get(["main"=>$ticket_id]);
		$content = isset($ticket['debug_code'])?$ticket['debug_code']:'';
	}
	
	setup($atts,$content);
}


\aw2_library::add_service('live_debug.setup','Setup Debugging Env',['namespace'=>__NAMESPACE__]);
function setup($atts=null,$content=null,$shortcode=null){

	\aw2_library::set('@live_debug',array(
		"active"=>"no",
		"event"=>array(),
		"output"=>array(),
		"is_publishing"=>"no",
		"collection_reset"=>"no",
		"left_events"=>"500"
	));
	\aw2_library::parse_shortcode($content);
	
	
}


\aw2_library::add_service('live_debug.start.add','Start publishing when condition is met',['func'=>'start_add','namespace'=>__NAMESPACE__]);
function start_add($atts=null,$content=null,$shortcode=null){
	if(active()!=='yes') return;		
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>'',
	'event'=>null,
	), $atts, '' ) );
	
	//\util::var_dump("debug.start.add");
	//\util::var_dump($content);
	//\util::var_dump($atts);
	
	$ab=new \array_builder();
	$arr=$ab->parse($content);
	\aw2_library::set('@live_debug.start.conditions.new',$arr);
	
}

\aw2_library::add_service('live_debug.code.add','This code is executed while checking for Publishing Events',['func'=>'code_add','namespace'=>__NAMESPACE__]);
function code_add($atts=null,$content=null,$shortcode=null){
	if(active()!=='yes') return;		
	
	extract(\aw2_library::shortcode_atts( array(
	'main'=>'',
	'event'=>null,
	), $atts, 'dump' ) );
	
	\aw2_library::set('@live_debug.start.code.new',$content);
	
}


\aw2_library::add_service('live_debug.publish.start','Start Publishing Events',['func'=>'publish_start','namespace'=>__NAMESPACE__]);
function publish_start($atts=null,$content=null,$shortcode=null){
	
	$status = \aw2_library::get('@live_debug.is_publishing');
	if($status==='yes')return;	
	
	//check if controller is JS or CSS in that case do not debug
	//get url and check for /js/ or /css/ in that case do not 
	//$request_uri = $_SERVER["REQUEST_URI"];
	
	$exclude_path = array('/js/','/css/','/fileviewer/','/file/');
	
	foreach($exclude_path as $path){
		if(strpos($_SERVER["REQUEST_URI"], $path) !== false){
			return;
		}	
	}
	
	\aw2_library::set('@live_debug.is_publishing',"yes");

}

\aw2_library::add_service('live_debug.publish.stop','Stop Publishing Events',['func'=>'publish_stop','namespace'=>__NAMESPACE__]);
function publish_stop($atts=null,$content=null,$shortcode=null){

	$status = \aw2_library::get('@live_debug.is_publishing');
	if($status==='no')return;	
	
	\aw2_library::set('@live_debug.is_publishing',"no");

}

\aw2_library::add_service('live_debug.publish.is_active','Check if publishing is active',['func'=>'publish_is_active','namespace'=>__NAMESPACE__]);
function publish_is_active($atts=null,$content=null,$shortcode=null){
	$status = \aw2_library::get('@live_debug.is_publishing');
	if($status==='yes')return true;	
	
	return false;
}

\aw2_library::add_service('live_debug.publish.active','gives the current status of is_publishing',['func'=>'publish_active','namespace'=>__NAMESPACE__]);
function publish_active($atts=null,$content=null,$shortcode=null){
	return \aw2_library::get('@live_debug.is_publishing');
}

\aw2_library::add_service('live_debug.publish.event','Publish Events',['func'=>'publish_event','namespace'=>__NAMESPACE__]);
function publish_event($atts=null,$content=null,$shortcode=null){
	if(!is_active()) return;		
	
	extract(\aw2_library::shortcode_atts( array(
	'event'=>'',
	'bgcolor'=>'',
	'format'=>array()
	), $atts, '' ) );
	
	
	$flow = (isset($event['flow'])?$event['flow']:'');
	$action = (isset($event['action'])?$event['action']:'');
	$event_title= $flow.':'.$action;
	
	$event['bg_color'] =$bgcolor;
	
	if(isset($format['bgcolor']))
		$event['bg_color'] = $format['bgcolor'];
	
	
	
	event_set(['event_title'=>$event_title,'message'=>$event]);
	
	//check the conditions to see if publishing has started
	
	publish_decide();
	
	if(!publish_is_active())return;
	
	output_decide();

}

\aw2_library::add_service('live_debug.output.decide','Decide to output the published events',['func'=>'output_decide','namespace'=>__NAMESPACE__]);
function output_decide($atts=null,$content=null,$shortcode=null){
	if(!is_active()) return;		

	//run code conditions
	$codes = \aw2_library::get('@live_debug.output.code');
	if(is_array($codes)){
		array_map(function($item){
			\aw2_library::parse_shortcode($item);
				
		 },$codes);
	}
	
	$starts = \aw2_library::get('@live_debug.output.conditions');

	if(!is_array($starts)) return;
	
	foreach($starts as $item){

		$match=true;		

		if(isset($item['checks'])){
			foreach($item['checks'] as $check){
				$rhs = \aw2_library::safe_explode(',',$check['value']);		
				
				if(isset($check['event_key']))
					$lhs=\aw2_library::get('@live_debug.event.' . $check['event_key']);

				if(isset($check['debug_key']))
					$lhs=\aw2_library::get('@live_debug.' . $check['debug_key']);

				if(isset($check['env_key']))
					$lhs=\aw2_library::get($check['env_key']);

				
				if(!in_array($lhs,$rhs)){
					$match=false;	
					break; // this is loop for "and" so if any condtion fails, exit the loop
				}
			}
		}
	
		if($match===true){
			output_items($item);
		}	
	}
}


\aw2_library::add_service('live_debug.publish.decide','Decide to publish the events',['func'=>'publish_decide','namespace'=>__NAMESPACE__]);
function publish_decide($atts=null,$content=null,$shortcode=null){
	if(!is_active()) return;		
	
	$starts = \aw2_library::get('@live_debug.start.conditions');
	
	if(!is_array($starts)) return;
	
	foreach($starts as $item){
		
		$match=true;

		foreach($item['checks'] as $check){
			$rhs = \aw2_library::safe_explode(',',$check['value']);	
			
			if(isset($check['event_key']))
				$lhs=\aw2_library::get('@live_debug.event.' . $check['event_key']);

			if(isset($check['debug_key']))
				$lhs=\aw2_library::get('@live_debug.' . $check['debug_key']);

			if(isset($check['env_key']))
				$lhs=\aw2_library::get($check['env_key']);

			
			if(!in_array($lhs,$rhs)){
				$match=false;	
				break; // this is loop for "and" so if any condtion fails, exit the loop
			}
			
		}
		
		if($match===true){
			//\util::var_dump($match);
			publish_start();
			break;
		}	 
	}
	
	//run code conditions
	$codes = \aw2_library::get('@live_debug.start.code');
	if(is_array($codes)){
		array_map(function($item){
				\aw2_library::parse_shortcode($item);
				
		 },$codes);
	}
}

function output_items($atts){
	
	extract(\aw2_library::shortcode_atts( array(
	'output'=>array()
	), $atts, '' ) );

	//if app is debugger - skip the output
	if(\aw2_library::get('app.slug') === 'debugger') return;
	if(strpos($_SERVER["REQUEST_URI"], '/debugger/') !== false){
		return;
	}	
	
	//after everything is verified reduce the event
	$left_events = \aw2_library::get('@live_debug.left_events');
	if($left_events <= 0) return;
	
	\aw2_library::set('@live_debug.left_events',((int)$left_events-1));
	
	//check if subscribe condition is met 
	foreach($output as $item){
		if(!isset($item['service'])) continue;
		
		$service = $item['service'];
		unset($item['service']);
		
		if(function_exists("\aw2\debug_output\\".$service )){
			call_user_func("\aw2\debug_output\\".$service ,$item);
		}

	}

}

\aw2_library::add_service('live_debug.event.set','Set the published event in the @live_debug',['func'=>'event_set','namespace'=>__NAMESPACE__]);
function event_set($atts=null,$content=null,$shortcode=null){
	if(!is_active()) return;		
	
	extract(\aw2_library::shortcode_atts( array(
	'event_title'=>'',
	'message'=>''
	), $atts, '' ) );

	$message['event_id']=$event_title;
	
	\aw2_library::set('@live_debug.event',$message);
	\aw2_library::set('@live_debug.event_title',$event_title);

}

\aw2_library::add_service('live_debug.output.add','Output  Events On Conditions',['func'=>'output_add','namespace'=>__NAMESPACE__]);
function output_add($atts=null,$content=null,$shortcode=null){

	$ab=new \array_builder();
	$arr=$ab->parse($content);
	

	$arr['output']=array_map(function($item){
			if(isset($item['event_keys']))
				$item['event_keys'] = explode(',',$item['event_keys']);
			
			if(isset($item['env_keys']))
				$item['env_keys'] = explode(',',$item['env_keys']);

			return $item;
	 },$arr['output']);

	\aw2_library::set('@live_debug.output.conditions.new',$arr);
}

\aw2_library::add_service('live_debug.output.code','hanndle the output directly',['func'=>'output_code','namespace'=>__NAMESPACE__]);
function output_code($atts=null,$content=null,$shortcode=null){
	if(!is_active()) return;
	
	\aw2_library::set('@live_debug.output.code.new',$content);
}

namespace aw2\debug_output;

\aw2_library::add_service('live_debug.output.dump','publish event on the screen',['namespace'=>__NAMESPACE__]);
function dump($atts=null,$content=null,$shortcode=null){
	extract(\aw2_library::shortcode_atts( array(
	'event_keys'=>'',
	'env_keys'=>'',
	'event'=>'',
	'live_debug'=>'',
	'env_dump'=>'',
	'die'=>''
	), $atts, '' ) );
	
	
	if(!\aw2\live_debug\is_active()) return;		
	if(!\aw2\live_debug\publish_is_active()) return;	
	
	if(\aw2_library::get('@live_debug.debug_icon') !== 'yes'){
		include_once "lib/debug_icon.php";
		\aw2_library::set('@live_debug.debug_icon','yes');
	 }
	
	
		
	$bg_color = \aw2_library::get('@live_debug.event.bg_color');	

	$bg_color=empty($bg_color)?'#C1EFFF':$bg_color;
		
	$active_event = \aw2_library::get('@live_debug.event_title');

	$msg='<h3>' . $active_event .
	'<br><small><strong>app:</strong><em>' . \aw2_library::get('app.slug') .'</em>' .
	' <strong>post_type:</strong><em>' . \aw2_library::get('module.collection.post_type').'</em>' .
	' <strong>module:</strong><em>' . \aw2_library::get('module.slug') .'</em>'.
	' <strong>tpl:</strong><em>' . \aw2_library::get('template.name').'</em>' .
	' <strong>service:</strong><em>' . \aw2_library::get('module.collection.service_id') .'</em>'.
	' <strong>conn:</strong><em>' . \aw2_library::get('module.collection.connection').'</em>' .
	'</small></h3>' ;

	
	if(!empty($event_keys)){	
		foreach($atts['event_keys'] as $key){	
			$msg .= '<em>'.$key .'</em>'.\util::var_dump(\aw2_library::get('@live_debug.event.' . $key),true);
		}
	}
	
	if(!empty($env_keys)){	
		foreach($atts['env_keys'] as $key){	
			$msg .= '<em>'.$key .'</em>'.\util::var_dump(\aw2_library::get($key),true);
		}
	}
	
	if($event==='yes'){
		$msg .= '<em>#all</em>'.\util::var_dump(\aw2_library::get('@live_debug.event'),true);
	}

	if($live_debug==='yes'){
		$msg .= '<em>#live_debug</em>'.\util::var_dump(\aw2_library::get('@live_debug'),true);
	}
		
	
	echo "<template class='awesome_live_debug_data'> <div style='padding:10px;margin-bottom:5px;background-color:".$bg_color."'>".$msg."</div></template>";
	
	if($env_dump==='yes')
		echo "<template class='awesome_live_debug_data'> <div>".\aw2\env\dump([],null,null)."</div></template>";
	
	if($die==='yes'){
		die('Die Called.');
	}	
}

\aw2_library::add_service('live_debug.output.collect','collect the events to collect_id',['namespace'=>__NAMESPACE__]);
function collect($atts=null,$content=null,$shortcode=null){
	//if(!isset($_COOKIE['live_debug'])) return; 
		
	extract(\aw2_library::shortcode_atts( array(
		'event_keys'=>'',
		'env_keys'=>'',
		'preserve'=>'no',
		'max_events'=>500,
		'event'=>'',
		'live_debug'=>'',
		'collect_id'=>isset($_COOKIE['live_debug'])?$_COOKIE['live_debug']:rand()
		), $atts, '' ) );

	if(!\aw2\live_debug\is_active()) return;		
	if(!\aw2\live_debug\publish_is_active()) return;	

	$ticket_id = 'debug_collect:'.$collect_id;
	
	 if(\aw2_library::get('@live_debug.'.$collect_id.'.collection_reset') !== 'yes'){
		//reset the redis cache 
		if($preserve === 'no') 
			\aw2\session_cache\del(['main'=>$ticket_id],null,null);
		
		//and set it to yes
		\aw2_library::set('@live_debug.'.$collect_id.'.collection_reset','yes');
	 }

	$count = \aw2\session_cache\hlen(['main'=>$ticket_id],null,null);
	if($count >= $max_events) return;
	$count++; //to be used as hashfield key
	
	
	$arr=array();			
	
	$active_event = \aw2_library::get('@live_debug.event_title');
	
	$arr['event_title']=$active_event;
	$arr['bg_color']= \aw2_library::get('@live_debug.event.bg_color');
	$arr['extra_info']='<small><strong>app:</strong><em>' . \aw2_library::get('app.slug') .'</em>' .
	' <strong>post_type:</strong><em>' . \aw2_library::get('module.collection.post_type').'</em>' .
	' <strong>module:</strong><em>' . \aw2_library::get('module.slug') .'</em>'.
	' <strong>tpl:</strong><em>' . \aw2_library::get('template.name').'</em>' .
	' <strong>service:</strong><em>' . \aw2_library::get('module.collection.service_id') .'</em>'.
	' <strong>conn:</strong><em>' . \aw2_library::get('module.collection.connection').'</em>' .
	'</small>' ;
	
	
	if(!empty($event_keys)){	
		foreach($event_keys as $key){	
			$arr['event_keys'][$key]= \aw2_library::get('@live_debug.event.' . $key);
		}
	}
	
	if(!empty($env_keys)){	
		foreach($env_keys as $key){	
			$arr['env_keys'][$key]= \aw2_library::get($key);
		}
	}
	
	if($event==='yes'){
		$arr['event']= \aw2_library::get('@live_debug.event');
	}

	if($live_debug==='yes'){
		$arr['live_debug']= \aw2_library::get('@live_debug');
	}
	
	
	\aw2\session_cache\hset(['main'=>$ticket_id,'ttl'=>'60','field'=>$count,'value'=>json_encode($arr)],null,null);
	
}

<?php

namespace aw2\jwt;
#require_once AWESOME_PATH.'/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

\aw2_library::add_service('jwt_token.encode','Encodes the string. Use jwt_token.encode',['namespace'=>__NAMESPACE__]);
function encode($atts,$content=null,$shortcode=null){
	
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( \aw2_library::shortcode_atts( array(
		'data'=>''
		), $atts) );	
		
		$api_key=$data['api_key'];
		$partner_id = $data['partner_id'];
	
		//payload
		$payload = array(
			"aud" => $partner_id,
			"iat" => time(),
			"jti" => "loantap".time()
		);
	
		// create token
		try{
			$jwt = JWT::encode($payload,$api_key,'HS256');
		}catch(\Exception $e){
			return json_encode(array("status"=>"error","message"=>"Invalid input"));
		}
		return json_encode(array("status"=>"success","message"=>"JWT token generated",'jwt_token'=>$jwt));
}

\aw2_library::add_service('jwt_token.decode','decodes the string. Use jwt_token.decode',['namespace'=>__NAMESPACE__]);
function decode($atts,$content=null,$shortcode=null){

	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( \aw2_library::shortcode_atts( array(
		'data'=>'',
		'jwt_token'=>'',
		'time_out' =>''
		), $atts) );	

	$api_key=$data['api_key'];

	try{
		$decoded = JWT::decode($jwt_token, $api_key, array('HS256'));
	
	}catch(\Exception $e){
		return json_encode(array("status"=>"error","status_code"=>304,"message"=>"Invalid API Key."));
	}
	
	if($decoded->aud!=$data['partner_id']){
		return json_encode(array("status"=>"error","status_code"=>302,"message"=>"Invalid partner Id."));
	}
	$mins = (time() - $decoded->iat) / 60;
	
	$time_out = $time_out ?: 2;
	if($mins > $time_out){		
		return json_encode(array("status"=>"error","status_code"=>316,"message"=>"Token has been expired."));
	}
		
	return json_encode(array("status"=>"success","status_code"=>200,"message"=>"Authentication successful."));

}


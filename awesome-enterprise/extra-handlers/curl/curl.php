<?php 
namespace aw2\curl;

\aw2_library::add_service('curl.api.get','CURL GET for API',['func'=>'api_get','namespace'=>__NAMESPACE__]);
function api_get($atts=null,$content=null,$shortcode=null){
	if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    
    extract(\aw2_library::shortcode_atts(array(
    'data' => null,
    'url' => null,
    'proxy' => null,
    'header' => null
    ), $atts));
	
	$live_debug_event=array();
	$live_debug_event['flow']='curl';
	$live_debug_event['action']='curl.called';
	$live_debug_event['stream']='curl.api.get';
	
	
    if(is_null($url)) { 
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_type']='curl url is missing';
		\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#F0EBE3']);
		return;
	}
	
	$return_value = curl_call('get', $url, $data, $header, $proxy);
	
    $return_value = \aw2_library::post_actions('all', $return_value, $atts);
    return $return_value;
    
}


\aw2_library::add_service('curl.api.post','CURL POST for API',['func'=>'api_post','namespace'=>__NAMESPACE__]);
function api_post($atts=null,$content=null,$shortcode=null){
	if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    
    extract(\aw2_library::shortcode_atts(array(
    'data' => null,
    'url' => null,
    'proxy' => null,
    'header' => null
    ), $atts));
	
	$live_debug_event=array();
	$live_debug_event['flow']='curl';
	$live_debug_event['action']='curl.called';
	$live_debug_event['stream']='curl.api.post';
	
	
    if(is_null($url)) { 
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_type']='curl url is missing';
		\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#F0EBE3']);
		return;
	}
	
	$return_value = curl_call('POST', $url, $data, $header, $proxy);
	
    $return_value = \aw2_library::post_actions('all', $return_value, $atts);
    return $return_value;
    
}


\aw2_library::add_service('curl.api.patch','CURL PATCH for API',['func'=>'api_patch','namespace'=>__NAMESPACE__]);
function api_patch($atts=null,$content=null,$shortcode=null){
	if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    
    extract(\aw2_library::shortcode_atts(array(
    'data' => null,
    'url' => null,
    'proxy' => null,
    'header' => null
    ), $atts));
	
	$live_debug_event=array();
	$live_debug_event['flow']='curl';
	$live_debug_event['action']='curl.called';
	$live_debug_event['stream']='curl.api.patch';
	
	
    if(is_null($url)) { 
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_type']='curl url is missing';
		\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#F0EBE3']);
		return;
	}
	
	$return_value = curl_call('PATCH', $url, $data, $header, $proxy);
	
    $return_value = \aw2_library::post_actions('all', $return_value, $atts);
    return $return_value;
    
}


\aw2_library::add_service('curl.page.get','CURL GET for API',['func'=>'page_get','namespace'=>__NAMESPACE__]);
function page_get($atts=null,$content=null,$shortcode=null){
	if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    
    extract(\aw2_library::shortcode_atts(array(
    'data' => null,
    'url' => null,
    'proxy' => null,
    'header' => null
    ), $atts));
	
	$live_debug_event=array();
	$live_debug_event['flow']='curl';
	$live_debug_event['action']='curl.called';
	$live_debug_event['stream']='curl.api.get';
	
	
    if(is_null($url)) { 
			$temp_debug=$live_debug_event;
			$temp_debug['error']='yes';
			$temp_debug['error_type']='curl url is missing';
		\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#F0EBE3']);
		return;
	}
	
	$return_value = curl_call('POST', $url, $data, $header, $proxy);
	
    $return_value = \aw2_library::post_actions('all', $return_value, $atts);
    return $return_value;
    
}

function curl_call($method, $url, $data=null, $headers=null, $proxy=null){
	$live_debug_event=array();
	$live_debug_event['flow']='curl';
	$live_debug_event['action']='curl_call';
	$live_debug_event['stream']='curl_call';
	
    $curl = curl_init();
    switch ($method){
	  case "POST":
		 curl_setopt($curl, CURLOPT_POST, 1);
		 if ($data)
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		 break;
	  case "PUT":
		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		 if ($data)
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
		 break;
	  case "PATCH":
		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
		 if ($data)
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
		 break;
	  default:
		 if ($data)
			$url = sprintf("%s?%s", $url, http_build_query($data));
    }
   
     
	// OPTIONS:
	curl_setopt($curl, CURLOPT_URL, $url);
	if(!is_null($headers)){
		$head= array();
		foreach($headers['header'] as $item){
			$head[]=$item['key'].": ".$item['val'];
		}
	   curl_setopt($curl, CURLOPT_HTTPHEADER, $head);
	}
	
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
   curl_setopt($curl, CURLOPT_TIMEOUT, 600);
   curl_setopt($curl, CURLOPT_HEADER,  true);
   //proxy
   if(is_array($proxy)){
		//Set the proxy IP.
		curl_setopt($curl, CURLOPT_PROXY, $proxy['host']);

		//Set the port.
		curl_setopt($curl, CURLOPT_PROXYPORT, $proxy['port']);
		if(isset($proxy['username'])){
			//Specify the username and password.
			curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxy['username'].":".$proxy['password']);
		}
   }

    /**
    $result_headers=[];
    // this function is called by curl for each header received
	curl_setopt($ch, CURLOPT_HEADERFUNCTION,
	  function($curl, $header) use (&$result_headers)
	  {
		$len = strlen($header);
		$header = explode(':', $header, 2);
		if (count($header) < 2) // ignore invalid headers
		  return $len;

		$result_headers[strtolower(trim($header[0]))][] = trim($header[1]);
		
		return $len;
	  }
	);
     **/ 
   // EXECUTE:
   $result = curl_exec($curl);
   
 	  // Split headers and content
	//$response_array = explode("\r\n\r\n", $result);
	
	$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
  	$headers = substr($result, 0, $header_size);
  	$content = substr($result, $header_size);

	$headers_arr = extractHeaders($headers);
	// Parse and print the cookies from the response headers
	//$content = array_pop($response_array);
	$cookies = extractCookies($headers);
		
   if(!$result){
		$temp_debug=$live_debug_event;
		$temp_debug['error']='yes';
		$temp_debug['error_type']='curl_error';
		$temp_debug['error_message']=curl_error($curl);;
		\aw2\live_debug\publish_event(['event'=>$temp_debug,'bgcolor'=>'#F0EBE3']);
		return array();
   }

   $info = curl_getinfo($curl);
   curl_close($curl);
   $temp_arr=array(
	"info"=>$info,
	"headers"=>$headers_arr,
	"cookies"=>$cookies,
	"result"=>$content
   );
   return $temp_arr;
}

// Function to extract cookies from a header block
function extractCookies($headerBlock) {
    $cookieHeader = explode("\n", $headerBlock);
    $cookies = [];

    foreach ($cookieHeader as $header) {
        if (strpos($header, 'Set-Cookie:') !== false) {
            $cookie = trim(str_replace('Set-Cookie:', '', $header));
            $cookieParts = explode(';', $cookie, 2);
            $cookiePair = explode('=', $cookieParts[0], 2);
            $cookies[trim($cookiePair[0])] = trim($cookiePair[1]);
        }
    }

    return $cookies;
}

function extractHeaders($headerBlock){
	  // Convert the $headers string to an indexed array
	  $headers_indexed_arr = explode("\r\n", $headerBlock);

	  // Define as array before using in loop
	  $headers_arr = array();
	  // Remember the status message in a separate variable
	  $status_message = array_shift($headers_indexed_arr);
  
	  // Create an associative array containing the response headers
	  foreach ($headers_indexed_arr as $value) {
		  if(false !== ($matches = explode(':', $value, 2))) {
			  $headers_arr["{$matches[0]}"] = trim($matches[1]);
		  }                
	  }
	  $headers_arr = array_filter($headers_arr);
	  return $headers_arr;
}

<?php
namespace aw2\google;

\aw2_library::add_service('google','google api support',['namespace'=>__NAMESPACE__]);


\aw2_library::add_service('google.login_url','returns the login URL for google',['namespace'=>__NAMESPACE__]);

function login_url($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'ticket_id'=>null,
	'scope'=>null,
	'app_id'=>null,
	'version'=>7.4,
	'app_secret'=>null
	), $atts) );
	
	if(empty($ticket_id)) return '';
	if(empty($app_id) || empty($app_secret)) return '';	
	
	require_once AWESOME_PATH.'/vendor/autoload.php';
	
	$return_value='';
	
	$scope = \aw2\session_ticket\get(["main"=>$ticket_id,"field"=>'scope'],null,null);
	$scopes= explode(',',$scope);
	
	
	// create Client Request to access Google API
	$client = new \Google_Client();
	$client->setClientId($app_id);
	$client->setClientSecret($app_secret);
	$client->setRedirectUri(SITE_URL.'?social_auth=google');
	foreach($scopes as $s){
		$client->addScope($s);
	}
	
	$client->setAccessType('offline');
	
	
	
	
	//$client = new Client($app_id,$app_secret) ;
	
	
    $return_value = $client->createAuthUrl(); // get url on Google to start linking
  
    //\aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'state',"value"=>$client->getState()],null,null); // save state for future validation
    //\aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'redirect_url',"value"=>$return_value],null,null); // save state for future validation
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}


\aw2_library::add_service('google.auth','Check the auth for linkedin',['namespace'=>__NAMESPACE__]);

function auth($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	extract(\aw2_library::shortcode_atts( array(
	'ticket_id'=>null,
	'scope'=>null,
	'version'=>7.4,
	'app_id'=>null,
	'app_secret'=>null
	), $atts) );
	
	if(empty($ticket_id)) return '';
	if(empty($app_id) || empty($app_secret)) return '';	
	
	$path = plugin_dir_path( __DIR__ );
	
	require_once AWESOME_PATH.'/vendor/autoload.php';
	
	if (isset($_GET['error']) || !isset($_GET['code'])) {
	  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'status',"value"=>'error'],null,null);
	  \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'description',"value"=>$_REQUEST['error_description']],null,null);
	  return;
	}
	$return_value='';
	
	$app_path = \aw2_library::get('app.path');
	
	$client = new \Google_Client();
	$client->setClientId($app_id);
	$client->setClientSecret($app_secret);
	$client->setRedirectUri(SITE_URL.'?social_auth=google');
	
	$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
	$client->setAccessToken($token['access_token']);
  
 
	\aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'access_token',"value"=>$token['access_token']],null,null);
	// perform api call to get profile information
	// get profile info
	$google_oauth = new \Google_Service_Oauth2($client);
	
	$return_value = $google_oauth->userinfo->get();
			   
    \aw2\session_ticket\set(["main"=>$ticket_id,"field"=>'status',"value"=>'success'],null,null);

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}
if(IS_WP){
	\add_action( 'wp', 'aw2\google\auth_check', 11 );

		
	function auth_check(){

		if(!isset($_REQUEST['social_auth'])) return;
		
		if($_REQUEST['social_auth'] !== 'google') return;
		
		$ticket_id = $_COOKIE['google_login'];
		
		$query_string=explode('&',$_SERVER["QUERY_STRING"]);

		array_shift($query_string);
		
		$query_string =  implode('&',$query_string);
		
		$app_path = \aw2\session_ticket\get(["main"=>$ticket_id,"field"=>'app_path'],null,null);
		
		

		wp_redirect($app_path.'/t/'.$ticket_id.'?'.$query_string);

		die();
	}
}	

\aw2_library::add_service('google.auth_offline', 'Get access token for business profile API', ['namespace' => __NAMESPACE__]);

function auth_offline($atts, $content = null, $shortcode) {

    if (\aw2_library::pre_actions('all', $atts, $content) == false) {
        return;
    }

    extract(\aw2_library::shortcode_atts([
    'client_id' => 'client_id',
    'client_secret' => 'client_secret',
    'redirect_uri' => 'redirect_uri',
    'scope' => 'scope'
], $atts));

    if (empty($client_id) || empty($client_secret) || empty($redirect_uri) || empty($scope)) {
        return '';
    }
	
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'google_access_tokens';
	$query = $wpdb->prepare("SELECT * FROM $table_name WHERE client_id = %s ORDER BY id DESC LIMIT 1", $client_id); 
	$tokens = $wpdb->get_row($query, ARRAY_A);
	$access_token = isset($tokens['access_token']) ? $tokens['access_token'] : null;
	$refresh_token = isset($tokens['refresh_token']) ? $tokens['refresh_token'] : null;
	

    // Initialization of Google client instance
    $client = new \Google_Client();
    $client->setClientId($client_id);
    $client->setClientSecret($client_secret);
    $client->setRedirectUri($redirect_uri);
    $client->setScopes([$scope]);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');

    // Check if access token is provided
    if ($access_token) {
    $client->setAccessToken($access_token);
    // Check if access token is expired
    if (!$client->isAccessTokenExpired()) {
      return $access_token;
    } else {

      // Try to refresh access token using refresh token
      if ($refresh_token) {
        try {
          $client->refreshToken($refresh_token);
          $new_access_token = $client->getAccessToken();
			$wpdb->update(
                        $table_name,
                        array('access_token' => $new_access_token['access_token']),
                        array('client_id' => $client_id)
                    );
          return $new_access_token;
        } catch (Exception $e) {
          return false; 
        }
      } else {
        return false; 
      }
    }
  }

  // Check if authorization code is present in the request
  if (isset($_GET['code'])) {
    // Exchange authorization code for access and refresh tokens
    try {
      $client->authenticate($_GET['code']);
      $access_token = $client->getAccessToken();
      $refresh_token = (isset($access_token['refresh_token'])) ? $access_token['refresh_token'] : null;
		
	   // Create table if not exists
        $table_name = $wpdb->prefix . 'google_access_tokens';
        $charset_collate = $wpdb->get_charset_collate();
        $create_table_query = "CREATE TABLE IF NOT EXISTS $table_name (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                client_id VARCHAR(255) NOT NULL,
                access_token VARCHAR(255) NOT NULL,
                refresh_token VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $create_table_query );
        
        // Insert tokens into the table
        $access_token_value = $access_token['access_token'];
        $refresh_token_value = $refresh_token;
        $insert_query = $wpdb->prepare("INSERT INTO $table_name (client_id, access_token, refresh_token) VALUES (%s, %s, %s)", $client_id, $access_token_value, $refresh_token_value);
        $wpdb->query($insert_query);
      return $access_token;
    } catch (Exception $e) {
      return false; 
    }
  }

  // No access token or authorization code, redirect user for authorization
  if (!isset($_GET['code'])) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit;
  }
}

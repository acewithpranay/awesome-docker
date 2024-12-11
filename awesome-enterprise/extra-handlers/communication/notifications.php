<?php
namespace aw2\notify;

\aw2_library::add_service('notify.wpmail','Send wp mail',['namespace'=>__NAMESPACE__]);

function wpmail($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
        'email' => null,
        'log' => null,
        'notification_object_type' => null,    
        'notification_object_id' => null,
        'tracking_set' => null
    ), $atts, 'aw2_wpmail' ) );
    
    // if email is null, return
    if(is_null($email)) return;

    if(!isset($email['to']['email_id']))$email['to']['email_id']='';
    if(!isset($email['subject']))$email['subject']='';
    if(!isset($email['message']))$email['message']='';
	if(!isset($email['headers']))$email['headers']='';
    if(!isset($email['attachment']))$email['attachment']='';
	
	$tracking = array();
    if(!empty($tracking_set))$tracking['tracking_set']=$tracking_set;

    // Log data in db
	require_once __DIR__ .'/includes/notification_helper.php';
    \notification_log('mail', 'wpmail', $email, $log, $notification_object_type, $notification_object_id,$tracking);

	wp_mail( 
        $email['to']['email_id'], 
        $email['subject'], 
        $email['message'], 
        $email['headers'], 
        $email['attachments'] 
    );

    $return_value = "success";
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}

\aw2_library::add_service('notify.sendgrid','Send Sendgrid mail',['namespace'=>__NAMESPACE__]);

function sendgrid($atts,$content=null,$shortcode){
    if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    //including SENDGRID library
	
	//require_once AWESOME_PATH.'/vendor/autoload.php';
        
    extract(\aw2_library::shortcode_atts( array(
		'email' => null,
        'log' => null,
        'notification_object_type' => null,    
        'notification_object_id' => null,
        'tracking_set' => null
    ), $atts, 'aw2_sendgrid' ) );
    
    // if email is null, return
    if(is_null($email)) return;
    
    // Checking for values and setting them if not present.
	if(!isset($email['from']['email_id']))$email['from']['email_id']='';
	if(!isset($email['to']['email_id']))$email['to']['email_id']='';
    if(!isset($email['message']))$email['message']='';
    if(!isset($email['subject']))$email['subject']='';
		
    //provider.apiKey or settings.sendgrid_apiKey
    $apiKey = $email['provider']['key'];

    if(empty($apiKey) || strlen($apiKey) === 0){
        $return_value=\aw2_library::post_actions('all','No api key is not provided, check your settings for default api key!',$atts);
        return $return_value;
    }

    $sendgrid_email = new \SendGrid\Mail\Mail();

    $sendgrid_email->setFrom($email['from']['email_id'], null);
    $sendgrid_email->setSubject($email['subject']);

        //$email['to']['email_id']
    if(isset($email['to']['email_id'])){
        $to_emails = explode(",",$email['to']['email_id']);
        foreach($to_emails as $val){
            $sendgrid_email->addTo($val, null);
        }
    }


    // Content 
    $sendgrid_email->addContent(
        "text/html", $email['message']
    );

    // Works on only when the attachments are present
    if(isset($email['attachments']['file'])){
        
        //storing file array in variable
        $file = $email['attachments']['file'];
        //looping through the file content
        for($i=0; $i<sizeof($file); $i++){
            $name = $file[$i]['name'];
            $path = $file[$i]['path'];
            if(!empty($path)){
                $file_encoded = base64_encode(file_get_contents($path));
                $sendgrid_email->addAttachment($file_encoded, null, $name, "attachment", null);
            }
        }
        
    }

    //$email['cc']['email_id']
    if(isset($email['cc']['email_id'])){
        $cc_emails = explode(",",$email['cc']['email_id']);
        foreach($cc_emails as $val){
             $sendgrid_email->addCc($val, null);
        }
    }

    //$email['bcc']['email_id']
    if(isset($email['bcc']['email_id'])){
        $bcc_emails = explode(",",$email['bcc']['email_id']);
        foreach($bcc_emails as $val){
             $sendgrid_email->addBcc($val, null);
        }
    }

    //$email['reply_to']['email_id']
    if(isset($email['reply_to']['email_id'])){      
        $reply_to_emails = explode(",",$email['reply_to']['email_id']);
        foreach($reply_to_emails as $val){
             $sendgrid_email->setReplyTo($val, null);
        }
    }

    $sendgrid = new \SendGrid($apiKey);

    try {
        $response = $sendgrid->send($sendgrid_email);
    
        //get headers from the response->headers();
        $header = $response->headers();    

        foreach ($header as $val) {
            $val_array = explode(':', $val);

            if($val_array[0] == 'X-Message-Id'){
                //getting the message id from the header response
                $messageId = $val_array[1]; 

                //setting up tracking array
                $tracking['tracking_id'] = trim($messageId) ;
                $tracking['tracking_status'] = 'sent_to_provider';
                $tracking['tracking_stage'] = 'sent_to_provider';
                if(!empty($tracking_set))$tracking['tracking_set']=$tracking_set;
                
	    	// Log data in db
			require_once __DIR__ .'/includes/notification_helper.php';
    		\notification_log('email', 'sendgrid', $email, $log, $notification_object_type, $notification_object_id, $tracking);
		    
                break;
            }
        }

    	$return_value = $response->statusCode();

        if($return_value == 202){
            $return_value = "success";
        }

        
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        $return_value = "error";
    }
    
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
    return $return_value;
}

\aw2_library::add_service('notify.kookoo','Send Kookoo SMS',['namespace'=>__NAMESPACE__]);

function kookoo($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
		'sms' => null,
        'log' => null,
        'notification_object_type' => null,    
        'notification_object_id' => null
    ), $atts, 'aw2_kookoo' ) );

    // if sms is null, return
    if(is_null($sms)) return;

    if(!isset($sms['to']['mobile_number']))$sms['to']['mobile_number']='';
    if(!isset($sms['message']))$sms['message']='';
    if(!isset($sms['provider']['key']))$sms['provider']['key']='';

    // Log data in db
	require_once __DIR__ .'/includes/notification_helper.php';
    \notification_log('sms', 'kookoo', $sms, $log, $notification_object_type, $notification_object_id);

    // api base url
    $url = 'http://www.kookoo.in/outbound/outbound_sms.php';

    $apiKey = $sms['provider']['key'];
	
	
    if(empty($apiKey) || strlen($apiKey) === 0){
        $return_value=\aw2_library::post_actions('all','No api key is not provided, check you settings for default api key!',$atts);
        return $return_value;
    }

    // parameter to send in sms
    $param = array(
        'api_key' => $apiKey,
        'phone_no' => '0'.$sms['to']['mobile_number'], 
        'message' => $sms['message']
    );

    $url = $url . "?" . http_build_query($param, '&');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = simplexml_load_string($result);
	
	$return_value = 'error';
	if(isset($result->status)){
		$return_value= $result->status;
	}
	
    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
}


\aw2_library::add_service('notify.msg91','Send msg91 SMS',['namespace'=>__NAMESPACE__]);

function msg91($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;

    extract(\aw2_library::shortcode_atts( array(
		'sms' => null,
        'log' => null,
        'notification_object_type' => null,    
        'notification_object_id' => null
    ), $atts, 'aw2_kookoo' ) );
	
	// if $sms is not present
    if(is_null($sms)){
        return \aw2_library::post_actions('all','Sms array is required!',$atts);
	}
	
    // Log data in db
	require_once __DIR__ .'/includes/notification_helper.php';
    \notification_log('sms', 'msg91', $sms, $log, $notification_object_type, $notification_object_id);
	
	// check if values are present or not
    if(!isset($sms['to']['mobile_number']))$sms['to']['mobile_number']='';
    if(!isset($sms['message']))$sms['message']='';
    if(!isset($sms['provider']['key']))$sms['provider']['key']='';
	
    // api base url
    $url = 'http://api.msg91.com/api/v2/sendsms';
    $apiKey = $sms['provider']['key'];
	
	// if api key is not present
	if(empty($apiKey) || strlen($apiKey) === 0){
        return $return_value=\aw2_library::post_actions('all','No api key is not provided, check you settings for default api key!',$atts);
    }
	
	// create sms payload Array
	$payloadArr = array(); 
	$payloadArr['sender'] = $sms['provider']['sender'];
	$payloadArr['DLT_TE_ID'] = $sms['dlt_template_id'];
	$payloadArr['route'] = $sms['provider']['route'];
	$payloadArr['country'] = $sms['provider']['country'];
	$payloadArr['sms'][0]['message'] = $sms['message'];
	$payloadArr['sms'][0]['to'][0] = $sms['to']['mobile_number'];
	
	$payload = json_encode($payloadArr);
		
	// use curl to send data
	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array("authkey:$apiKey","Content-Type:application/json"));
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$result = json_decode(curl_exec($ch));
	curl_close($ch);
	
	$return_value= $result->type == 'success' ? 'success' : 'error';

    $return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}

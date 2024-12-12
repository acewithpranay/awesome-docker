<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

    <title><?php wp_title(); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!--<![endif]-->
       
    <style type="text/css">
    
      .ReadMsgBody { width: 100%; background-color: #F6F6F6; }
      .ExternalClass { width: 100%; background-color: #F6F6F6; }
      body { width: 100%; background-color: #f6f6f6; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; font-family: Arial, Times, serif }
      table { border-collapse: collapse !important; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
              
      @-ms-viewport{ width: device-width; }

      @media only screen and (max-width: 639px){
        .wrapper{ width:100%;  padding: 0 !important; }
      }    

      @media only screen and (max-width: 480px){ 
        .centerClass{ margin:0 auto !important; }         
        .imgClass{ width:100% !important; height:auto; }    
        .wrapper{ width:320px; padding: 0 !important; }  
        .header{ width:320px; padding: 0 !important; background-image: url(http://placehold.it/320x400)  }   
        .container{ width:300px;  padding: 0 !important; }
        .mobile{ width:300px; display:block; padding: 0 !important; text-align:center !important;}
        .mobile50{ width:300px; padding: 0 !important; text-align:center; }
        *[class="mobileOff"] { width: 0px !important; display: none !important; }
        *[class*="mobileOn"] { display: block !important; max-height:none !important; }
      }    
    </style>
    
    <!--[if gte mso 15]>
    <style type="text/css">
        table { font-size:1px; line-height:0; mso-margin-top-alt:1px;mso-line-height-rule: exactly; }
        * { mso-line-height-rule: exactly; }
    </style>
    <![endif]-->    
	 <?php
       		
		if(\aw2_library::post_exists("newsletter-styles",AWESOME_CORE_POST_TYPE)){
			echo \aw2_library::module_run(['post_type'=>AWESOME_CORE_POST_TYPE],"newsletter-styles");
		}	

		
      ?>
</head>
<body marginwidth="0" marginheight="0" leftmargin="0" topmargin="0" style="background-color:#F6F6F6; font-family:Arial,serif; margin:0; padding:0; min-width: 100%; -webkit-text-size-adjust:none; -ms-text-size-adjust:none;">

    <!--[if !mso]><!-- -->
    <img style="min-width:640px; display:block; margin:0; padding:0" class="mobileOff" width="640" height="1" src="<?php echo get_template_directory_uri(); ?>/assets/images/spacer.gif">
    <!--<![endif]-->

    <!-- Start Background -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F6F6F6">
        <tr>
            <td width="100%" valign="top" align="center">
                <?php while ( have_posts() ) : the_post();?>
                <?php the_content(); ?>
                <?php endwhile; // end of the loop. ?>
            </td>
        </tr>
    </table>
    <!-- End Background -->
    
</body>
</html>
<?php
namespace aw2\wp;

\aw2_library::add_service('wp.image_resize','Will resize the image file using WordPress functions',['namespace'=>__NAMESPACE__]);


function image_resize($atts,$content=null,$shortcode){
	
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( \aw2_library::shortcode_atts(array(
	'image_url' =>'',
	'width' =>'',
	'height' =>'',
	'crop'=>'no'
	), $atts ) );
	
	if(empty( $image_url )){
		\aw2_library::set_error('one of image_url or image_path is required'); 
		return '';
	}
	
	if($crop=='yes')
		$crop=true;
	else
		$crop=false;
	
	$image_found=false;
	
	
	$image_path = parse_url( $image_url );
	$image_path = ltrim( $image_path['path'], '/' );


    $orig_size = getimagesize( $image_path );
    
	$image_src[0] = $image_url;    
    $image_src[1] = $orig_size[0];
    $image_src[2] = $orig_size[1];

	if (!$image_path) {
		return array(
		  'url' => '#',
		  'path' => '',
		  'width' => -1,
		  'height' => -1
		);
	}
    
	// default output - without resizing
	$vt_image = array (
		'url' => $image_src[0],
		'path' => '',
		'width' => $image_src[1],
		'height' => $image_src[2]
	);

	$file_info = pathinfo( $image_path );
	$extension='';
	if(isset($file_info['extension']))
		$extension = '.'. $file_info['extension'];

	// the image path without the extension
	$no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];

	$cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;
  
    if ( file_exists( $cropped_img_path ) ) {

      $cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
      
      $vt_image = array (
        'url' => $cropped_img_url,
		'path' => $cropped_img_path,
        'width' => $width,
        'height' => $height
      );
      $image_found=true;
    }
	
	$final_img_path = $cropped_img_path;
	
	if ( $crop == false ) {
    
      // calculate the size proportionaly
      $proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
      $resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;      

      // checking if the file already exists
      if ( file_exists( $resized_img_path ) ) {
      
        $resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );
		$new_img_size = getimagesize( $resized_img_path );

        $vt_image = array (
          'url' => $resized_img_url,
		  'path' => $resized_img_path,
          'width' => $new_img_size[0],
          'height' => $new_img_size[1]
        );
		$image_found=true;
      }
	  
	  $final_img_path = $resized_img_path;
    }
  // checking if the file size is larger than the target size if it is smaller or the same size, stop right here and return default
  if ( !$image_found && ($image_src[1] > $width || $image_src[2] > $height )) {
	
	$editor = wp_get_image_editor( $image_path, array() );
	if (!is_wp_error($editor)) {

		// Resize the image.
		$result = $editor->resize($width, $height, $crop);

		// If there's no problem, save it; otherwise, print the problem.
		if (!is_wp_error($result)) {
		  $editor->save($final_img_path);
		}
		
		$new_img = str_replace( basename( $image_src[0] ), basename( $final_img_path ), $image_src[0] );
		$new_img_size = getimagesize( $final_img_path );

		// resized output
		$vt_image = array (
			'url' => $new_img,
			'path' => $final_img_path,
			'width' => $new_img_size[0],
			'height' => $new_img_size[1]
		);
	}

  }
  
	$return_value=\aw2_library::post_actions('all',$vt_image,$atts);
	return $return_value;
  
}
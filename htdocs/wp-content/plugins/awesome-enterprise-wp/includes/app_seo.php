<?php 

//yoast seo

require_once dirname( __FILE__) .'/seo/yoast-seo.php';

require_once dirname( __FILE__) .'/seo/rankmath-seo.php';



class app_seo{
	
	static function enable_sitemap($app){
		
		if(!isset($app['collection']['config'])) return false;
			
		//$arr=aw2_library::get_module($app['collection']['config'],'settings');
		//if(!$arr) return false;
		//aw2_library::module_run($app['collection']['config'],'settings');
		
		$exists=aw2_library::module_exists_in_collection($app['collection']['config'],'settings');
		if(!$exists)return false;
		
		//change to module_meta
		$all_post_meta = aw2_library::get_module_meta($app['collection']['config'],'settings');
		if(!is_array($all_post_meta)) $all_post_meta = array();
		foreach($all_post_meta as $key=>$meta){
			
			//ignore private keys
			if(strpos($key, '_') === 0 )
				continue;
			
			$app['settings'][$key] = $meta;

		}
		\aw2_library::module_run($app['collection']['config'],'settings');

		if(!isset($app['settings']['enable_sitemap'])) return false;
		
		//$enable_sitemap = aw2_library::get_post_meta($arr['id'],'enable_sitemap');
		$enable_sitemap = $app['settings']['enable_sitemap'];
		
		if($enable_sitemap !== 'yes')  return false;
		
		return true;
	}

}	
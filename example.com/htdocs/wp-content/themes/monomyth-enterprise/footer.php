<?php

$app=&aw2_library::get_array_ref('app');

$module='footer';
if(basename( get_page_template() )==='page-landing.php') $module='landing-page-footer';

$post_type=AWESOME_CORE_POST_TYPE;

if(isset($app['collection']['config']['post_type']) && \aw2_library::post_exists($module,$app['collection']['config']['post_type'])){
	$post_type=$app['collection']['config']['post_type'];
}

echo \aw2_library::module_run(['post_type'=>$post_type],$module);
wp_footer(); 

if (current_user_can('develop_for_awesomeui')) {
	echo '<!-- ' . get_num_queries() . ' queries in ' . timer_stop(0,3) . ' seconds -->';
}
?>
</body>
</html>
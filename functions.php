<?php
$autoloader = require_once( str_replace('/wp-content/themes', '', get_theme_root()) .'/vendor/autoload.php');

// turn off autop in wp7forms
add_action('muplugins_loaded',function(){
	define( 'WPCF7_AUTOP', false );
});
// Timber load fix which got broken in V1.1.0 of timber/timber
if (!class_exists('Timber')) {
   new \Timber\Timber;
}

add_action( 'after_setup_theme', function(){
    load_theme_textdomain( 'blankslate', get_template_directory() . '/languages' );
		add_post_type_support( 'page', 'excerpt' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'post-thumbnails' );



    register_nav_menus(array( 'main-menu' => __( 'Main Menu', 'fg-timber' ) ) );

    // removes admin bar if user is signed out, which is a false bar because a user at once was signed in
    $current_user = wp_get_current_user();
    if ( 0 == $current_user->ID){
        show_admin_bar(false);
    }
});

add_filter('the_content',function($content){
	// don't want this annoying p tag wrap and br tag. except for blog posts.
	if ((get_post_type() === 'page') and ( in_array( get_the_ID(), array(2) )) ){
		remove_filter( 'the_content', 'wpautop' );
	}
	return $content;
},1);


add_filter('wp_seo_get_bc_title',function($text){
	$text = str_replace('&nbsp;','',$text);
	return $text;
});


add_action( 'wp_enqueue_scripts', function(){

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script('modernizr', get_template_directory_uri() .'/assets/js/modernizr.js');
	wp_enqueue_script('bootstrap', get_template_directory_uri() .'/assets/js/bootstrap.min.js');
	wp_enqueue_script('theme-master-script', get_template_directory_uri() .'/assets/js/script.js');
	wp_enqueue_script('google-api','http://www.google.com/jsapi/?key=AIzaSyCEcwmi0zRoHvBkXFW475ROm0kQhmwHxek');

	wp_enqueue_style( 'bootstrap', get_template_directory_uri() .'/assets/css/bootstrap.min.css' );
	wp_enqueue_style('fontawesome',get_template_directory_uri() .'/assets/css/font-awesome.min.css');
	if (is_plugin_active('wp-less/bootstrap.php') ){
		wp_enqueue_style('master-style-less',get_template_directory_uri() .'/assets/less/style.less');
	}
	else {
		wp_enqueue_style('master-style',get_template_directory_uri() .'/assets/css/style.css');
	}


},20);
// if has wp less plugin then compress.
add_action('wp-less_init', function($WPLess) {
  $WPLess->getCompiler()->setFormatter('compressed');
});

add_filter( 'the_title', function($title){
    if ( $title == '' ) {  return '&rarr;'; }
	else 				{  return $title;   }
});

add_action('save_post', function($post_id){
	// clear cache if post save.
	$loader = new TimberLoader();
	$loader->clear_cache_timber();
	$loader->clear_cache_twig();

});

add_action( 'widgets_init', function() {
	register_sidebar(array(
		'name' => 		'Footer - Left',
		'id'=> 			'widget_footer_sidebar_left',
		'before_widget' => '<li id="%1$s" class="widget-container  %2$s">',
		'after_widget' => "</li>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'class' => 'widget_footer_sidebar_left',

	));
	register_sidebar(array(
		'name' => 		'Footer - Right',
		'id'=> 			'widget_footer_sidebar_right',
		'before_widget' => '<li id="%1$s" class="widget-container widget_footer_sidebar_right %2$s">',
		'after_widget' => "</li>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'class' => 'widget_footer_sidebar_right',
	));
	register_sidebar(array(
		'name' => 		'Sidebar (Home)',
		'id'=> 			'widget_home_sidebar',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => "</li>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'class' => 'widget_home_sidebar',

	));
	register_sidebar(array(
		'name' => 		'Sidebar (Page)',
		'id'=> 			'widget_page_sidebar',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => "</li>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'class' => 'widget_page_sidebar',

	));
	register_sidebar(array(
		'name' => 		'Sidebar (Blog)',
		'id'=> 			'widget_blog_sidebar',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => "</li>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'class' => 'widget_blog_sidebar',

	));
	register_sidebar(array(
		'name' => 		'Sidebar (Contact)',
		'id'=> 			'widget_contact_sidebar',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => "</li>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'class' => 'widget_contact_sidebar ',

	));
});
// adding config and context data into templates
add_filter('wpcf7_fg_email_data','wpcf7_fg_email_data');
function wpcf7_fg_email_data($data){
	global $get_config;
	return array_merge($data,Timber::get_context(),array('config'=>$get_config()));
}

// adding shortcode / or twig render.
add_filter('widget_text', function($text) {
	$text = do_shortcode($text);
	return $text;
});

$get_config=call_user_func(function() {
	return function() use (&$config) {
		// lets see if we have a cached version
		if (!is_null($config)){
			return $config;
		}
		$cache = array('config_ts'=> get_transient('fg_config_timestamp'),
					   'config'=>get_transient('fg_config')
				);
		$errors = array();
		$theme = get_option('theme_directory','default');

		$config_file = get_stylesheet_directory().'/config/settings.yml';
		if (!file_exists ( $config_file ) ) {
			echo 'Error Config file not found';
			return;
		}
		$filetime = hashDirectory(get_stylesheet_directory().'/config/');
		// checking if settings.yml has changed if so lets update, but only if is valid
		if ($cache['config_ts'] != $filetime) {
			$config=@file_get_contents($config_file);
			if ($config===false) die('Could not read '.$config_file);
			// this compiles all context settings in config file
			$config = Timber::compile_string($config, Timber::get_context());

			try {
				$config=\Symfony\Component\Yaml\Yaml::parse($config);
				//this loads any external yamls file
				updateExternalYaml($config);

			}
			catch(\Symfony\Component\Yaml\Exception\ParseException $e){
				$errors[] = 'ParseException in function.php Line '. __LINE__ . ' '. $e;
			}
			// have a good config, lets update tranient values
			if (is_array($config) && (count($errors) === 0)){
				set_transient('fg_config',$config,0);
				set_transient('fg_config_timestamp',$filetime,0);
			}
			else {
				//means an error in yaml settings. -- need a way to notify user it failed
				$config = $cache['config'];
			}
		}
		// no changes, so load transients.
		else {
			$config = $cache['config'];

		}
		//adding errors
		$config['ERRORS'] = $errors;

		$dirnameChildTheme = get_stylesheet_directory(). '/twig-templates';
		$dirnameTheme = get_template_directory(). '/twig-templates';



		Timber::$dirname=array('twig-templates');

		 //setting up timber twig file locations
		 // it will look in theme first, if it doesn't find it it will look in master
		$timberLocationsArray = array($dirnameChildTheme,
								 $dirnameChildTheme.'/wp',
								 $dirnameChildTheme.'/partials',
								 $dirnameChildTheme.'/email',
								 $dirnameChildTheme.'/form');
		// only merge if seperate;
		if (( $dirnameChildTheme !== $dirnameTheme ) ){

			$timberLocationsArray = array_merge($timberLocationsArray, array(	$dirnameTheme,
																				$dirnameTheme.'/wp',
																				$dirnameTheme.'/partials',
																				$dirnameTheme.'/email',
																				$dirnameTheme.'/form')
												);
		}
		// adding filter to add twig locations
		$timberLocationsArray = apply_filters('fg_theme_master_twig_locations', $timberLocationsArray);
		Timber::$locations=	 $timberLocationsArray;
		$config = apply_filters('fg_theme_master_config', $config);
		return $config;
	};
});

if (is_admin()){
	require_once(__DIR__.'/include/theme-settings.php');
	require_once(__DIR__.'/include/html-title-metadata.php');
}
else {
	require_once(__DIR__.'/include/shortcodes.php');
}

function updateExternalYaml(Array &$config){
	$results = array();
	array_find_deep($config, '@import', $results);
	if (count($results)){
		foreach($results as $keys){
			$ref = &$config;
			$index = $keys[count($keys) -2];
			$file = '';
			$value = '';
			while( count($keys) !== 0){
				if ($keys[0] === $index){
					$value = &$ref[$keys[0]];
					$ref = &$ref[$keys[0]];
				}
				// transfverse
				else {
					$ref = &$ref[$keys[0]];
				}

				array_shift($keys);
			}
			$file = $ref;

			$external = '-no info';
			$external_data = false;
			$external_file = get_stylesheet_directory().'/config/'.$file;
			if (!file_exists ( $external_file ) ) {
				$external =  'Error Config file not found ' . $external_file;
			}
			else {
				$external_data=@file_get_contents($external_file);


				if ($external_data===false) {
					$external = 'Could not read '.$external_file;
				}
				else {
					try {
						$external_data = Timber::compile_string($external_data, Timber::get_context());
						$external=\Symfony\Component\Yaml\Yaml::parse($external_data);
					}
					catch(\Symfony\Component\Yaml\Exception\ParseException $e){
						$external = 'ParseException in function.php Line '. __LINE__ . ' '. $e;
					}
				}
			}
			$value = $external;
			unset($value);
		}
	}
}

function array_find_deep( $array, $search, &$results, $keys=array() ){
	foreach($array as $key => $value) {
		if (is_array($value)) {
			$sub = array_find_deep($value, $search, $results, array_merge($keys, array($key) ));
			if (count($sub)) {
					return $sub;
			}
		}
		elseif ($key === $search) {
			$results[] =  array_merge($keys, array($key));
			$keys = array();
		}
	}
	return array();
}

function hashDirectory($directory){
    if (! is_dir($directory))
    {
        return false;
    }
    $fileTimes = array();
    $dir = dir($directory);
    while (false !== ($file = $dir->read()))   {
        if ($file != '.' and $file != '..')  {
            if (is_dir($directory . '/' . $file))  {
                $fileTimes[] = hashDirectory($directory . '/' . $file);
            }
            else  {

                $fileTimes[] =  filemtime($directory . '/' . $file);
            }
        }
    }
    $dir->close();
    return md5(implode('', $fileTimes));
}
function get_fg_menu($id=2){
	return new TimberMenu($id);
}
function get_fg_media_category($cat=0){
	$args = [
		'post_type' => 'attachment',
		'post_status' => 'inherit',
		'posts_per_page' => 500
	];
	if ($cat > 0){

		$args['tax_query'] = [
			[
				'taxonomy' 	=> 'media_category',
				'field' 		=> 'id',
				'terms' 		=> $cat
			]
		];

	}
	//return $args;
	//return new WP_Query( $args );
	return Timber::get_posts($args);
}

function get_fg_post_type($post_type='post', $limit=null, $orderby=null,$order='DESC', $metakey=false) {
	$args = [
		'post_type'				=> $post_type
	];
	if (intval($limit) > 0){ $args['posts_per_page'] = $limit; }
	if (!empty($order)){ $args['order'] = $order; }
	if (!empty($orderby)){ $args['orderby'] = $orderby; }


	if ($metakey !== false ){
		$args['meta_key'] = $metakey;
	}
	$posts =  Timber::get_posts($args);
	return $posts;
}


add_image_size( 'piklist', 50, 50, false );

function get_timber_menu($menu='main'){
	return new TimberMenu($menu);
}

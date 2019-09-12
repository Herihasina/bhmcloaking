<?php
/**
 * @package BHMCloaking
 * @version 1.0
 */
/*
*	Plugin Name: BHMCloaking
*	Version: 1.0
*	Description: Plugin de cloaking
*	Author: herihasina
* Author URI: https://profiles.wordpress.org/herihasina/
* Licence: GPL
* Plugin URI: http://dev.test/
*/
?>
<?php

require( $_SERVER['DOCUMENT_ROOT'] .'/wp-load.php' );

// require dirname(__FILE__).'/inc/updater.php';

// Create backoffice menu (under > Tools)
function hcloak_options_page()
{
	add_menu_page(
		'BHM Cloaking',
		'BHM Cloaking',
		'manage_options',
		'bhmcloaking',
		'hcloak_page',
		'none',
		65
	);
}
add_action('admin_menu', 'hcloak_options_page');

function hcloak_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'Droit d\'accès insiffisant.' ) );
	}
	require_once dirname( __FILE__ ) . '/inc/settings.php';
}

/******************************************************/


add_action(	'admin_enqueue_scripts', 'hcloak_style'); 
function hcloak_style() {
	wp_enqueue_style( 'hcloak_style', plugins_url( '/inc/hcloak.css', __FILE__ ) );
	wp_enqueue_script( 'hcloak_js', plugins_url( '/inc/hcloak.js', __FILE__ ) );
}

function hcloak_options() {
	register_setting('hcloak_options','hcloak_shortcode');
	register_setting('hcloak_options','hcloak_301');
	register_setting('hcloak_options','hcloak_301_general');
	register_setting('hcloak_options','hcloak_url_redirect_cat');
	register_setting('hcloak_options','hcloak_noarchive');
	register_setting('hcloak_options','hcloak_content');
	register_setting('hcloak_options','hcloak_content_textarea');
	register_setting('hcloak_options','hcloak_category');
	register_setting('hcloak_options','hcloak_wysiwyg');
	register_setting('hcloak_options','hcloak');
	register_setting('hcloak_options','hcloak_visitor_google');
	register_setting('hcloak_options','hcloak_referer_domain');
	register_setting('hcloak_options','hcloak_referer_redirect');

	$cats = get_categories(['hide_empty' => 0]);
	foreach ($cats as $key => $cat) {
		register_setting('hcloak_options','hcloak_'.$key);
		register_setting('hcloak_options','hcloak_url_'.$key);
		register_setting('hcloak_options','hcloak_url_'.$cat->slug);
		register_setting('hcloak_options','hcloak_cat_'.$cat->slug);
	}
	
}
add_action( 'admin_init','hcloak_options' );

register_deactivation_hook(__FILE__, 'del_custom_option');
function del_custom_option() {
	$options_names = array('hcloak_shortcode','hcloak_301_general', 'hcloak_301','hcloak_noarchive','hcloak_content','hcloak_category','hcloak_url_redirect','secret_key','hcloak_url_redirect_cat','hcloak');
		foreach ($options_names as $option) {
			update_option('activated','0');
			delete_option($option);
		}
	
	
}


/**********************************
* Check if human
************************************/
function not_robot() {
	// Detect if robots by browser user agent
	$browser = preg_match('/(.*)(googlebot|msnbot|slurp|bing)(.*)/i', filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'));
  $browser = $browser !== 1 ? false : true;
  
  // Detect robots by DNS
  $dns = preg_match('/(.*)(googlebot|msnbot|slurp|bing)(.*)/i', gethostbyaddr(filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING)));
  $dns = $dns !== 1 ? false : true;

  // Detect robots by IP address
  $address = preg_match('/(66\.249\.|72\.14\.)(.*)/i', filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING));
  $address = $address !== 1 ? false : true;

  // detect robots by wp uri
  $uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);  
  $wp = preg_match('/^(\/wp-.*)/i', $uri) !== 1 ? false : true;

  if (!$address && !$browser && !$dns && !$wp) {
  	return true; //not a robot
  }else{
  	return false;
  }
}

/**********************************
* 301 redirect checkbox          *
***********************************/
function cloak_create_metabox() {
		
		add_meta_box(
			'cloak_metabox', // Metabox ID
			'Cloak 301', // Title to display
			'cloak_render_metabox', // Function to call that contains the metabox content
			'post', // Post type to display metabox on
			'normal', 
			'default' // Priority
		);
		
	}
	
	
	function cloak_render_metabox() {
		// Variables
		global $post; // Get the current post data
		$details = get_post_meta( $post->ID, 'cloak', true ); // Get the saved values
		echo 
			'<fieldset class="fbox">
				<label>Rediriger cet article vers :</label>
				<div>
					<input type="text" class="url-post" name="cloak_custom_metabox" placeholder="http://domain.com" value="'.$details.'" ?>">
				</div>
			</fieldset>';

		// Security field
		wp_nonce_field( 'cloak_form_metabox_nonce', 'cloak_form_metabox_process' );
	}
	//
	// Save data
	
	function cloak_save_metabox( $post_id, $post ) {
		// Verify that our security field exists. If not, bail.
		if ( !isset( $_POST['cloak_form_metabox_process'] ) ) return;
		// Verify data came from edit/dashboard screen
		if ( !wp_verify_nonce( $_POST['cloak_form_metabox_process'], 'cloak_form_metabox_nonce' ) ) {
			return $post->ID;
		}
		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}
		if ( !isset( $_POST['cloak_custom_metabox'] ) ) {
			return $post->ID;
		}
		
		$sanitized = wp_filter_post_kses( $_POST['cloak_custom_metabox'] );
		// Save our submissions to the database
		update_post_meta( $post->ID, 'cloak', $sanitized );
	}

add_action('init', 'redirect_301');

function redirect_301() {
	
	$url = get_post_meta(retrieve_id(), 'cloak', true);
	$url_general = get_option('hcloak_301_general');
	
	if ( get_option("hcloak") == "cloak_301"){
		add_action( 'add_meta_boxes', 'cloak_create_metabox' );
		add_action( 'save_post', 'cloak_save_metabox', 1, 2 );
		if( !is_admin() && not_robot()) {
			// input 301 in post has priority
			if ( filter_var($url, FILTER_VALIDATE_URL) != "" ) {
				header( sprintf("Location: %s", filter_var($url, FILTER_VALIDATE_URL) ) );
		 		exit;
			}else if( filter_var($url_general, FILTER_VALIDATE_URL) != "" ) {
				header( sprintf("Location: %s", filter_var($url_general, FILTER_VALIDATE_URL) ) );
		 		exit;
			}
			
		}
	}
	
}	
/******************************************
******************************************************************************************************************/

/**********************************
* cloak shortcode          *
***********************************/
	function hcloak_shortcode_init()
	{		
		    function hcloakGoogle_shortcode($atts = [], $content = null)
		    {
		    		// humans dnt see content
		    		if ( not_robot() ){
		    			$content = null;
		    		}else{//robots only see content
		    			$content = $content;
		    		}		    		

		        return $content;
		    }
		    add_shortcode('cloakGoogle', 'hcloakGoogle_shortcode');

		    function hcloakVisitor_shortcode($atts = [], $content = null)
		    {
		    	if ( not_robot() ){
	    			$content = $content;
	    		}else{//robots dont see content
	    			$content = null;
	    		}	
		      return $content;
		    }
		    add_shortcode('cloakVisitor', 'hcloakVisitor_shortcode');
		
	}
	if ( get_option('hcloak_visitor_google') == "hcloak_visitor_google" )
		add_action('init', 'hcloak_shortcode_init');
/*************************************************
***********************************************************************************************************************/

// cloak meta tag

add_action('wp_head', 'hcloak_noarchive');

function hcloak_noarchive() {
	if ( get_option('hcloak_noarchive') == 'hcloak_noarchive' ) {
		echo '<meta name="robots" content="noarchive"><meta name="robots" content="nosnippet">';
	}
}
/***********************************************
***********************************************************************************************************************/

function redirect_category(){
	$currentUri = $_SERVER['REQUEST_URI'] ;
	$currentUri = explode("/", $currentUri);
	if ( count($currentUri) == 4 && $currentUri[1] == "category" ) {
		$currentCateg = $currentUri[2];

		$tmp = get_categories(['hide_empty' => 0]);
		$count = count($tmp);
		
		for ($i=0; $i < $count; $i++) {
			$x = $tmp[$i]->slug;
			 if ( (get_option('hcloak') == 'cloak_category')
			 			&& get_option("hcloak_".$i) 
			 			&& ($x == $currentCateg)
			 			&& not_robot() ) {
			 				if ( filter_var(get_option("hcloak_url_".$i), FILTER_VALIDATE_URL) != "" ) {
			 						header( sprintf("Location: %s", filter_var(get_option("hcloak_url_".$i), FILTER_VALIDATE_URL) ) );
			 						exit;			 					
			 				}else{
			 						wp_die('Erreur au niveau de l\'URL de redirection : '. get_option("hcloak_url_".$i) .' <br> <a href="'.home_url().'">Retourner au site</a>');
			 				}
			 		
			 }
		}
	}
}
add_action('init', 'redirect_category');

function get_post_category() {
	$id = retrieve_id();
	$cats = get_the_category($id);
	foreach ($cats as $cat){
		$tmp = get_categories(['hide_empty' => 0]);
		foreach ($tmp as $key => $ct) {
			 if ( (get_option('hcloak') == 'cloak_category')
			 			&& get_option("hcloak_".$key) 
			 			&& $ct->slug == $cat->slug
			 			&& not_robot() ) {
			 				if ( filter_var(get_option("hcloak_url_".$key), FILTER_VALIDATE_URL) != "" ) {
				 					header( sprintf("Location: %s", filter_var(get_option("hcloak_url_".$key), FILTER_VALIDATE_URL) ) );
				 					exit;
			 				}else{
			 						wp_die('Erreur au niveau de l\'URL de redirection : '. get_option("hcloak_url_".$key) .' <br> <a href="'.home_url().'">Retourner au site</a>');			 					
			 				}
			 }
		}
	}

}
add_action('init', 'get_post_category');

function retrieve_id() {
  return url_to_postid((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
}	
/************************************************************
******************************************************************************************/

/**********************************
* Cloaking content        *
***********************************/
if ( get_option('hcloak') == 'cloak_content' ) {
	if ( not_robot() ){
		add_filter('the_content', 'cloak_filter_content');
	}

	function cloak_filter_content($content) {		
		$details = get_option('hcloak_content_textarea');
		if ($details != "") {
			return $details;
		}		
		return $content;
	}
}

/******************************
Cloaking referer *************/
	if ( get_option('hcloak') == "hcloak_referer"){
		// add_action('init', 'cloak_referer');
		$origin = @$_SERVER["HTTP_REFERER"];
		if (!is_null($origin) ) {
			$origin = parse_url($origin); 
			if ( array_key_exists('host', $origin) ){
				$origin_domain = $origin['host']; 
				$server_name   = $_SERVER['SERVER_NAME']; 
				$domain_referer= parse_url(get_option('hcloak_referer_domain'));
				if ( array_key_exists('host', $domain_referer) ){
					$domain_referer = $domain_referer['host'];
				}else{
					$domain_referer = get_option('hcloak_referer_domain'); 
				}
				
				if ( $origin_domain != $server_name )
					if (strpos($origin_domain, $domain_referer) !== false) {
					 	add_action( "wp_head", "redirect_referer" );
					}

			}
		}else{
			add_action( 'admin_notices', 'notice_no_header' );
		}
	}

function notice_no_header(){
		echo 
		'<div class="notice notice-error is-dismissible">
			<p>
				BHM Cloaking : Référent non valide. <a href="https://www.php.net/manual/fr/reserved.variables.server.php" title="savoir plus" target="_blank">En savoir plus</a>. 
			</p>
		</div>';
}

function redirect_referer(){
	echo '<meta http-equiv="refresh" content="2; url='. filter_var( get_option( "hcloak_referer_redirect" ), FILTER_VALIDATE_URL)  .'">';
}


add_filter('site_transient_update_plugins', 'bhm_push_update' );
 
function bhm_push_update( $transient ){

	if ( empty($transient->checked ) ) {
            return $transient;
        }
 
	// trying to get from cache first, to disable cache comment 10,20,21,22,24
	if( false == $remote = get_transient( 'bhm_upgrade_bhmcloaking' ) ) {

		// info.json is the file with the actual plugin information on your server
		$remote = wp_remote_get( 'http://dev.remote/info.json', array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			) )
		);
 
		if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {
			set_transient( 'bhm_upgrade_bhmcloaking', $remote, 43200 ); // 12 hours cache
		}
 
	}
 
	if( $remote ) {
  
		$remote = json_decode( $remote['body'] );
 
		// your installed plugin version should be on the line below! You can obtain it dynamically of course 
		if( $remote && version_compare( '1.0', $remote->version, '<' ) && version_compare($remote->requires, get_bloginfo('version'), '<' ) ) {
			$res = new stdClass();
			$res->slug = 'bhmcloaking';
			$res->plugin = 'bhmcloaking/bhmcloaking.php'; // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
			$res->new_version = $remote->version;
			$res->tested = $remote->tested;
			$res->package = $remote->download_url;
           		$transient->response[$res->plugin] = $res;
           		//$transient->checked[$res->plugin] = $remote->version;
           	}
	}
        return $transient;
}
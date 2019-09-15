<?php
/**
 * @package BHMCloaking
 * @version 1.0.0
 */
/*
*	Plugin Name: BHMCloaking
*	Version: 1.0.0
*	Description: Plugin de cloaking
*	Author: Black Hat Money
* Author URI: http://blackhat.money
* Licence: GPL
*/
?>
<?php

require( $_SERVER['DOCUMENT_ROOT'] .'/wp-load.php' );
require_once dirname( __FILE__ ) . '/inc/func.php';

//custom updates/upgrades
$this_file = __FILE__;
$update_check = "http://dev.remote/update.chk";
require_once dirname( __FILE__ ) . '/inc/gill-updates.php';

add_action('admin_menu', 'hcloak_options_page');

add_action(	'admin_enqueue_scripts', 'hcloak_style'); 

add_action( 'admin_init','hcloak_options' );

register_deactivation_hook(__FILE__, 'del_custom_option');

if ( get_option("hcloak") == "cloak_301")
	add_action('init', 'redirect_301');

if ( get_option('hcloak_visitor_google') == "hcloak_visitor_google" )
	add_action('init', 'hcloak_shortcode_init');

// cloak meta tag
if ( get_option('hcloak_noarchive') == 'hcloak_noarchive' ) 
	add_action('wp_head', 'hcloak_noarchive');

if (get_option('hcloak') == 'cloak_category') 
	if ( !is_admin() ){
		add_action('init', 'redirect_category');
		add_action('init', 'get_post_category');
		if ( get_option("block_all") )
			add_action('template_redirect', 'die_other');
	}

if ( get_option('hcloak') == 'cloak_content' ) 
	if ( not_robot() )
		add_filter('the_content', 'cloak_filter_content');

/******************************
Cloaking referer *************/
if ( get_option('hcloak') == "hcloak_referer")
	cloak_referer();	

add_action('admin_notices', 'settings_invite');


add_filter('plugins_api', 'bhmcloaking_update_info', 20, 3);

function bhmcloaking_update_info( $res, $action, $args ){
 
	if( $action !== 'plugin_information' )
		return false;
 
	if( 'BHMCloaking' !== $args->slug )
		return $res;
 
 
		$remote = wp_remote_get( 'https://gofile.io/?c=HpzsKv', array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			) )
		);
 
		if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {
			set_transient( 'upgrade_BHMCloaking', $remote, 43200 ); // 12 hours cache
		}
 	$res = '';
	if( $remote ) {
		$remote =  json_decode($remote['body']); 
		$res = new stdClass();
		$res->name = $remote->name;
		$res->slug = $remote->slug;
		$res->requires = "4";
		$res->version = $remote->version;
		$res->author = "Black Hat Money";
		$res->author_profile = $remote->author;
		$res->download_link = $remote->author_homepage;
		$res->trunk = "http://trunk.lnk";
		$res->last_updated = "09/15/2019";
		$res->sections = array(
			'description' => $remote->sections->description, // description tab
			'installation' => $remote->sections->installation, // installation tab
		);
 
 
		$res->banners = array(
			'low' => dirname( __FILE__ ). '/inc/ban-logo-dark.jpg',
      'high' => dirname( __FILE__ ). '/inc/ban-logo-dark.jpg'
		);
		
           	
 
	}
 return $res;
 
}

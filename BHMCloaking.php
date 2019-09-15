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

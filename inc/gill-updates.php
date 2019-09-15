<?php
//Exclude from WP updates
function gill_updates_exclude( $r, $url ) {
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) )
		return $r; // Not a plugin update request. Bail immediately.
	$plugins = unserialize( $r['body']['plugins'] );
	unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
	unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
	$r['body']['plugins'] = serialize( $plugins );
	return $r;
}

add_filter( 'http_request_args', 'gill_updates_exclude', 5, 2 );


//Returns current plugin info.
function gill_plugin_get($i) {
	global $this_file;
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$plugin_folder = get_plugins( '/' . plugin_basename( dirname( $this_file ) ) );
	$plugin_file = basename( ( $this_file ) );
	return $plugin_folder[$plugin_file][$i];
}

//check for update twice a day (same schedule as normal WP plugins)
// register_activation_hook($this_file, 'gill_check_activation');
// add_action('gill_check_event', 'gill_check_update');
// function gill_check_activation() {
// 	wp_schedule_event(time(), 'twicedaily', 'gill_check_event');
// }
add_action('admin_init','gill_check_update');
function gill_check_update() {
	global $wp_version;
	global $this_file;
	global $update_check;
	$plugin_folder = plugin_basename( dirname( $this_file ) );
	$plugin_file = basename( ( $this_file ) );
	if ( defined( 'WP_INSTALLING' ) ) return false;

	$response = wp_remote_get( $update_check );
	list($version, $url) = explode('|', $response['body']);
	if(gill_plugin_get("Version") == $version) return false;
	$plugin_transient = get_site_transient('update_plugins');
	$a = array(
		'slug' => $plugin_folder,
		'new_version' => $version,
		'url' => gill_plugin_get("AuthorURI"),
		'package' => $url
	);
	$o = (object) $a;
	$plugin_transient->response[$plugin_folder.'/'.$plugin_file] = $o;
	set_site_transient('update_plugins', $plugin_transient);
}

//remove cron task upon deactivation
register_deactivation_hook($this_file, 'gill_check_deactivation');
function gill_check_deactivation() {
	wp_clear_scheduled_hook('gill_check_event');
}

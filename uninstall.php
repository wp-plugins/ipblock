<?php

	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
 
	$option_name = 'ipblock-settings';
 
	delete_option( $option_name );
 
	global $wpdb;
	wp_clear_scheduled_hook('ipblock_records_cleanup');
	$dbtab=$wpdb->prefix.'ipblock';
	$wpdb->query("DROP TABLE IF EXISTS $dbtab");



?>

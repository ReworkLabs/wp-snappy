<?php
/**
* Uninstaller
*
*/

// If the uninstall was not called by WordPress, exit

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit(); }

// Delete options
delete_option( 'wp_snappy_settings' );
?>
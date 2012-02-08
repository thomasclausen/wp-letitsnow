<?php
/*
Plugin Name: Let it snow, let it snow, let it snow...
Description: For the holiday season.
Version: 0.2
License: GPLv2
Author: Thomas Clausen
Author URI: http://www.thomasclausen.dk/wordpress/
*/

// Add snowstorm script
function letitsnow_scripts() {
	wp_register_script( 'letitsnow-script', plugins_url( '/letitsnow.js', __FILE__ ), array( 'jquery' ), '0.2' );
	wp_enqueue_script( 'letitsnow-script' );
}
add_action( 'wp_enqueue_scripts', 'letitsnow_scripts' ); ?>
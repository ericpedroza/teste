<?php

/**
 * Create an empty object if the ZN framework/ function is not available
 */
if( ! function_exists( 'ZN' ) ){
	function ZN(){
		return new stdClass();
	}
}

// Define constants
define( 'ZN_FW_VERSION', ZNHGTFW()->getVersion() );
define( 'THEME_BASE', get_template_directory() );
define( 'CHILD_BASE', get_stylesheet_directory() );
define( 'THEME_BASE_URI', esc_url( get_template_directory_uri() ) );
define( 'CHILD_BASE_URI', esc_url( get_stylesheet_directory_uri() ) );

// Set object vars
ZN()->version = ZN_FW_VERSION;

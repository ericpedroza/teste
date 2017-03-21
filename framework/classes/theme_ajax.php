<?php if ( !defined( 'ABSPATH' ) )
{
	return;
}

// Register AJAX actions
add_action( 'wp_ajax_zn_ajax_callback', 'zn_ajax_callback' );
add_action( 'wp_ajax_znhgkl_refresh_pb_data', 'znhgkl_refresh_pb_data' );

function znhgkl_refresh_pb_data(){
	ZN()->pagebuilder->refresh_pb_data();
	znklfw_regenerate_dynamic_css();
	die();
}

//<editor-fold desc="::: AJAX CALLBACKS">
function zn_ajax_callback()
{

	check_ajax_referer( 'zn_framework', 'zn_ajax_nonce' );

	$save_action = $_POST[ 'zn_action' ];

	if ( $save_action == 'zn_save_options' )
	{

		// DO ACTION FOR SAVED OPTIONS
		do_action( 'zn_save_theme_options' );

		$data = json_decode( stripslashes( $_POST[ 'data' ] ), true );

		/* REMOVE THE HIDDEN FORM DATA */
		unset( $data[ 'action' ] );
		unset( $data[ 'zn_action' ] );
		unset( $data[ 'zn_ajax_nonce' ] );

		$options_field = $data[ 'zn_option_field' ];

		// Combine all options
		// Get all saved options
		$saved_options = zget_option( '', '', true );
		$saved_options[ $options_field ] = $data;

		$result = znklfw_save_theme_options( $saved_options );

		if ( $result == 0 || $result )
		{
			echo 'Settings successfully save';
			die();
		}
		else
		{
			echo 'There was a problem while saving the options';
			die();
		}

	}

	die( 'Are you cheating ?' );

}


//</editor-fold desc="::: AJAX CALLBACKS">

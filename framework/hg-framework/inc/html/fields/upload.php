<?php

class ZnHgFw_Html_Upload extends ZnHgFw_BaseFieldType{

	function render ( $value ) {

		// ONLY ALLOW SUPER ADMINS TO UPLOAD NEW ICONS
		if ( !current_user_can( 'update_plugins' ) ){
			return 'You need super admin capabilities to use this option!';
		}

		// GET/SET DEFAULTS
		$supports = $value['supports'];
		$output = '';

		// CHECK TO SEE IF THE FILE TYPE IS ALLOWED
		// CHECK ON MULTISITE
		if ( is_multisite() && strpos( get_site_option( 'upload_filetypes' ), $supports['file_extension'] ) === false )
		{
			return 'It seems that the '.$supports['file_extension'].' file type is not allowed on your multisite enable network. Please go to <a title="Network settings page" href="'.network_admin_url('settings.php').'"">Network settings page</a> and add the '.$supports['file_extension'].' file extension to the list of "Upload file types"';
		}

		$output .= '<div class="zn_file_upload zn_admin_button" data-file_type="'.$supports['file_type'].'" data-button="Upload" data-title="Upload File">Select file</div>';
		$output .= '<div class="uploads_container">';

			$fonts = ZNHGFW()->getComponent('icon_manager')->get_custom_fonts();

			if( !empty( $fonts ) ) {
				foreach ( $fonts as $key => $font ) {
					$output .= '<a class="zn_remove_font" href="#">'.$key.'<span data-font_name="'.$key.'" class="zn_remove_font_trigger">&#215;</span></a> ';
				}
			}

		$output .= '</div>';

		return $output;

	}

}

return new ZnHgFw_Html_Upload();

<?php if(! defined('ABSPATH')){ return; }

/*
*	Sanitize theme options
*	Will convert the string to a database sage option string
*/
function zn_fix_insecure_content($url){
	return preg_replace('#^https?://#', '//', $url);
}

add_action('zn_save_theme_options', 'zn_refresh_mailchimp_lists');
function zn_refresh_mailchimp_lists(){
	delete_option( 'zn_mailchimp_lists' );
}

/*--------------------------------------------------------------------------------------------------
	Adds user generated custom css
--------------------------------------------------------------------------------------------------*/
add_filter( 'zn_dynamic_css', 'add_custom_css', 100 );
function add_custom_css( $css ){

	$saved_css = get_option( 'zn_'.ZN()->theme_data['theme_id'].'_custom_css', '' );
	$new_css = $css  . $saved_css;

	return $new_css;
}

add_action( 'wp', 'zn_fw_custom_js' );
function zn_fw_custom_js(){

	$custom_js = get_option( 'zn_'.ZN()->theme_data['theme_id'].'_custom_js' );

	if( ! empty( $custom_js ) ){
		$custom_js = array( 'theme_custom_js' => $custom_js );
		ZNHGFW()->getComponent('scripts-manager')->add_inline_js( $custom_js );
	}

}


/*--------------------------------------------------------------------------------------------------
	Get option - This function will return the option
	@option : if specified, returns the option value , if not, returns the full list of category options
	@category : returns the saved options category
--------------------------------------------------------------------------------------------------*/
	global $saved_options;
	$saved_options = '';

	function zget_option( $option, $category = false , $all = false , $default = false ) {

		global $saved_options;

		if ( !ZN()->theme_data ) {
			return false;
		}

		if ( empty( $saved_options ) ) {
			$saved_options = get_option( ZN()->theme_data['options_prefix'] );
		}

		if ( $all ){
			return $saved_options;
		}

		if ( !empty($saved_options[$category][$option]) || ( isset($saved_options[$category][$option]) && $saved_options[$category][$option] === '0' ) ) {
			$return = $saved_options[$category][$option];
		}
		elseif( isset( $default ) ){
			$return = $default;
		}
		else {
			$return = false;
		}

		return $return;
	}

	function zn_uid( $prepend = 'eluid', $length = 8 ){
		return $prepend . substr(str_shuffle(MD5(microtime())), 0, $length);
	}


	function zn_create_folder( &$folder, $addindex = true ) {
		if( is_dir( $folder ) && $addindex == false)
			return true;

		$created = wp_mkdir_p( trailingslashit( $folder ) );
		// SET PERMISSIONS
		@chmod( $folder, 0777 );

		if($addindex == false) return $created;

		// ADD AN INDEX.PHP FILE
		$index_file = trailingslashit( $folder ) . 'index.php';
		if ( file_exists( $index_file ) )
			return $created;

		$handle = @fopen( $index_file, 'w' );
		if ($handle)
		{
			fwrite( $handle, "<?php\r\necho 'Directory browsing is not allowed!';\r\n?>" );
			fclose( $handle );
		}

		return $created;
	}

	function zn_delete_folder( $path ) {
		//echo $path;
		//check if folder exists
		if( is_dir( $path) )
		{

			$it = new RecursiveDirectoryIterator($path);
			$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

			foreach($files as $file) {
				if ($file->getFilename() === '.' || $file->getFilename() === '..')
				{
					continue;
				}

				if ( $file->isDir() ){
					rmdir($file->getRealPath());
				}
				else {
					unlink($file->getRealPath());
				}
			}

			rmdir($path);
		}
	}

	function find_file( $folder , $extension ) {
		$files = scandir( $folder );

		foreach($files as $file)
		{
			if(strpos(strtolower($file), $extension )  !== false && $file[0] != '.')
			{
				return $file;
			}
		}

		return false;
	}

	/*--------------------------------------------------------------------------------------------------
		zn_extract_link - This function will return the option
		@accepts : An link option
		@returns : array containing a link start and link end HTML
	--------------------------------------------------------------------------------------------------*/
	function zn_extract_link( $link_array , $class = false , $attributes = false, $def_start = '', $def_end = '', $def_url = false ){

		if($def_url && empty($link_array['url'])){
			$link_array['url'] = trim($def_url);
		}

		if ( !is_array( $link_array ) || empty( $link_array['url'] ) ) {
			$link['start'] = $def_start ? $def_start : '';
			$link['end'] = $def_end ? $def_end : '';
		}
		else{

			$title 	= ! empty( $link_array['title'] ) ? 'title="'.$link_array['title'].'"' : '';
			$target = ! empty( $link_array['target'] ) ? zn_get_target( esc_attr( $link_array['target'] ) ) : '';
			$link 	= array( 'start' => '<a href="'.esc_url( $link_array['url'] ).'" '.$attributes.' class="'.$class.'" '.$title.' '.$target.' '.WpkPageHelper::zn_schema_markup('url').'>' , 'end' => '</a>' );
		}

		return $link;

	}

	/*--------------------------------------------------------------------------------------------------
		zn_extract_link_title - This function will return the title string from link array
		@accepts : An link option
		@returns : string
	--------------------------------------------------------------------------------------------------*/
	function zn_extract_link_title( $link_array, $esc = false ){

		return is_array( $link_array ) && !empty( $link_array['title'] ) ? ( $esc ? esc_attr( $link_array['title'] ) : $link_array['title'] )  : '';

	}

	/*--------------------------------------------------------------------------------------------------
		Minimifyes CSS code
	--------------------------------------------------------------------------------------------------*/
	function zn_minimify( $css_code ){

		// Minimiy CSS
		$css_code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css_code); // Remove comments
		$css_code = str_replace(': ', ':', $css_code); // Remove space after colons
		$css_code = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css_code); // Remove whitespace

		return $css_code;
	}



	global $zn_current_post_id;
	function zn_get_the_id() {
		global $zn_current_post_id;

		if ( isset( $zn_current_post_id ) ) {
			$id = $zn_current_post_id;
		}
		else{
			if( isset( $_POST['post_id'] ) ){
				$id = $zn_current_post_id = $_POST['post_id'];
			}
			else{
				$post = get_post();
				if(isset( $post->ID) ) {
					$id = $zn_current_post_id = get_queried_object_id();
				}
				else{
					$id = $zn_current_post_id = false;
				}

			}
		}

		$id = apply_filters('zn_get_the_id', $id);

		return $id;

	}

/*--------------------------------------------------------------------------------------------------
	Preety print
--------------------------------------------------------------------------------------------------*/
function print_z($string, $hidden = false) {
	echo '<pre '. ( $hidden ? 'style="display:none"':'' ) .'>';
		print_r($string);
	echo '</pre>';
}

/*--------------------------------------------------------------------------------------------------
	Sanitize string for widgets
--------------------------------------------------------------------------------------------------*/
function zn_sanitize_widget_id($id){
	$id = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $id );
	return str_replace(' ','_',strtolower($id) );
}

/*--------------------------------------------------------------------------------------------------
	Create dynamic css
--------------------------------------------------------------------------------------------------*/
function generate_options_css( $data = false ) {

	global $zn_framework, $saved_options;

	/* CLEAR THE FW OPTIONS CACHE */
	if( ! empty( $data ) ){
		$saved_options = $data;
	}
	else{
		$saved_options = false;
	}


	/** Define some vars **/
	$uploads = wp_upload_dir();
	$css_dir = apply_filters( 'zn_dynamic_css_location', THEME_BASE. '/css/'); // Shorten code, save 1 call

	$zn_uploads_dir = trailingslashit( $uploads['basedir'] );

	/** Capture CSS output **/
	ob_start();
	require($css_dir . 'dynamic_css.php');
	$css = ob_get_clean();

	$css = apply_filters('zn_dynamic_css',$css);
	$css = zn_minimify( $css );

	/** Write to zn_dynamic.css file **/
	file_put_contents( $zn_uploads_dir . 'zn_dynamic.css', $css );

}



/* CUSTOM WP_FOOTER FUNCTION
	Fixes problems with next gen gallery
*/
function zn_footer(){
	do_action('zn_footer');
}

/**
 * Checks if a plugin is installed. The $plugin variable should contain the plugin name and main file ( for example zn_framework/zn_framework.php )
 * @param type $plugin
 * @return bool
 */
function zn_is_plugin_installed( $plugin ){
	if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
		return true;
	} else {
		return false;
	}
}

function zn_is_plugin_active( $plugin_path = '' ) {
	/**
	 * Detect plugin. For use on Front End only.
	 * eg: zn_is_plugin_active( 'plugin-directory/plugin-file.php' );
	 */
	if(!function_exists('is_plugin_active')){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	// check for plugin using plugin name
	return is_plugin_active( $plugin_path );
}

/**
 * Verify whether or not the WooCommerce plugin is installed and active.
 * On some web hosts, like godaddy, the check for WooCommerce using is_plugin_active returns true even if the plugin
 * is not installed or active.
 */
function znfw_is_woocommerce_active(){
	return class_exists('WooCommerce');
}

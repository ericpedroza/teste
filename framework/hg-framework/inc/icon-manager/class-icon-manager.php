<?php if ( !defined( 'ABSPATH' ) ) {
	return;
}

class ZnHgFw_IconManager {
	public $paths = array();

	/**
	 * The name of the option from the database
	 */
	const OPTION_NAME = 'zn_custom_fonts';

	public $font_name = 'new_font';

	// CONTAINS ALL THE ICONS
	public static $icon_data;
	public static $icons_locations;
	public static $custom_fonts;

	public function __construct() {
		global $wp_upload_dir;
		// SET PATHS
		$this->paths = $wp_upload_dir;
		$this->paths[ 'fonts' ] = 'zn_fonts';
		$this->paths[ 'temp' ] = trailingslashit( $this->paths[ 'fonts' ] ) . 'zn_temp';
		$this->paths[ 'fontdir' ] = trailingslashit( $this->paths[ 'basedir' ] ) . $this->paths[ 'fonts' ];
		$this->paths[ 'tempdir' ] = trailingslashit( $this->paths[ 'basedir' ] ) . $this->paths[ 'temp' ];
		$this->paths[ 'fonturl' ] = trailingslashit( $this->paths[ 'baseurl' ] ) . $this->paths[ 'fonts' ];
		$this->paths[ 'tempurl' ] = trailingslashit( $this->paths[ 'baseurl' ] ) . trailingslashit( $this->paths[ 'temp' ] );

		// FIlters
		add_filter( 'zn_dynamic_css', array( $this, 'set_css' ) );
		add_filter( 'upload_mimes', array( $this, 'upload_mimes' ), 0 );

		if( is_admin() ) {
			add_action( 'wp_ajax_zn_upload_icons', array( $this, 'upload_icons' ) );
			add_action( 'wp_ajax_zn_remove_icons', array( $this, 'remove_icons' ) );
			add_action( 'admin_print_styles', array( $this, 'admin_css' ) );
			// Regenerate dynamic css
			add_action( 'znhgfw_domain_change', 'generate_options_css' );
		}
	}

	function upload_mimes( $mimes ){
		$mimes['svg'] = 'image/svg+xml';
		$mimes['ttf'] = 'font/ttf';
		$mimes['woff'] = 'application/font-woff';
		$mimes['eot'] = 'application/vnd.ms-fontobject';
		return $mimes;
	}

	function admin_css()
	{
		echo '<!-- ICON FONTS CSS -->';
		echo '<style type="text/css">';
		echo $this->set_css();
		echo '</style>';
	}


	//<editor-fold desc="::: AJAX">
	/**
	 * UPLOADS THE ICONS
	 */
	public function upload_icons() {
		// Validate request
		check_ajax_referer( 'zn_framework', 'security' );
		if ( !isset( $_POST[ 'attachment' ] ) ) {
			wp_send_json( array( 'message' => esc_html( __( 'Invalid request', '' ) ) ) );
		}
		$attachment = $_POST[ 'attachment' ];
		if ( empty( $attachment ) || !is_array( $attachment ) || !isset( $attachment[ 'id' ] ) || !isset( $attachment[ 'title' ] ) ) {
			wp_send_json( array( 'message' => esc_html( __( 'Invalid request. Missing attachment', '' ) ) ) );
		}
		$path = get_attached_file( (int)$attachment[ 'id' ] );
		$return = $this->do_icon_install( $path, $attachment[ 'title' ] );
		wp_send_json( $return );
	}

	/**
	 * DELETES THE ICONS
	 */
	public function remove_icons() {
		if ( !isset( $_POST[ 'font_name' ] ) ) {
			wp_send_json( array( 'message' => esc_html( __( 'Invalid request. Missing font name', '' ) ) ) );
		}
		$font_name = wp_strip_all_tags( $_POST[ 'font_name' ] );
		if ( empty( $font_name ) ) {
			wp_send_json( array( 'message' => esc_html( __( 'Invalid request. Missing font name', '' ) ) ) );
		}
		$dirPath = trailingslashit( $this->paths[ 'fontdir' ] ) . $font_name;
		if ( !is_dir( $dirPath ) ) {
			wp_send_json( array( 'message' => esc_html( __( 'Invalid request. Directory not found', '' ) ) ) );
		}
		zn_delete_folder( $dirPath );
		$fonts = $this->get_custom_fonts();
		if ( is_array( $fonts ) && isset( $fonts[ $font_name ] ) ) {
			unset( $fonts[ $font_name ] );
		}
		update_option( self::OPTION_NAME, $fonts );
		$return[ 'message' ] = esc_html( sprintf( __( ' The %s font was successfully deleted', '' ), $font_name ) );
		wp_send_json( $return );
	}

	//</editor-fold desc="::: AJAX">

	public function do_icon_install( $path, $title ) {
		$unzipped = $this->do_icons_archive( $path );
		if ( isset( $unzipped[ 'error' ] ) && !empty( $unzipped[ 'error' ] ) ) {
			$return[ 'message' ] = $unzipped[ 'error' ];
		}
		else {
			// ADD THE FONT INFO TO DB AND CREATE ICON_LIST
			$font_data = $this->create_data();
			if ( isset( $font_data[ 'message' ] ) && !empty( $font_data[ 'message' ] ) ) {
				$return[ 'message' ] = $font_data[ 'message' ];
			}
			else {
				$return[ 'message' ] = esc_html( sprintf( __( ' The %s font was successfully added', '' ), $this->font_name ) );
				$return[ 'html' ] = '<a class="zn_remove_font" href="#">' . $this->font_name . '<span data-font_name="' . esc_attr( $title ) . '" class="zn_remove_font_trigger">&#215;</span></a> ';
			}

		}
		generate_options_css();
		return $return;
	}

	public function create_data() {
		$svg_file = find_file( $this->paths[ 'tempdir' ], '.svg' );
		$return = array();
		if ( empty( $svg_file ) ) {
			zn_delete_folder( $this->paths[ 'tempdir' ] );
			$return[ 'message' ] = esc_html( __( 'The zip did not contained any svg files.', '' ) );
			return $return;
		}
		//#! since v4.1.4
		$svgFile = trailingslashit( $this->paths[ 'tempdir' ] ) . $svg_file;
		if ( !is_file( $svgFile ) || !is_readable( $svgFile ) ) {
			zn_delete_folder( $this->paths[ 'tempdir' ] );
			$return[ 'message' ] = esc_html( __( 'Could not find or read the svg file.', '' ) );
			return $return;
		}
		$file_data = file_get_contents( $svgFile );
		if ( !is_wp_error( $file_data ) && !empty( $file_data ) ) {
			$xml = simplexml_load_string( $file_data );
			//#! since v4.1.4 - make sure this is a valid font archive
			if ( !is_object( $xml ) || !isset( $xml->defs ) || !isset( $xml->defs->font ) ) {
				zn_delete_folder( $this->paths[ 'tempdir' ] );
				$return[ 'message' ] = esc_html( __( 'Could not find or read the svg file.', '' ) );
				return $return;
			}
			$font_attr = $xml->defs->font->attributes();
			$this->font_name = (string)$font_attr[ 'id' ];
			$icon_list = array();
			$glyphs = $xml->defs->font->children();
			$class = '';
			foreach ( $glyphs as $item => $glyph ) {
				if ( $item == 'glyph' ) {
					$attributes = $glyph->attributes();
					$unicode = (string)$attributes[ 'unicode' ];
					$d = (string)$attributes[ 'd' ];
					if ( $class != 'hidden' && !empty( $d ) ) {
						$unicode_key = trim( json_encode( $unicode ), '\\\"' );
						if ( $item == 'glyph' && !empty( $unicode_key ) && trim( $unicode_key ) != '' ) {
							$icon_list[ $this->font_name ][ $unicode_key ] = $unicode_key;
						}
					}
				}
			}
			if ( !empty( $icon_list ) && !empty( $this->font_name ) ) {
				$icon_list_file = fopen( $this->paths[ 'tempdir' ] . '/icon_list.php', 'wb' );
				if ( $icon_list_file ) {
					fwrite( $icon_list_file, '<?php $icons = array();' );
					foreach ( $icon_list[ $this->font_name ] as $unicode ) {
						if ( !empty( $unicode ) ) {
							$delimiter = "'";
							if ( strpos( $unicode, "'" ) !== false ) {
								$delimiter = '"';
							}
							fwrite( $icon_list_file, "\r\n" . '$icons[\'' . $this->font_name . '\'][' . $delimiter . $unicode . $delimiter . '] = ' . $delimiter . $unicode . $delimiter . ';' );
						}
					}
					fclose( $icon_list_file );
				}
				else {
					zn_delete_folder( $this->paths[ 'tempdir' ] );
					$return[ 'message' ] = esc_html( __( 'There was a problem creating the icon list file', '' ) );
					return $return;
				}
				// RENAME ALL FILES SO WE CAN LOAD THEM BY FONT NAME
				$this->rename_files();
				// RENAME THE FOLDER WITH THE FONT NAME
				$this->rename_folder();
				// ADD FONT DATA TO FONT OPTION
				$this->add_font_data();
			}
		}
		else {
			$return[ 'message' ] = esc_html( __( 'The svg file could not be opened.', '' ) );
		}
		return $return;
	}

	/*
	*	Retrieves all custom fonts from options table
	*/
	public static function get_custom_fonts() {
		if ( !empty( self::$custom_fonts ) ) {
			return self::$custom_fonts;
		}
		$fonts = get_option( self::OPTION_NAME );
		if ( empty( $fonts ) ) {
			$fonts = array();
		}
		// CACHE THE VALUE
		self::$custom_fonts = $fonts;
		return $fonts;

	}

	public function add_font_data() {
		$fonts = $this->get_custom_fonts();
		if ( empty( $fonts ) ) {
			$fonts = array();
		}
		$url = trailingslashit( $this->paths[ 'fonturl' ] );
		$url = preg_replace( '#^https?://#', '//', $url ); // SSL friendly URL
		$fonts[ $this->font_name ] = array( 'url' => $url, 'filepath' => trailingslashit( $this->paths[ 'fontdir' ] ), );
		update_option( self::OPTION_NAME, $fonts );
	}

	public function rename_files() {
		$directory = trailingslashit( $this->paths[ 'tempdir' ] );
		$extensions = array( 'eot', 'svg', 'ttf', 'woff' );
		foreach ( glob( $directory . '*' ) as $file ) {
			$path_parts = pathinfo( $file );
			if ( in_array( $path_parts[ 'extension' ], $extensions ) ) {
				rename( $file, trailingslashit( $path_parts[ 'dirname' ] ) . $this->font_name . '.' . $path_parts[ 'extension' ] );
			}
		}
	}

	/*
	*	RENAME THE FOLDER
	*	@param : the font name
	*/
	public function rename_folder() {
		$new_name = trailingslashit( $this->paths[ 'fontdir' ] ) . $this->font_name;
		zn_delete_folder( $new_name );
		rename( $this->paths[ 'tempdir' ], $new_name );
	}

	/*
	*	EXTRACTS AN ARCHIVE CONTAINNING ICONS
	*/
	public function do_icons_archive( $zip ) {
		$return = array();
		$extensions = array( 'eot', 'svg', 'ttf', 'woff', 'json' );
		$tempdir = zn_create_folder( $this->paths[ 'tempdir' ], false );
		$temp2Folder = $this->paths[ 'tempdir' ] . "2";
		$tempdir2 = zn_create_folder( $temp2Folder, false );
		if ( !$tempdir ) {
			$return[ 'error' ] = esc_html(__('The temp folder could not be created !', ''));
			return $return;
		}
		WP_Filesystem();
		$extracted = unzip_file( $zip, $temp2Folder );
		if ( $extracted ) {
			// We need to remove any unnecessary files and move the allowed file types one folder up
			$elements_files_obj = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $temp2Folder, RecursiveIteratorIterator::CHILD_FIRST ) );
			$elements_files_obj->setMaxDepth( 2 );
			foreach ( $elements_files_obj as $filename => $fileobject ) {
				if ( !in_array( pathinfo( $fileobject->getFilename(), PATHINFO_EXTENSION ), $extensions ) ) {
					continue;
				}
				copy( $filename, $this->paths[ 'tempdir' ] . '/' . $fileobject->getFilename() );
			}
			zn_delete_folder( $this->paths[ 'tempdir' ] . '2' );
		}
		else {
			$return[ 'error' ] = esc_html(__('The zip file could not be extracted !', ''));
		}
		return $return;
	}

	/*
	*	GET ALL THE ICONS
	*/
	public static function get_icons() {
		if ( !empty( self::$icon_data ) ) {
			return self::$icon_data;
		}
		$icon_locations = self::get_icon_locations();
		$config = array();
		$icons = array();
		foreach ( $icon_locations as $name => $icon ) {
			if ( file_exists( $icon[ 'filepath' ] . $name . '/icon_list.php' ) ) {
				include( $icon[ 'filepath' ] . $name . '/icon_list.php' );
			}
			$config = array_merge( $config, $icons );

		}
		self::$icon_data = $config;
		return $config;
	}

	/*
	*	GET ALL ICONS LOCATIONS ( DEFAULT AND CUSTOM )
	*/
	public static function get_icon_locations() {
		if ( !empty( self::$icons_locations ) ) {
			return self::$icons_locations;
		}
		// CUSTOM FONTS
		$custom_fonts = self::get_custom_fonts();
		$font_names = array();
		$default_fonts_base = THEME_BASE . '/template_helpers/icons/';
		foreach ( glob( $default_fonts_base . '*' ) as $file ) {
			$path_parts = pathinfo( $file );
			$font_names[] = $path_parts[ 'basename' ];

		}
		$url = THEME_BASE_URI . '/template_helpers/icons/';
		$url = preg_replace( '#^https?://#', '//', $url );
		$default_fonts = array();
		foreach ( $font_names as $font ) {
			$default_fonts[ $font ] = array(
				'url'      => $url, // USED TO LOAD THE FILES
				'filepath' => $default_fonts_base // USED INTERNALLY
			);
		}
		// COMBINE DEFAULT AND CUSTOM FONTS
		$icons_locations = array_merge( $default_fonts, $custom_fonts );
		self::$icons_locations = $icons_locations;
		return $icons_locations;

	}

	public function get_icon( $icon ) {
		if ( strpos( $icon, 'u' ) === 0 ) {
			$icon = json_decode( '"\\' . $icon . '"' );
		}
		return $icon;
	}

	public function set_css( $output = '' ) {
		$icons_locations = self::get_icon_locations();
		//$output .= '<style type="text/css">';
		foreach ( $icons_locations as $name => $font_data ) {
			$icon_file = $font_data[ 'url' ] . $name . '/' . $name;
			$output .= "
@font-face {font-family: '{$name}'; font-weight: normal; font-style: normal;
src: url('{$icon_file}.eot');
src: url('{$icon_file}.eot#iefix') format('embedded-opentype'),
url('{$icon_file}.woff') format('woff'),
url('{$icon_file}.ttf') format('truetype'),
url('{$icon_file}.svg#{$name}') format('svg');
}
[data-zniconfam='{$name}']:before , [data-zniconfam='{$name}']  { font-family: '{$name}' !important; }
[data-zn_icon]:before {
	content: attr(data-zn_icon)
}";
		}
		return $output;
	}

	public function generate_icon_data( $icon ) {
		$result = '';
		if ( !is_array( $icon ) ) {
			return $result;
		}
		if ( empty( $icon[ 'family' ] ) || empty( $icon[ 'unicode' ] ) ) {
			return $result;
		}
		// print_z($icon);
		return 'data-zniconfam="' . $icon[ 'family' ] . '" data-zn_icon="' . $this->get_icon( $icon[ 'unicode' ] ) . '"';
	}
}

/*
 * This should be moved elsewhere
 */
if( ! function_exists('zn_generate_icon') ) {
	function zn_generate_icon( $icon ) {
		return ZNHGFW()->getComponent('icon_manager')->generate_icon_data( $icon );
	}
}

return new ZnHgFw_IconManager();

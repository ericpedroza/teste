<?php if ( !defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class ZnHgFw_ShortcodesManager
 */
class ZnHgFw_ShortcodesManager {
	private $_registeredShortcodes = '';
	private $_includePaths         = array();
	private $_internalPath         = '';

	public function __construct() {
		$this->_internalPath = wp_normalize_path( trailingslashit( dirname( __FILE__ ) ) . 'inc' );
		array_push( $this->_includePaths, $this->_internalPath );
		$this->_includePaths = apply_filters( 'znhgfw_shortcodes_registered_paths', $this->_includePaths );
		//#! Register shortcodes
		$this->loadShortcodes();
	}

	public function loadShortcodes() {
		$scanPaths = $this->_includePaths;
		foreach ( $scanPaths as $path ) {
			$path = trailingslashit( wp_normalize_path( $path ) );
			if ( is_dir( $path ) ) {
				$files = glob( $path . '*.php' );
				if ( !empty( $files ) ) {
					foreach ( $files as $filePath ) {
						$fn = basename( $filePath, '.php' );
						if ( $this->isShortcodeRegistered( $fn ) ) {
							continue;
						}
						require_once( $filePath );
						if ( !is_callable( array( $fn, 'render' ) ) || !is_callable( array( $fn, 'getTag' ) ) ) {
							continue;
						}
						$shTag = call_user_func( array( $fn, 'getTag' ) );
						add_shortcode( $shTag, array( $fn, 'render' ) );
					}
				}
			}
		}
	}

	public function isShortcodeRegistered( $shortcodeName ) {
		return isset( $this->_registeredShortcodes[ "$shortcodeName" ] );
	}

}

return new ZnHgFw_ShortcodesManager();

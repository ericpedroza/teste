<?php

class ZnHgTFw_ThemeFramework{

	/**
	 * Holds the theme configuration
	 * @var array
	 */
	public static $instance = null;
	private $registeredComponent = array();

	/**
	 * Holds the current Theme Version
	 * @var string
	 */
	private $_version;

	/**
	 * Holds the current Theme Name
	 * @var string
	 */
	private $_theme_name;

	/**
	 * Holds the current Framework path
	 * @var string
	 */
	private $_fwPath;

	/**
	 * Holds the current Framework URL
	 * @var string
	 */
	private $_fwUrl;

	/**
	 * Holds the Theme options id
	 * @see get_option()
	 * @var string
	 */
	private $theme_db_id;

	/**
	 * Holds internal theme id
	 * @var string
	 */
	private $theme_id;


	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct(){

		// Set FW vars
		$this->initVars();
		// Register all FW components
		$this->_registerComponents();

		// Main class init
		add_action( 'init', array( $this, 'initFw' ), 1 );

	}

	/**
	 * Main Framework init
	 * @see WordPress init action
	 * @return void
	 */
	public function initFw() {
		// Load all helper functions
		$this->initHelpers();

		// Get the theme config
		$config = apply_filters('znhgtfw_config', array());

		// Setup vars
		$keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $config[ $key ] ) ) {
				$this->$key = $config[ $key ];
			}
		}

		// SAMPLE config
		// array(
		// 	'theme_db_id' => 'MYTHEMEOPTIONSID' // The option id that is saved to DB
		// )

		if( is_admin() ){
			// Load admin stuff

			// TODO: Load admin helper functions
			// Load theme Installer
			$this->_loadComponent( 'installer' );
			// TODO: REORGANIZE THE ADMIN CLASS
			$this->_loadComponent( 'admin' );
			$this->_loadComponent( 'updater' );
			$this->_loadComponent( 'dashboard' );
			// TODO: Load theme Updater
		}
		else{
			// Load frontend stuff
			// TODO: Load frontend helper functions
		}
	}


	/**
	 * Will load all helper functions
	 * @return void
	 */
	function initHelpers(){

		require ( $this->getFwPath( 'inc/helpers/functions-color-helpers.php' ) );
		require ( $this->getFwPath( 'inc/helpers/functions-image-helpers.php' ) );

		if( is_admin() ){

		}
		else{
			require ( $this->getFwPath( 'inc/helpers/functions-frontend.php' ) );
		}
	}

	function initVars(){
		// Get active theme version even if it is a child theme
		$active_theme = wp_get_theme();
		$this->_version = $active_theme->parent() ? $active_theme->parent()->get('Version') : $active_theme->get('Version');
		$this->_theme_name = $active_theme->parent() ? $active_theme->parent()->get_stylesheet() : $active_theme->get_stylesheet();

		// FW PATHS
		$this->themePath = get_template_directory();
		$this->childthemePath = get_stylesheet_directory();

		// FW URLS
		$this->themeUri = esc_url( get_template_directory_uri() );
		$this->childthemeUri = esc_url( get_stylesheet_directory_uri() );

		// FW PATHS
		$this->_fwPath = wp_normalize_path( dirname( __FILE__ ) );
		$fw_basename = str_replace( wp_normalize_path( $this->themePath ), '', $this->_fwPath );
		$this->_fwUrl = $this->themeUri . $fw_basename;
	}

	public function getVersion() {
		return $this->_version;
	}

	public function getThemeName() {
		return $this->_theme_name;
	}

	public function getFwPath( $path = '' ) {
		return trailingslashit( $this->_fwPath ) . $path;
	}

	public function getFwUrl( $path = '' ) {
		return trailingslashit( $this->_fwUrl ) . $path;
	}

	public function getThemeDbId(){
		return $this->theme_db_id;
	}

	public function getThemeId(){
		return $this->theme_id;
	}
	/**
	 * Will register all components by name
	 */
	private function _registerComponents() {
		$this->registerComponent( 'admin', $this->getFwPath( 'inc/admin/class-zn-admin.php' ) );
		$this->registerComponent( 'installer', $this->getFwPath( 'inc/installer/class-theme-install.php' ) );
		$this->registerComponent( 'updater', $this->getFwPath( 'inc/updater/class-theme-updater.php' ) );
		$this->registerComponent( 'dashboard', $this->getFwPath( 'inc/admin/class-zn-admin.php' ) );
	}

	public function registerComponent( $componentName, $path ) {
		$this->registeredComponent[ $componentName ] = $path;
	}

	private function _loadComponent( $component_name ) {
		$this->components[ $component_name ] = require_once( $this->registeredComponent[ $component_name ] );
	}

	public function getComponent( $component_name ) {
		if ( empty( $this->components[ $component_name ] ) ) {
			$this->_loadComponent( $component_name );
		}
		return $this->components[ $component_name ];
	}

}

function ZNHGTFW(){
	return ZnHgTFw_ThemeFramework::getInstance();
}

ZNHGTFW();

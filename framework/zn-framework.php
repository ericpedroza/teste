<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_upload_dir;
$wp_upload_dir = wp_upload_dir();

/**
 * Class Zn_Framework
 */
final class Zn_Framework {

	protected static $_instance = null;
	public $theme_data = array();
	public $theme_options;
	public $pagebuilder;
	public $mega_menu;

	/**
	 * Main Zn_Framework Instance
	 *
	 * Ensures only one instance of Zn_Framework is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see ZN()
	 * @return Zn_Framework - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->$key();
	}


	/**
	 * Class constructor
	 *
	 * @access public
	 */
	public function __construct() {

		// SET-UP THE FRAMEWORK BASED ON CONFIG FILE
		$config_file = apply_filters( 'zn_theme_config_file', get_template_directory().'/template_helpers/theme_config.php' );
		$theme_config = '';
		if ( file_exists( $config_file ) ) {
			include( $config_file );
			$this->theme_data = apply_filters( 'zn_theme_config', $theme_config );

			$this->define_constants();
			$this->includes();
			$this->add_actions();
		}

		add_action( 'init', array( $this, 'rewrite_rules' ) );

	}

	/**
	 * Define ZN Constants
	 */
	private function define_constants() {

		define( 'FW_PATH', dirname( __FILE__ ) );
		// TODO : BETTER WRITE THIS
		define( 'FW_URL', esc_url( get_template_directory_uri() . '/framework' ) );
	}


	/**
	 * What type of request is this?
	 * @var string $type ajax, frontend or admin
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		return false;
	}

	public function rewrite_rules(){
		if( get_option( 'znhg_flush_rewrite_rules' ) ){
			// Clear any unwanted data and flush rules
			delete_transient( 'woocommerce_cache_excluded_uris' );
			if( function_exists( 'WC' ) ){
				WC()->query->init_query_vars();
				WC()->query->add_endpoints();
			}
			flush_rewrite_rules();
			delete_option( 'znhg_flush_rewrite_rules' );
		}
	}

	private function includes() {
		include( FW_PATH .'/classes/functions-helper.php' );

		// Load the new shortcodes manager
		include( FW_PATH .'/modules/shortcodes_manager/class-shortcodes-manager.php' );

		if ( $this->is_request( 'admin' ) ) {

			include( FW_PATH .'/classes/functions-backend.php' );


		}

		if ( $this->is_request( 'ajax' ) ) {
			include( FW_PATH .'/classes/theme_ajax.php' );
		}

	}

	private function add_actions() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'wp_enqueue_scripts', array( &$this, 'init_scripts' ) );
	}


	public function init() {

		if ( $this->theme_data['supports']['pagebuilder'] ) {
			$this->init_pagebuilder();
		}

		if ( $this->theme_data['supports']['megamenu'] ) {
			$this->init_megamenu();
		}

		do_action( 'zn_framework_init' );
	}

	function init_scripts() {
		wp_register_script( 'isotope', FW_URL .'/assets/js/jquery.isotope.min.js','jquery','',true );
	}

	function init_pagebuilder() {

		// Don't load the internal PB if the PB plugin is active
		if( class_exists( 'ZnBuilder' ) ){
			return;
		}

		include( FW_PATH .'/pagebuilder/class-page-builder.php' );
		$this->pagebuilder = new ZnPageBuilder();
	}


	function init_megamenu() {

		// MEGA MENU CLASS
		include( FW_PATH .'/modules/mega-menu/class-mega-menu.php' );

		// INIT THE MegaMenu CLASS
		$this->mega_menu = new ZnMegaMenu();

	}

	function is_debug(){
		return defined( 'ZN_FW_DEBUG' ) && ZN_FW_DEBUG == true;
	}

	/**
	 * Retrieve all pages as ID => Title to display in the theme options for user tos elect their custom Coming Soon
	 * page
	 * @since v4.1.4
	 * @return array
	 */
	public function get_pages()
	{
		$result = array(
			'__zn_default__' => __('Use default', 'zn_framework')
		);

		$pages = get_pages();
		if(empty($pages)){
			return $result;
		}

		foreach($pages as $page){
			$result[$page->ID] = $page->post_title;
		}
		return $result;
	}

	/**
	 * Check to see whether or not the current user is the network/website administrator
	 * @since v4.1.5
	 * @return bool
	 */
	public static function isManagingAdmin(){
		return (is_user_logged_in() && (current_user_can('manage_network') || current_user_can('manage_options')));
	}
}


/**
 * Returns the main instance of ZnFramework to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Zn_Framework
 */
function ZN() {
	return Zn_Framework::instance();
}
/**
 * Returns the main instance of Pagebuilder
 *
 * @since  1.0.0
 * @return Zn_Framework
 */
function ZNPB() {
	return Zn_Framework::instance()->pagebuilder;
}


// Global for backwards compatibility.
$GLOBALS['zn_framework'] = ZN();

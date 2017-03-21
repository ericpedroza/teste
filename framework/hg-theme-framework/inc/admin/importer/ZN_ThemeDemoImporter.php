<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ZN_ThemeDemoImporter
 */
class ZN_ThemeDemoImporter
{
	const NONCE_ACTION = 'ZN_DEMO_IMPORT';

	const DEMO_TEMP_FOLDER = 'znDummyTemp';

	// Whether or not a demo is currently installing
	const DEMO_INSTALLING_TRANSIENT = 'zn_demo_installing';

	// Installation steps

	// [INTERNAL] SPECIAL STEPS
	const STEP_DO_GET_DEMO        = 'zn_get_demo';
	const STEP_DO_GLOBAL_SETTINGS = 'zn_global_settings';
	const STEP_DO_CUSTOM_ICONS    = 'zn_custom_icons';
	const STEP_DO_CUSTOM_FONTS    = 'zn_custom_fonts';
	const STEP_DO_IMPORT_IMAGES   = 'zn_import_images';
	const STEP_DO_IMPORT_MENUS    = 'zn_import_menus';
	const STEP_DO_CLEANUP         = 'zn_import_cleanup';
	const STEP_DO_REV_SLIDERS     = 'zn_import_rev_sliders';
	const STEP_DO_POST_PROCESSING = 'zn_import_post_processing';

	//@see: importer/inc/importer-tmpl.php
	const STEP_INSTALL_PLUGINS = 'zn_install_plugins';
	const STEP_INSTALL_THEME_OPTIONS = 'zn_install_theme_options';
	const STEP_INSTALL_WIDGETS = 'zn_install_widgets';
	const STEP_INSTALL_CONTENT = 'zn_install_content';

	// Caches the uploads Dir URL
	static $uploadsDirUrl = '';

	/**
	 * User's required capability to view or interact with this page
	 */
	const USER_CAP = 'manage_options';


	public function __construct(){
		// delete_transient( self::DEMO_INSTALLING_TRANSIENT );
		// Add ajax actions
		add_action( 'wp_ajax_install_demo', array( $this, 'ajaxInstallDemo') );

		// Load resources
		add_action( 'admin_enqueue_scripts', array($this, 'loadScripts') );
	}

	/**
	 * Retrieve the path to the demos directory
	 * @return string
	 */
	public static function getDemosDirPath(){
//		return THEME_BASE . '/template_helpers/dummy_content';
		return self::getTempFolder();
	}


	public function loadScripts( $hook )
	{
		if( false !== ($pos = strpos($hook, 'zn-about')))
		{
			// Include the old system
			wp_enqueue_style( 'zn_about_style', ZNHGTFW()->getFwUrl( '/inc/admin/assets/css/zn_about.css' ), array(), ZN()->version );

			// Include the new system
			wp_enqueue_style( 'zn-demo-import-styles', ZNHGTFW()->getFwUrl( '/inc/admin/importer/assets/zn-demo-import.css' ) );
			wp_enqueue_script('jquery');
			wp_register_script( 'znde-manager', ZNHGTFW()->getFwUrl( '/inc/admin/importer/assets/znde-manager.js' ),array('jquery') );
			wp_localize_script( 'znde-manager', 'ZN_THEME_DEMO', array(
				'nonce' => wp_create_nonce( self::NONCE_ACTION ),
				'status_waiting' => __('Waiting', 'zn_framework'),
				'status_in_progress' => __('In progress', 'zn_framework'),
				'status_completed' => __('Done', 'zn_framework'),
				'status_failed' => __('Failed', 'zn_framework'),
				'status_none' => __('No', 'zn_framework'),
				'status_error' => __('Error', 'zn_framework'),

				// Messages
				'msg_select_option' => __('Please select at least one option.', 'zn_framework'),
				'msg_invalid_markup' => __('Invalid markup. Please contact the theme developers.', 'zn_framework'),
				'msg_get_demo' => __('Retrieving demo:', 'zn_framework'),
				'msg_install_plugins' => __('Install plugins:', 'zn_framework'),
				'msg_install_theme_options' => __('Install theme options:', 'zn_framework'),
				'msg_install_widgets' => __('Install widgets:', 'zn_framework'),
				'msg_install_content' => __('Install demo content:', 'zn_framework'),
				'msg_install_global_opt' => __('Install global options:', 'zn_framework'),
				'msg_install_custom_icons' => __('Install custom icons:', 'zn_framework'),
				'msg_install_custom_fonts' => __('Install custom fonts:', 'zn_framework'),
				'msg_import_images' => __('Import images:', 'zn_framework'),
				'msg_import_menus' => __('Install menus:', 'zn_framework'),
				'msg_import_rev_sliders' => __('Install Revolution Sliders:', 'zn_framework'),
				'msg_import_post_processing' => __('Post processing:', 'zn_framework'),
				'msg_import_cleanup' => __('Cleanup:', 'zn_framework'),
				'msg_install_complete' => __('Success! Install complete.', 'zn_framework'),
				'msg_install_failed_retries' => __('Maximum failed retries reached. Unfortunately, your server cannot install this demo.', 'zn_framework'),
				'msg_install_abort' => __('Installation failed.', 'zn_framework'),
				'msg_install_failed_invalid_response' => __('Invalid response from server.', 'zn_framework'),
				'msg_install_configure' => __('Please configure your installation and try again.', 'zn_framework'),

				// Modal window
				'modal_title' => __('Demo Install', 'zn_framework'),
				'modal_close' => __('Close', 'zn_framework'),

				// Install steps
				'state_none' => ZN_DemoImportHelper::STATE_NONE,
				'state_wait' => ZN_DemoImportHelper::STATE_WAIT,
				'state_done' => ZN_DemoImportHelper::STATE_DONE,
				'state_fail' => ZN_DemoImportHelper::STATE_FAIL,
				'state_abort' => ZN_DemoImportHelper::STATE_ABORT,
				'state_complete' => ZN_DemoImportHelper::STATE_COMPLETE,
				'state_unknown' => ZN_DemoImportHelper::STATE_UNKNOWN,
			));
			wp_enqueue_script( 'znde-manager' );

			wp_enqueue_script('znde-init', ZNHGTFW()->getFwUrl( '/inc/admin/importer/assets/znde-init.js' ),array('znde-manager'));
		}
	}

	/**
	 * Mark the demo as installing
	 */
	private static function __setDemoInstalling(){

		//#! Disable cache if this is GoDaddy hosting
		if( ZN_HogashDashboard::isGoDaddy() ){
			wp_using_ext_object_cache( false );
		}

		set_transient( self::DEMO_INSTALLING_TRANSIENT, true, 5*60);
	}

	/**
	 * Check to see whether or not there is a demo installing
	 * @return bool
	 */
	public static function isDemoInstalling(){

		//#! Disable cache if this is GoDaddy hosting
		if( ZN_HogashDashboard::isGoDaddy() ){
			wp_using_ext_object_cache( false );
		}

		$data = get_transient( self::DEMO_INSTALLING_TRANSIENT );
		return (!empty($data));
	}

	private static function __preValidateRequest()
	{
		if( ! defined('DOING_AJAX')){
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_ABORT,
				'msg' => __('Fatal Error: DOING_AJAX not defined', 'zn_framework')
			));
		}
		if( ! DOING_AJAX){
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_ABORT,
				'msg' => __('Fatal Error: Not doing ajax', 'zn_framework')
			));
		}

		if( 'POST' != strtoupper($_SERVER['REQUEST_METHOD'])){
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_ABORT,
				'msg' => __('Fatal Error: Invalid request method.', 'zn_framework')
			));
		}

		if( ! self::__validateUser()){
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_ABORT,
				'msg' => __('Fatal Error: You are not allowed to perform this action.', 'zn_framework')
			));
		}

		if(! wp_verify_nonce($_POST['nonce'], self::NONCE_ACTION)){
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_ABORT,
				'msg' => __('Fatal Error: Nonce is not valid', 'zn_framework')
			));
		}
	}

	public static function canAccessDir( $dirPath )
	{
		return ( is_dir($dirPath) && is_readable($dirPath) );
	}

	/**
	 * Recursive value search/replace in array
	 *
	 * @param array $array The target array where to search in
	 * @param mixed $find The target value to search for
	 * @param mixed $replace The value to replace $find with
	 *
	 * @return array
	 */
	public static function recursiveArrayReplace( &$array, $find, $replace){
		if(is_array($array)){
			foreach($array as $key => &$value) {
				if(empty($value)){
					continue;
				}
				$serialized = is_serialized( $value );
				if($serialized){
					$value = unserialize( $value );
					if(is_array($value)){
						$value = self::recursiveArrayReplace($value, $find, $replace);
					}
					$value = serialize($value);
				}
				elseif( is_array($value) ){
					$value = self::recursiveArrayReplace($value, $find, $replace);
				}
				else {
					$value = str_replace($find, $replace, $value);
				}
			}
		}
		return $array;
	}

	/**
	 * Retrieve the path to the demo directory
	 * @param string $demoName
	 * @return int|string
	 */
	public static function getDemoDirPath($demoName = ''){
		if(empty($demoName)) {
			if (!isset($_POST['demo_name']) || empty($_POST['demo_name'])) {
				return 0;
			}
			$demoName = esc_attr( wp_strip_all_tags($_POST['demo_name']) );
		}
		return self::getTempFolder() . '/' . $demoName;
	}

	public static function getTempFolder(){
		$demoTmpPath = self::getUploadsDirPath() . self::DEMO_TEMP_FOLDER;
		return $demoTmpPath;
	}

	/**
	 * Utility method to use for the provided installing steps
	 */
	public function ajaxInstallDemo()
	{
		self::__preValidateRequest();

		// Try to increase the time limit
		@set_time_limit( 300 );

		// The installation step field is required
		if(! isset($_POST['step']) || empty($_POST['step'])){
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_ABORT,
				'msg' => __('Invalid request. The installation step is missing', 'zn_framework')
			));
		}
		// The demo_name field is required
		if(! isset($_POST['demo_name']) || empty($_POST['demo_name'])){
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_ABORT,
				'msg' => __('Invalid request. "demo_name" not found in post data.', 'zn_framework')
			));
		}

		//#! Disable cache if this is GoDaddy hosting
		if( ZN_HogashDashboard::isGoDaddy() ){
			wp_using_ext_object_cache( false );
		}

		$demoName = ZN_DemoImportHelper::sanitizeString( $_POST['demo_name'] );
		$step = ZN_DemoImportHelper::sanitizeString( $_POST['step'] );
		$demoDirPath = self::getDemoDirPath($demoName);

		// Get the demo
		if( self::STEP_DO_GET_DEMO == $step )
		{
			self::__setDemoInstalling();

			// The installation step field is required
			if(! ZN_HogashDashboard::isConnected(false)){
				delete_site_transient( ZN_HogashDashboard::THEME_DEMOS_TRANSIENT );
				delete_site_transient( ZN_HogashDashboard::THEME_CHECK_TRANSIENT );
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_ABORT,
					'msg' => __('Please verify your Hogash API Key.', 'zn_framework')
				));
			}

			// Demo already retrieved and extracted
			if(self::canAccessDir($demoDirPath))
			{
				//#! Check if there is any content
				$cfgFile = trailingslashit($demoDirPath).ZN_DemoImportHelper::DEMO_CONFIG_FILE;
				if( ! is_file($cfgFile) || ! is_readable($cfgFile)){
					self::__respond( array(
						'state' => ZN_DemoImportHelper::STATE_ABORT,
						'msg' => __('An error occurred while retrieving the demo. Please try again in a few minutes.', 'zn_framework')
					));
				}

				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			// else, get the demo
			else {
				$demoDirPath = self::__getDemo( $demoName );
				if(empty($demoDirPath) || ! is_dir( $demoDirPath)){
					self::__respond( array(
						'state' => ZN_DemoImportHelper::STATE_ABORT,
						'msg' => __('Fatal Error: Could not retrieve the demo, aborting.', 'zn_framework')
					));
				}

				//#! Check if there is any content
				$cfgFile = trailingslashit($demoDirPath).ZN_DemoImportHelper::DEMO_CONFIG_FILE;
				if( ! is_file($cfgFile) || ! is_readable($cfgFile)){
					self::__respond( array(
						'state' => ZN_DemoImportHelper::STATE_ABORT,
						'msg' => __('An error occurred while retrieving the demo. Please try again in a few minutes.', 'zn_framework')
					));
				}

				// so far so good
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
		}

		// IMPORT IMAGES
		elseif( self::STEP_DO_IMPORT_IMAGES == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::importDemoImages( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// INSTALL GLOBAL SETTINGS (wp global options, etc...)
		elseif( self::STEP_DO_GLOBAL_SETTINGS == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::importGlobalOptions( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// INSTALL PLUGINS
		elseif( self::STEP_INSTALL_PLUGINS == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::installDependentPlugins( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// INSTALL REVOLUTION SLIDERS
		elseif( self::STEP_DO_REV_SLIDERS == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::installRevolutionSliders( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// INSTALL THEME OPTIONS
		elseif( self::STEP_INSTALL_THEME_OPTIONS == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::importThemeOptions( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// INSTALL WIDGETS
		elseif( self::STEP_INSTALL_WIDGETS == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::importWidgets( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// INSTALL CUSTOM ICONS
		elseif( self::STEP_DO_CUSTOM_ICONS == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::importCustomIcons( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// INSTALL CUSTOM FONTS
		elseif( self::STEP_DO_CUSTOM_FONTS == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::importCustomFonts( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// INSTALL CONTENT
		elseif( self::STEP_INSTALL_CONTENT == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::importContent( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// INSTALL MENUS
		elseif( self::STEP_DO_IMPORT_MENUS == $step )
		{
			self::__setDemoInstalling();

			if(ZN_DemoImportHelper::importMenus( $demoDirPath )){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// POST-PROCESSING
		elseif( self::STEP_DO_POST_PROCESSING == $step )
		{
			self::__setDemoInstalling();

			if( ZN_DemoImportHelper::postProcessing( $demoDirPath ) ){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// CLEANUP
		elseif( self::STEP_DO_CLEANUP == $step )
		{
			self::__setDemoInstalling();

			if( ZN_DemoImportHelper::__cleanup() ){
				self::__respond( array(
					'state' => ZN_DemoImportHelper::STATE_DONE,
					'msg' => sprintf(__('Installation step "%s" completed.', 'zn_framework'), $step)
				));
			}
			self::__respond( array(
				'state' => ZN_DemoImportHelper::STATE_FAIL,
				'msg' => sprintf(__('Installation step "%s" failed to complete.', 'zn_framework'), $step)
			));
		}

		// Not good
		delete_transient( self::DEMO_INSTALLING_TRANSIENT );
		self::__respond( array(
			'state' => ZN_DemoImportHelper::STATE_UNKNOWN,
			'msg' => __('Fatal Error: Invalid installation state.', 'zn_framework')
		));
	}

//<editor-fold desc=">>> PRIVATE METHODS">

	/**
	 * TODO: IMPLEMENT VALIDATION AND RESPONSE, SANITIZE DIR PATH, ETC
	 *
	 * Retrieve the demo from our server
	 *
	 * @requires valid user
	 * @param string $demoName
	 * @return bool|string
	 */
	private static function __getDemo( $demoName )
	{
		$result = '';

		$tmpDirPath = self::getDemosDirPath();
		if(! is_dir($tmpDirPath)){
			wp_mkdir_p($tmpDirPath);
		}

		// Set save path
		$demoArchivePath = trailingslashit($tmpDirPath) . $demoName.'.zip';

		//#!
		$savePath = ZN_HogashDashboard::getDemo( $demoName, $demoArchivePath );
		if(false === $savePath){
			return $result;
		}
		elseif( is_file($savePath) )
		{
			$saveDirPath = self::getTempFolder().'/'.$demoName.'/';

			if( ! is_dir($saveDirPath)){
				wp_mkdir_p( $saveDirPath );
			}

			WP_Filesystem();
			unzip_file( $savePath ,$saveDirPath );

			return $saveDirPath;
		}
		return $result;
	}

	/**
	 * Respond to ajax requests
	 * @param array $data The data to send as a response
	 */
	private static function __respond( $data = array( 'state' => '', 'msg' => '') ){
		delete_transient( ZN_ThemeDemoImporter::DEMO_INSTALLING_TRANSIENT );
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo '<div class="zn-importer-js-response">'.wp_json_encode( $data ).'</div>';
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ){ wp_die(); }
		exit;
	}

	public static function getUploadsDirPath()
	{
		$wp_uploadsDir = wp_upload_dir();
		$dirPath = '';
		if(is_array($wp_uploadsDir))
		{
			if(isset($wp_uploadsDir['basedir'])) {
				return trailingslashit( $wp_uploadsDir['basedir'] );
			}
		}
		return $dirPath;
	}
	public static function getUploadsDirUrl()
	{

		if( empty( self::$uploadsDirUrl ) ){
			$wp_uploadsDir = wp_upload_dir();
			if(is_array($wp_uploadsDir))
			{
				if(isset($wp_uploadsDir['baseurl'])) {
					self::$uploadsDirUrl = trailingslashit($wp_uploadsDir['baseurl']);
				}
			}
		}
		return self::$uploadsDirUrl;
	}
//</editor-fold desc=">>> PRIVATE METHODS">

//<editor-fold desc="::: INTERNALS">
	/**
	 * Check to see whether or not the current user is logged in and has the "manage_options" capability (administrator)
	 * @return bool
	 */
	 private static function __validateUser(){
		return (is_user_logged_in() && current_user_can(self::USER_CAP));
	}
//</editor-fold desc="::: INTERNALS">

}
new ZN_ThemeDemoImporter();

<?php if ( !defined( 'ABSPATH' ) )
{
	return;
}

/**
 * This class handles the theme update functionality
 */
class ZN_ThemeUpdater
{

	public static function init()
	{
		add_action( 'init', array( get_class(), 'pre_check_update' ) );
	}

	public static function pre_check_update()
	{
		$apiKey = ZN_HogashDashboard::getApiKey();
		if ( empty( $apiKey ) || ! ZN_HogashDashboard::isConnected() )
		{
			return;
		}

		add_filter( "pre_set_site_transient_update_themes", array( get_class(), "check" ), 800 );
	}

	public static function check( $updatesAvailable )
	{

		$apiKey = ZN_HogashDashboard::getApiKey();
		if ( empty( $apiKey ) || !ZN_HogashDashboard::isConnected() )
		{
			return $updatesAvailable;
		}

		//#! Get the theme info from Dashboard
		$dashThemeInfo = ZN_HogashDashboard::getThemeInfo();

		if ( empty( $dashThemeInfo ) || !isset( $dashThemeInfo[ 'url' ] ) )
		{
			return $updatesAvailable;
		}
		if( !isset( $dashThemeInfo[ 'new_version' ] )|| empty($dashThemeInfo[ 'new_version' ])){
			return $updatesAvailable;
		}

		if( !isset( $dashThemeInfo[ 'package' ] ) || empty($dashThemeInfo[ 'package' ])){
			return $updatesAvailable;
		}

		//#! Check if the theme needs an update
		if ( !version_compare( ZNHGTFW()->getVersion(), $dashThemeInfo[ 'new_version' ], '<' ) )
		{
			return $updatesAvailable;
		}

		//#! Update and return the list
		$updatesAvailable->response[ZNHGTFW()->getThemeName()] = $dashThemeInfo;
		return $updatesAvailable;
	}

}

return ZN_ThemeUpdater::init();

<?php if(! defined('ABSPATH')){ return; }

if( ZN_HogashDashboard::isWPMU() )
{
	$isConnected = ZN_HogashDashboard::isConnected();
	$isNetworkPage = (isset($_REQUEST['page']) && $_REQUEST['page'] == ZN_HogashDashboard::NETWORK_MENU_SLUG);

	if( $isNetworkPage )
	{
		include( ZNHGTFW()->getFwPath('/inc/admin/tmpl/form-register-theme-tmpl.php'));
	}
	else {
		if( $isConnected){
			?>
			<div class="inline notice notice-error">
				<p>
					<?php
					echo sprintf(
						__('The theme has already been registered and connected with the Hogash Dashboard. To change the API Key, please head over to the <a href="%s" target="_blank">Multisite Network Dashboard</a>.', 'zn_framework'),
						network_admin_url('admin.php?page=kdash_')
					);
					?>
				</p>
			</div>
			<?php
		}
		else {
			?>
			<div class="inline notice notice-error">
				<p><?php

					echo sprintf(
						__('Please register the theme through the <a href="%s" target="_blank">Multisite Network Dashboard</a>, or contact the network administrator and ask them to register the theme with the Hogash Dashboard.', 'zn_framework'),
						network_admin_url('admin.php?page=kdash_')
					);
					?>
				</p>
			</div>
			<?php
		}
	}
}
else {
	include( ZNHGTFW()->getFwPath( '/inc/admin/tmpl/form-register-theme-tmpl.php' ));
}

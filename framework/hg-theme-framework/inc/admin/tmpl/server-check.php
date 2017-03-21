<?php
/**
 * This file displays the server check button
 */

// delete_transient( 'zn_server_connection_check' );
$message = '';
$icon = 'dashicons-warning';
$connection_status = get_transient( 'zn_server_connection_check' );
$hgDomain = str_replace( 'http://', '', ZN()->theme_data[ 'server_url' ] );
$btn_class = '';
if ( 'ok' == $connection_status )
{
	$btn_class = 'zn-action--gray';
	$icon = 'dashicons-yes';
}
elseif ( 'notok' == $connection_status )
{
	$icon = 'dashicons-no';
	$message = '<br />' . esc_html( __( 'It seems that your server cannot connect to Hogash Servers. Some features like demo data import will not work. In order to resolve this, please contact your server administrator and ask them to allow connections to the theme servers.', 'zn_framework' ) );
}
?>
<div class="zn-server-status-column zn-server-status-column-name"><?php echo esc_html( __( 'Connection to server', 'zn_framework' ) ); ?></div>
<div class="zn-server-status-column">
	<span
		class="zn-server-status-column-icon dashicons-before dashicons-update js-zn-server-status-icon <?php echo $icon; ?>"
		title="<?php echo esc_html( sprintf( __( 'If a connection can be established between your current server and %s server', 'zn_framework' ), $hgDomain ) ); ?>"></span>
</div>
<div class="zn-server-status-column zn-server-status-column-value">
	<a class="zn-server-status-button-custom zn-action-input-custom zn-about-action <?php echo $btn_class; ?>" href="#"
	   title="<?php echo esc_html( sprintf( __( 'Verify the connection to our domain %s to see if you will be able to install our demos or plugins', 'zn_framework' ), $hgDomain ) ); ?>"><?php echo esc_html( __( 'Check now', 'zn_framework' ) ); ?></a> <?php echo $message; ?>
</div>


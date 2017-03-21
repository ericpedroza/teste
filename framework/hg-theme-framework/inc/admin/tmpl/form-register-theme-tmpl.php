<?php if ( !defined( 'ABSPATH' ) )
{
	return;
}

//#! Default GET request
$how = !( 'GET' == strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) );
$dashIsConnected = ZN_HogashDashboard::isConnected( $how );
$dashIsPostAction = ( isset( $GLOBALS[ 'dashRegisterPostAction' ] ) && !empty( $GLOBALS[ 'dashRegisterPostAction' ] ) && ( 'POST' == strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) );
if ( $dashIsPostAction )
{
	if ( isset( $GLOBALS[ 'dashRegisterPostAction' ][ 'success' ] ) )
	{
		$dashIsConnected = (bool)$GLOBALS[ 'dashRegisterPostAction' ][ 'success' ];
	}
}
?>
<div class="zn-registerContainer">

	<div class="znfb-row">

		<div class="znfb-col-12">
			<h3 class="zn-lead-title">
				<strong><?php echo esc_html( __( 'Register Kallyas', 'zn_framework' ) ); ?></strong></h3>
		</div>

		<div class="znfb-col-7">

			<?php if ( !$dashIsConnected )
			{ ?>
				<div class="zn-lead-text">
					<div class="zn-adminNotice zn-adminNotice-info">
						<p>
							<strong><?php echo esc_html( __( 'To enjoy the full experience we strongly recommend to register Kallyas.', 'zn_framework' ) ); ?></strong>
						</p>

						<p><?php echo sprintf( __( 'By connecting your theme with <a href="%s" target="%s">Hogash Dashboard</a>, you will get Kallyas theme updates, sample data demo packs and notifications about cool new features.', 'zn_framework' ), '//my.hogash.com/', esc_attr( '_blank' ) ); ?></p>
					</div>
					<h3><strong><?php echo esc_html( __( 'Please follow these steps:', 'zn_framework' ) ); ?></strong>
					</h3>
					<ul class="zn-dashRegister-steps">
						<li><?php echo sprintf( __( '1) <a href="%s" target="%s">Register to Hogash Customer Dashboard</a> with your Envato Account.', 'zn_framework' ), '//my.hogash.com/', esc_attr( '_blank' ) ); ?></li>
						<li><?php echo sprintf( __( '2) Access "<a href="%s" target="">My Products</a>" section of the dashboard and make sure you have at least one purchase of Kallyas theme.', 'zn_framework' ), '//my.hogash.com/register-products/', '_blank' ); ?></li>
						<li><?php echo esc_html( __( '3) Click on the Generate Key button and than copy the Key.', 'zn_framework' ) ); ?></li>
						<li><?php _e( '4) Insert/paste the generated API Key you just copied, into the right side "HOGASH API KEY" form. Click the <strong>Connect</strong> button.', 'zn_framework' ); ?></li>
					</ul>
				</div>
				<?php
			}

			else
			{
				?>
				<div class="zn-lead-text">
					<div class="zn-adminNotice zn-adminNotice-info">
						<p><strong><?php echo esc_html( __( 'You have successfully activated your copy of Kallyas theme. ', 'zn_framework' ) ); ?></strong></p>

						<p><?php echo sprintf( __( 'If you plan on migrating / changing the domain of this website, please regenerate the API key into the <a href="%s" target="%s">Hogash Dashboard</a>.', 'zn_framework' ), '//my.hogash.com/', '_blank' ); ?></p>
					</div>
				</div>
			<?php } ?>

		</div>

		<div class="znfb-col-5">
			<?php
			if ( $dashIsPostAction && !empty( $GLOBALS[ 'dashRegisterPostAction' ][ 'data' ] ) )
			{
				$cssClass = ( $GLOBALS[ 'dashRegisterPostAction' ][ 'success' ] ? 'success' : 'error' );
				?>
				<div class="zn-adminNotice zn-adminNotice-<?php echo $cssClass; ?>">
					<p><?php echo $GLOBALS[ 'dashRegisterPostAction' ][ 'data' ]; ?></p></div>
				<?php
			}
			?>
			<?php
			if ( !$dashIsConnected )
			{
				do_action( 'dash_clear_cached_data' );
			}
			$dash_api_key = ZN_HogashDashboard::getApiKey();
			?>
			<form action="" class="zn-about-register-form zn-dashRegister-form" method="post">

				<div class="zn-dashRegister-status">
					<?php echo esc_html( __( 'Status:', 'zn_framework' ) );?>
					<?php
					if ( !$dashIsConnected )
					{
						echo '<strong class="zn-dashRegister-statusName">' . esc_html( __( 'NOT CONNECTED', 'zn_framework' ) ) . '</strong>';
					}
					else
					{
						echo '<strong class="zn-dashRegister-statusName is-connected">' . esc_html( __( 'CONNECTED', 'zn_framework' ) ) . '</strong>';
					}
					?>
				</div>

				<!--// Displays the ajax result on single installations -->
				<div id="zn-register-theme-alert"></div>


				<div class="zn-about-form-field zn-dashRegister-formMain">
					<label for="hg_api_key"><?php echo esc_html( __( 'Hogash API key', 'zn_framework' ) ); ?></label>

					<input type="text" id="hg_api_key" name="dash_api_key" class="zn-about-register-form-api"
						   value="<?php echo $dash_api_key; ?>"
						   placeholder="<?php echo esc_html( __( 'XXXXX-XXXXX-XXXXX-XXXXX-XXXXX', 'zn_framework' ) ); ?>">
				</div>

				<?php wp_nonce_field( 'zn_theme_registration', 'zn_nonce' ); ?>
				<input type="submit"
					   class="zn-about-register-form-submit zn-dashRegister-formSubmit zn-about-action zn-action-green zn-action-md"
					   value="<?php echo esc_html( __( 'Connect', 'zn_framework' ) ); ?>">
			</form>


			<div class="zn-server-status-row">
				<?php
				include( ZNHGTFW()->getFwPath( '/inc/admin/tmpl/server-check.php' ) );
				// delete_transient( 'zn_server_connection_check' );
				$url = admin_url( 'admin.php?page=zn-about&check_connection=true' );
				$icon = 'dashicons-warning';
				$connection_status = get_transient( 'zn_server_connection_check' );
				$message = '';

				if ( 'ok' === $connection_status )
				{
					$icon = 'dashicons-yes';
				}
				elseif ( 'notok' === $connection_status )
				{
					$icon = 'dashicons-no';
					$message = '<br />' . esc_html( __( 'It seems that your server cannot connect to Hogash.com Servers. Some features like demo data import will not work. In order to resolve this, please contact your server administrator and ask them to allow (whitelist) connections to Hogash.com server.', 'zn_framework' ) );
				}
				?>
			</div>
		</div>

		<div class="znfb-col-12">
			<hr class="zn-dashRegister-sep">
		</div>

		<!-- <div class="znfb-col-3">
			<a href="http://support.hogash.com/documentation/how-to-register-kallyas-theme/" target="_blank"><img src="<?php echo THEME_BASE_URI ?>/framework/admin/assets/images/register-video-thumb.jpg" alt="" class="zn-dashRegister-videoThumb"></a>
		</div> -->

		<div class="znfb-col-12">
			<div class="zn-dashRegister-infoList">

				<h4 class="zn-dashRegister-tutorial"><?php echo sprintf( __( 'Having problems? <a href="%s" target="%s">Read the tutorial</a>', 'zn_framework' ), '//support.hogash.com/documentation/how-to-register-kallyas-theme/', '_blank' ); ?></h4>

				<h3><?php echo esc_html( __( 'Troubleshooting', 'zn_framework' ) ); ?></h3>
				<ul>
					<li><?php echo sprintf( __( '<a href="%s" target="_blank">Why is my API key inactive?</a>', 'zn_framework' ), '//support.hogash.com/documentation/how-to-register-kallyas-theme/#why_not_active', '_blank' ); ?></li>
					<li><?php echo sprintf( __( '<a href="%s" target="_blank">ERROR: cURL error 28: connect() timed out!</a>', 'zn_framework' ), '//support.hogash.com/documentation/how-to-register-kallyas-theme/#error-curl-error-28-connect-timed-out' ); ?></li>
					<li><?php echo sprintf( __( '<a href="%s" target="_blank">Kallyas Dashboard is taking a lot of time to load</a>', 'zn_framework' ), '//support.hogash.com/documentation/how-to-register-kallyas-theme/#3-kallyas-theme-dashboard-is-taking-a-lot-of-time-to-load' ); ?></li>
				</ul>

				<h3><?php echo esc_html( __( 'Frequently Asked Questions', 'zn_framework' ) ); ?></h3>
				<ul>
					<li><?php echo sprintf( __( '<a href="%s" target="%s">What are the benefits of registration?</a>', 'zn_framework' ), '//support.hogash.com/documentation/how-to-register-kallyas-theme/#registration_benefits', '_blank' ); ?></li>
					<li><?php echo sprintf( __( '<a href="%s" target="%s">Why do I need to register my theme?</a>', 'zn_framework' ), '//support.hogash.com/documentation/how-to-register-kallyas-theme/#what_is_needed', '_blank' ); ?></li>
					<li><?php echo sprintf( __( '<a href="%s" target="_blank">How can I verify my API Key?</a>', 'zn_framework' ), '//support.hogash.com/documentation/how-to-register-kallyas-theme/#how_to_verify_api_key', '_blank' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
</div>

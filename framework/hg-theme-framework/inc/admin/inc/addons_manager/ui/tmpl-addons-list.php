<?php if ( !defined( 'ABSPATH' ) )
{
	return;
}

if ( !ZN_HogashDashboard::isConnected() )
{
	echo '<p>' . esc_html( __( 'Plugins not available', 'zn_framework' ) ) . '</p>';
	return;
}

$plugins = ZN_HogashDashboard::getAllPlugins();
if ( empty( $plugins ) )
{
	echo '<p>' . esc_html( __( 'No plugins found', 'zn_framework' ) ) . '</p>';
}
elseif(isset($plugins['error'])){
	?>
	<div class="notice notice-error"><p><?php echo $plugins['error'];?></p></div>
	<?php
}
else
{
	?>
	<ul class="zn-extensions-list cf">
		<?php
		foreach ( $plugins as $plugin )
		{
			$plugin_status = ZnAddonsManager()->get_plugin_status( $plugin[ 'slug' ] );
			$is_private = isset($plugin['private']) && $plugin['private'] ? 'is-private' : '';
			$button = '<a class="zn-extension-button "
						data-action="' . esc_attr($plugin_status[ 'action' ]) . '"
						data-status="' . esc_attr($plugin_status[ 'status' ]) . '"
						data-source-type="' . ( isset( $plugin[ 'source_type' ] ) ? esc_attr( $plugin[ 'source_type' ] ) : '' ) . '"
						data-nonce="' . wp_create_nonce( 'zn_plugins_nonce' ) . '"
						href="#" data-slug="' . esc_attr($plugin[ 'slug' ]) . '">' . $plugin_status[ 'action_text' ] . '</a>';
			?>
			<li class="zn-extension <?php echo esc_attr($plugin_status[ 'status' ]); ?>" id="<?php echo esc_attr($plugin[ 'slug' ]); ?>">
				<div class="zn-extension-inner <?php echo $is_private; ?>" data-type="<?php echo isset( $plugin[ 'addon_type' ] ) ? esc_attr($plugin[ 'addon_type' ]) : ''; ?>">
					<img src="<?php echo esc_url($plugin[ 'z_plugin_icon' ]); ?>" class="img">

					<div class="zn-extension-info">
						<h4 class="zn-extension-title">
							<?php echo $plugin[ 'name' ]; ?>
							<span class="zn-extension-version"><?php echo __('Latest version: ', 'zn_framework') . $plugin[ 'version' ]; ?></span>
						</h4>
						<span class="zn-extension-status "><?php echo $plugin_status[ 'status_text' ]; ?></span>
						<?php
						if ( isset( $plugin[ 'deprecated' ] ) && isset($plugin[ 'deprecated' ][ 'message' ]) && !empty($plugin[ 'deprecated' ][ 'message' ]))
						{
							echo '<p class="zn-extension-deprecated">' . $plugin[ 'deprecated' ][ 'message' ] . '</p>';
						}
						?>
						<p class="zn-extension-desc"><?php echo $plugin[ 'z_plugin_description' ]; ?></p>
						<?php echo !empty($is_private) ? '<p class="zn-extension-private">PRIVATE PLUGIN.</p>' : ''; ?>

						<p class="zn-extension-author"><cite>By <?php echo $plugin[ 'z_plugin_author' ]; ?></cite></p>

						<p class="zn-extension-ajax-text"></p>
					</div>
					<div class="zn-extension-actions"><?php echo $button; ?></div>
				</div>
			</li>

		<?php } //#! endforeach
		?>
	</ul>
<?php }

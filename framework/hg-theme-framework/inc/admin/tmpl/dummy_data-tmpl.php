<?php if(! defined('ABSPATH')){ return; }

$availableDemos = null;
$blockUI = false;
$stepInstallPlugins = $stepInstallThemeOptions = $stepInstallWidgets = $stepInstallContent = '';

if(ZN_HogashDashboard::isConnected()){
    // Check to see whether or not there is a demo currently installing
    $blockUI = ZN_ThemeDemoImporter::isDemoInstalling();
    if(! $blockUI)
    {
        // Clear any leftovers from a possible previous failed install
        ZN_DemoImportHelper::__cleanup();
        ZN_DemoImportHelper::clearLogFile();
    }

    $stepInstallPlugins = ZN_ThemeDemoImporter::STEP_INSTALL_PLUGINS;
    $stepInstallThemeOptions = ZN_ThemeDemoImporter::STEP_INSTALL_THEME_OPTIONS;
    $stepInstallWidgets = ZN_ThemeDemoImporter::STEP_INSTALL_WIDGETS;
    $stepInstallContent = ZN_ThemeDemoImporter::STEP_INSTALL_CONTENT;

    // Get and display the available demos
    $availableDemos = ZN_HogashDashboard::getAllDemos();
}

?>
<div class="zn-about-dummy-container">

	<audio id="zn-about-dummySounds" preload="auto">
		<source src="<?php echo DEMO_IMPORT_DIR_URL ?>/assets/sounds/served.ogg" type="audio/ogg">
		<source src="<?php echo DEMO_IMPORT_DIR_URL ?>/assets/sounds/served.mp3" type="audio/mpeg">
	</audio>

	<div class="znfb-row">
		<div class="znfb-col-12">

            <?php if($blockUI) { ?>
            <div class="znde-block-ui znfb-col-12">
                <?php _e('A demo is currently installing, you will not be able to install another demo until the previous process completes. As soon as this message disappears, you can install the demo you want.', 'zn_framework'); ?>
            </div>
            <?php } ?>

			<?php
			// if not connected
			if( ! ZN_HogashDashboard::isConnected()){ ?>

				<div class="zn-adminNotice zn-adminNotice-error">
					<h4>Theme Demos are not available because Kallyas theme is not registered!</h4>
					<p>Please go back to step 2 (Theme Registration) and register Kallyas to gain full access to the Demos.</p>
					<p><br/><strong><em>If you still see this message after registering your theme, please reload this page.</em></strong></p>
				</div>

            <?php }
            // if connected
            else {
             ?>
				<div class="znfb-col-8">
					<div class="zn-lead-text">
						<p class="zn-lead-text--larger">Import Sample Data / Demo Content</p>
						<p>* Please know that images, videos and other media, are <strong>not</strong> included.</p>
						<p>** The import process <strong>might take even 10-15 minutes</strong> depending on your web-hosting.</p>
					</div>
				</div>
				<div class="znfb-col-4">
					<a href="#" class="js-refresh-demos zn_admin_button zn-refresh-theme-demos-button" title="Click to refresh demos list" data-nonce="<?php echo wp_create_nonce('refresh_demos_list');?>">Refresh List</a>
				</div>

            <?php } ?>
		</div>
	</div>

	<div class="znfb-row">

		<?php
		if(empty($availableDemos)){
			echo '<div class="znfb-col-12">
				<p>Demos not available.</p>
			</div>';
		}
		else {
			if(is_array($availableDemos) && isset($availableDemos['error'])){
				echo '<div class="zn-adminNotice zn-adminNotice-error">';
				echo '<p>'.__('<strong>An error occurred:</strong> ', 'zn_framework').$availableDemos['error'].' ';
				echo __('Please try again in a few minutes.', 'zn_framework').'</p>';
				echo '</div>';
			}
			// We have data
			else {
				foreach($availableDemos as $demoName => $info)
				{
					// Whether or not the demo is available for installing
					// Default to true, before checking for demo's requirements
					$available = true;
					$is_private = isset($info['private']) && $info['private'] ? 'is-private' : '';
					?>
					<div class="znfb-col-3">
						<div class="zn-about-dummy-wrapper zn-about-box <?php echo $is_private; ?>">
							<div class="zn-about-dummy-image">
								<img src="<?php echo $info['image']; ?>" alt="<?php echo $info['title']; ?>" />
								<div class="zn-about-dummy-details">
									<h4 class="zn-about-dummy-title"><?php echo $info['title']; ?></h4>
									<div class="zn-about-dummy-desc">
										<?php echo $info['desc']; ?>
										<?php echo !empty($is_private) ? '<p class="zn-about-dummy-descPrivate">PRIVATE DEMO.</p>' : ''; ?>
									</div>
								</div>
							</div>

							<?php
							// Check demo's requirements - see demo-config.json
                            if(isset($info['requires']) && !empty($info['requires']))
                            {
                                if(isset($info['requires']['wp_version']) && isset($info['requires']['theme_version']))
                                {
                                    global $wp_version;
                                    $themeInfo = wp_get_theme( get_template() );
                                    $themeVersion = $themeInfo->get('Version');

                                    if ( version_compare($themeVersion, $info['requires']['theme_version'], '<') ){
                                        $available = false;
                                        $unavailable_error = '<p class="zn-import-demo-notice-error">
                                        		<strong>'.__('Unavailable', 'zn_framework').'</strong>
                                        		<small>'.__('This demo is not available <br>for your version of Kallyas theme. Please update Kallyas!', 'zn_framework').'</small></p>';
                                    }
                                    elseif ( version_compare($wp_version, $info['requires']['wp_version'], '<') ){
                                        $available = false;
                                        $unavailable_error = '<p class="zn-import-demo-notice-error">
                                        		<strong>'.__('Unavailable', 'zn_framework').'</strong>
                                        		<small>'.__('This demo is not available for your version of WordPress.', 'zn_framework').'</small></p>';
                                    }
                                }
                            }

							 ?>
							<div class="zn-about-dummy-actions <?php echo isset($unavailable_error) && !$available ? 'has-error':''; ?>">
                            <?php

                                if(isset($unavailable_error) && !$available){
                                	echo $unavailable_error;
                                }

								if($available)
								{
									?>
                                    <?php if(!$blockUI) { ?>
									<a href="#" class="zn-about-dummy-button zn-about-dummy-install"
									   data-demo-name="<?php echo $demoName;?>"><?php _e('Install', 'zn_framework');?></a>
                                    <?php } ?>
									<a href="<?php echo $info['demo_url']; ?>"
									   class="zn-about-dummy-button zn-about-dummy-green"
									   target="_blank"><?php _e('Preview', 'zn_framework');?></a>
									<?php
								}
								?>
							</div>
						</div>
					</div>
					<?php
				}
			}
		}
		?>
	</div>
</div>
<div class="zn-install-popup-template">
	<div class="zn-install-popup-inner">
		<div class="zn-install-popup-header">
			<h4 class="zn-install-popup-title"></h4>
			<a href="#" class="zn-install-popup-close-button"></a>
		</div>
		<div class="zn-install-popup-content">
			<div class="zn-install-popup-content-inner">
				<div class="zn-install-popup-side">
					<img class="zn-demo-image" src=""/>
				</div>
				<div class="zn-install-popup-side">

					<div class="zn-installation-customize">
						<div>
							<h3><?php _e('Customize your installation', 'zn_framework');?></h3>
						</div>
						<div>
							<label>
								<?php $title = __('Install recommended plugins', 'zn_framework'); ?>
								<input type="checkbox" id="zn_dummy_data_install_plugins"
									   value="1"
									   data-title="<?php echo $title;?>"
									   data-step="<?php echo $stepInstallPlugins;?>"/>
								<span><?php echo $title;?></span>
							</label>
						</div>
						<div>
							<label>
								<?php $title = __('Import theme options', 'zn_framework'); ?>
								<input type="checkbox" id="zn_dummy_data_import_theme_options"
									   value="1"
									   data-title="<?php echo $title;?>"
									   data-step="<?php echo $stepInstallThemeOptions;?>"/>
								<span><?php echo $title;?></span>
							</label>
						</div>
						<div>
							<label>
								<?php $title = __('Install widgets', 'zn_framework'); ?>
								<input type="checkbox" id="zn_dummy_data_import_widgets"
									   value="1"
									   data-title="<?php echo $title;?>"
									   data-step="<?php echo $stepInstallWidgets;?>"/>
								<span><?php echo $title;?></span>
							</label>
						</div>
						<div>
							<label>
								<?php $title = __('Install content', 'zn_framework'); ?>
								<input type="checkbox" id="zn_dummy_data_import_content"
									   value="1"
									   data-title="<?php echo $title;?>"
									   data-step="<?php echo $stepInstallContent;?>"/>
								<span><?php echo $title;?></span>
							</label>
						</div>
						<!--// Other options should follow the above template -->
					</div><!-- /.zn-installation-customize -->

					<div id="zn-import-process-wrapper" class="zn-import-process-wrapper">
						<p><small>* May take up to 5-10 minutes or longer, depending on web hosting.</small></p>
						<p>
							<span id="zn-import-ajax-progress" class="zn-import-ajax-progress">
								<strong class="zn-import-ajax-progressTitle"><?php _e('Progress:', 'zn_framework'); ?> <span id="zn-import-progress-status-text" class="zn-import-progress-status-text"></span></strong>
								<span id="zn-import-progress-bar" class="zn-import-progress-bar"></span></span>
							<span id="zn-import-steps"></span>
						</p>
					</div>
				</div>
			</div>
		</div>
		<div class="zn-install-popup-footer">
			<div class="zn-install-popup-content-inner">
				<div>
					<a href="#" class="zn-about-dummy-install zn-popup-install-demo-button"><?php _e('Install', 'zn_framework'); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>

<?php

class ZnHgFw_Html_Tabbed_Form extends ZnHgFw_BaseFieldType{

	function render( $value ) {

		$menu = '';
		$i = 0;
		foreach ($option['menu'] as $menu_id => $menu_args) {
			$cls = $i === 0 ? 'active' : '';
			$menu .= '<li class="'.$cls.'" id="'.$menu_args['id'].'"><a href="#zn_tab_'.$menu_args['id'].'">'.$menu_args['name'].'</a></li>';
			$i++;
		}

		ob_start();
		?>
			<div class="znfb-row znopt_tabbed_group">
				<div class="znopt_tabbed_menu_container">
					<ul class="znopt_tabbed_menu">
						<?php echo $menu; ?>
					</ul>
				</div>
				<div class="znopt_tabbed_content">
					<?php
						$i = 0;
						foreach ( $option['menu'] as $menu_id => $menu_args) {
							$cls = $i === 0 ? 'active' : '';
							echo '<div class="znopt_single_tab '.$cls.'" id="zn_tab_'.$menu_args['id'].'">';
								if( empty( $menu_args['options'] ) ) continue;
								foreach ( $menu_args['options'] as $key => $single_option) {
									$saved_value = get_post_meta( $post->ID, $single_option['id'] , true);
									if(  !empty($saved_value) ) {
										$single_option['std'] = $saved_value;
									}

									echo $this->zn_render_single_option($single_option);
								}

							echo '</div>';
							$i++;
						}

					?>

				</div>
			</div>
		<?php
		$output = ob_get_clean();
		return $output;

	}

}

return new ZnHgFw_Html_Tabbed_Form();

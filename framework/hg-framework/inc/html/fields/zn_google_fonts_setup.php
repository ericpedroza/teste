<?php

class ZnHgFw_Html_Google_Fonts_Setup extends ZnHgFw_BaseFieldType{

	function __construct(){
		add_action( 'wp_ajax_znhgfw_html_google_font_add', array( $this, 'ajaxCallback' ) );
	}

	function ajaxCallback(){

		if ( empty( $_POST[ 'zn_elem_type' ] ) )
		{
			return;
		}

		$value = json_decode( base64_decode( $_POST[ 'zn_json' ] ), true );
		if ( isset( $_POST[ 'selected_font' ] ) )
		{
			$value[ 'selected_font' ] = $_POST[ 'selected_font' ];
		}
		$value[ 'dynamic' ] = true;

		echo $this->render( $value );

		die();
	}

	function render($option) {

		$output = '';

		include_once( FW_PATH . '/hg-framework/inc/font-manager/google_fonts.php' );
		// global $all_google_fonts;
		$all_fonts = $all_google_fonts;

		if ( empty ( $option['dynamic'] )  ) {

			$output .= '<select class="zn_input zn_input_select">';
				$output .= '<option>Please select a font</option>';
				if(! empty($all_fonts)) {
					foreach ( $all_fonts as $key => $font ) {
						$output .= '<option value="' . $font['family'] . '">' . $font['family'] . '</option>';
					}
				}
			$output .= '</select>';

			$output .= '<a class="button button-primary button-large zn_add_gfont" data-zn_data=\''.base64_encode( json_encode($option) ).'\' data-type="'.$option['id'].'" href="#">Add Font</a>';

			$number_of_elements = 0;
			$uid = '';

			// Count all default values
			if ( is_array ( $option['std'] ) )
			{
				$number_of_elements = count($option['std']);
			}

			$output .= '<div class="zn_group_inner zn_group_container zn_pb_group_content zn_google_fonts_holder" data-baseid="'.$option['id'].'">';
				if ( !empty($option['std']) ) {
					foreach ( $option['std'] as $key => $font ) {

						$uid = zn_uid();
						$selected_font = $font['font_family'];
						$font_family = $key;

						$output .= '<div class="zn_group">';
						$output .= '<div class="zn_group_header zn_gradient">';
							$output .= '<h4>'.$selected_font.'</h4>';
							// START ACTIONS
							$output .= '<div class="zn_group_actions">';
								// DELETE BUTTON
								$output .= '<a class="zn_remove"><span data-toggle="tooltip" data-title="Delete" class="zn_icon_trash"></span></a>';
								// Edit button
								$output .= '<a class="zn_modal_trigger no-scroll" href="#'.$uid.'" data-modal_title="'.$selected_font.' font options"><span data-toggle="tooltip" data-title="Edit" class="zn_icon_edit no-scroll"></span></a>';
							$output .= '</div>'; // END GROUP ACTIONS

						$output .= '</div>'; // END GROUP HEADER

					$output .= '<div id="'.$uid.'" class="zn-modal-form zn-modal-group-form zn_hidden no-scroll">';

						// $output .= '<h3>'.$selected_font.'</h3>';
						$custom_css_class = '.ff-'.strtolower(str_replace(' ', '_', $font['font_family'])).' { }';

						$option['subelements'] = array(
							array(
								'id'          => 'font_family',
								'name'        => 'Font Family',
								'type'        => 'hidden',
								'class'		  => 'zn_hidden'
							),
							array(
								'id'          => 'font_class',
								'name'        => 'Font CSS Class',
								'description'=> 'This is the custom CSS class selector for this font-family: <code>'.$custom_css_class.'</code> or straight use the css property: <code>font-family:"'.$font['font_family'].'";</code> .',
								'type'        => 'zn_title',
								'class'		  => 'zn_full'
							),
							array(
								'id'          => 'font_variants',
								'name'        => 'Font variants',
								'description' => 'Here you can select the font variants you want to load.',
								'type'        => 'checkbox',
								'options' => $all_fonts[$selected_font]['variants'],
								'class'		=> 'zn_full'
							)
						);

						foreach ( $option['subelements'] as $key => $value ) {

							// SET THE DEFAULT VALUE
							if( is_array ( $option['std'] ) && isset ( $option['std'][$font_family][$value['id']] ) ) {
								$value['std'] = $option['std'][$font_family][$value['id']];
							}

							// Set the proper id
							$value['id'] = $option['id'].'['.$font_family.']['.$value['id'].']';

							// Generate the options
							$output .= ZNHGFW()->getComponent('html')->zn_render_single_option($value);
						}

						$output .= '</div>'; // End Modal

					$output .= '</div>'; // Close zn_group

					}
				}


			$output .= '</div>'; // END .zn_group_container

		}
		else {

			$uid = zn_uid();
			$i = 0;
			$selected_font = $option['selected_font'];
			$font_family = str_replace(' ', '+', $selected_font);

			$output .= '<div class="zn_group">';
			$output .= '<div class="zn_group_header zn_gradient">';
				$output .= '<h4>'.$all_fonts[$selected_font]['family'].'</h4>';
				// START ACTIONS
				$output .= '<div class="zn_group_actions">';
					// DELETE BUTTON
					$output .= '<a class="zn_remove"><span data-toggle="tooltip" data-title="Delete" class="zn_icon_trash"></span></a>';
					// Edit button
					$output .= '<a class="zn_modal_trigger no-scroll" href="#'.$uid.'" data-modal_title="'.$all_fonts[$selected_font]['family'].' font options"><span data-toggle="tooltip" data-title="Edit" class="zn_icon_edit no-scroll" ></span></a>';
				$output .= '</div>'; // END GROUP ACTIONS

			$output .= '</div>'; // END GROUP HEADER

		$output .= '<div id="'.$uid.'" class="zn-modal-form zn-modal-group-form zn_hidden no-scroll">';

				$option['subelements'] = array(
					array(
						'id'          => 'font_family',
						'name'        => 'Font Family',
						'type'        => 'hidden',
						'std'		  =>  $selected_font,
						'class'		  => 'zn_hidden'
					),
					array(
						'id'          => 'font_variants',
						'name'        => 'Font variants',
						'description' => 'Here you can select the font variants you want to load.',
						'type'        => 'checkbox',
						'options' => $all_fonts[$selected_font]['variants'],
						'class'		=> 'zn_full'
					)
				);

				foreach ( $option['subelements'] as $key => $value ) {

					// Set the proper id
					$value['id'] = $option['id'].'['.$font_family.']['.$value['id'].']';

					// Generate the options
					$output .= ZNHGFW()->getComponent('html')->zn_render_single_option($value);
				}

		$output .= '</div>';

			$output .= '</div>';
		}

		return $output;
	}
}

return new ZnHgFw_Html_Google_Fonts_Setup();

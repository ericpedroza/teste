<?php

class ZnHgFw_Html_Group extends ZnHgFw_BaseFieldType{

	function __construct(){
		add_action( 'wp_ajax_znhgfw_html_group_add', array( $this, 'ajaxCallback' ) );
	}

	function ajaxCallback(){

		if ( empty( $_POST[ 'zn_elem_type' ] ) )
		{
			return;
		}

		$value = json_decode( base64_decode( $_POST[ 'zn_json' ] ), true );
		$value[ 'dynamic' ] = true;

		echo $this->render( $value );

		die();
	}

	function render( $option ){

		$output = '';

		$uid = '';
		$has_std_elements = false;

		// Count all default values
		if ( is_array ( $option['std'] ) )
		{
			$number_of_elements = count( $option['std'] );
		}
		elseif( isset( $option['dynamic'] ) ){
			$number_of_elements = 1;
		}
		else {
			$number_of_elements = 0;
		}

		$extra_button_class = '';
		$max_items = isset( $option['max_items'] ) ? 'data-max_items="'.$option['max_items'].'"' : '';
		if( !empty( $max_items ) ){
			if ( $number_of_elements >= $option['max_items'] ){
				$extra_button_class = 'zn_add_button_inactive';
			}
		}

		if ( !isset( $option['dynamic'] ) ){
			$output .= '<div class="zn_group_inner zn_group_container zn_pb_group_content '.$extra_button_class.'" data-baseid="'.$option['id'].'">';
		}

		// ADD THE STD OPTIONS THAT CANNOT BE DELETED
		if ( !empty( $option['default_std'] ) && !isset( $option['dynamic'] ) ) {

			$number_of_std_elements = count( $option['default_std'] );
			$has_std_elements = true;

			for ( $i = 0; $i < $number_of_std_elements; $i++ ) {

				$uid = zn_uid();

				// GET THE ELEMENT TITLE
				$title = isset( $option['default_std'][$i]['name'] ) ? '( Default ) '.$option['default_std'][$i]['name'] : '';

				// SET CUSTOM STD IF SET
				if ( !empty( $option['default_std'][$i]['std'] ) && !empty( $option['std'][$i] ) ){
					$option['std'][$i] = array_merge( $option['default_std'][$i]['std'] , $option['std'][$i] );
				}
				elseif( !empty( $option['default_std'][$i]['std'] ) ){
					$option['std'][$i] = $option['default_std'][$i]['std'];
				}

				$output .= '<div class="zn_group">';

				$output .= '<div class="zn_group_header zn_gradient">';
					$output .= '<h4>'.$title.'</h4>';
					// START ACTIONS
					$output .= '<div class="zn_group_actions">';
						// Clone button
						$output .= '<a class="zn_clone_button zn_icon_clone" data-clone="clone"></a>';
						// Edit button
						$output .= '<a class="zn_modal_trigger zn_icon_edit no-scroll" href="#'.$uid.'" data-modal_title="Element options"></a>';
					$output .= '</div>'; // END zn_group_actions
				$output .= '</div>'; // END zn_group_header

				$output .= '<div id="'.$uid.'" class="zn-modal-form zn-modal-group-form zn_hidden no-scroll" >';

					if( isset( $option['subelements']['has_tabs'] ) ) {

						unset( $option['subelements']['has_tabs'] );

						$output .= '<div class="zn-tabs-container">';
							$output .= '<div class="zn-options-tab-header">';
								$tab_num = 0;
								foreach ( $option['subelements'] as $key => $tab) {
									$cls = '';
									if ( $tab_num == 0 ) { $cls = 'zn-tab-active'; }
									$output .= '<a href="#" class="'.$cls.'" data-zntab="'.$key.'">'.$tab['title'].'</a>';
									$tab_num++;
								}

							$output .= "</div>";

							$tab_num = 0;
							foreach ( $option['subelements'] as $key => $tab ) {

								$cls = '';
								if ( $tab_num == 0 ) { $cls = 'zn-tab-active'; }
								$output .= '<div class="zn-options-tab-content zn-tab-key-'.$key.' '.$cls.'">';

									foreach ( $tab['options'] as $key => $value ) {
										$value['is_in_group'] = true;

										// SET THE DEFAULT VALUE
										if( is_array ( $option['std'] ) && isset ( $option['std'][$i][$value['id']] ) ) {
											$value['std'] = $option['std'][$i][$value['id']];
										}

										// Set the proper id
										$value['id'] = $option['id'].'['.$i.']['.$value['id'].']';
										$value['dependency_id'] = $option['id'].'['.$i.']';

										// Generate the options
										$output .= ZNHGFW()->getComponent('html')->zn_render_single_option($value);
									}

								$output .= "</div>";
								$tab_num++;
							}
						$output .= '</div>';
						$option['subelements']['has_tabs'] = true;
					}
					else{
						foreach ($option['subelements'] as $key => $value) {

							$value['is_in_group'] = true;

							// SET THE DEFAULT VALUE
							if( is_array ( $option['std'] ) && isset ( $option['std'][$i][$value['id']] ) ) {
								$value['std'] = $option['std'][$i][$value['id']];
							}

							// Set the proper id
							$value['id'] = $option['id'].'['.$i.']['.$value['id'].']';
							$value['dependency_id'] = $option['id'].'['.$i.']';

							// Generate the options
							$output .= ZNHGFW()->getComponent('html')->zn_render_single_option($value);
						}
					}



				$output .= '</div>';

				$output .= '</div>'; // Close zn_group

			}

		}

		// IF WE HAVE STANDARD ELEMENTS, CHANGE THE START VALUE
		if ( $has_std_elements ) {
			$start = count( $option['default_std'] );
		}
		else {
			$start = 0;
		}

		// We do not have any fixed elemenets
		for ( $i = $start; $i < $number_of_elements; $i++ ) {

			$options = array();
			$uid = zn_uid();

			// GET THE ELEMENT TITLE IF SUPPORTED
			if( isset($option['element_title']) && !isset( $option['dynamic'] ) && isset( $option['std'][$i][$option['element_title']] ) ) {

				$title = sanitize_text_field( $option['std'][$i][$option['element_title']] );

				if ( strlen( $title ) > 45 ){
					$title = substr( $title , 0 , 45 ) .'...';
				}

				if( $option['element_title'] == 'zn_color' ){
					$title .= '<span style="background-color:'.$title.'" class="zn-color-preview"></span>';
				}

			}
			// If empty show title or #xx
			else{
				if(isset($option['element_title'])){

					$title =  $option['element_title'];

					if( $title == 'zn_color' ){
						$title .= '<span style="background-color:'.$title.'" class="zn-color-preview"></span>';
					}

				} else {
					$title = '#'.($i+1);
				}
			}

			// Added image preview of list
			if( isset($option['element_img']) && !isset( $option['dynamic'] ) && isset( $option['std'][$i][$option['element_img']] ) ) {
				$img = sanitize_text_field( $option['std'][$i][$option['element_img']] );
				if(!empty($img)){
					$title .= '<span style="background-image:url('.$img.')" class="zn-dyn-image-preview"></span>';
				}
			}

			$output .= '<div class="zn_group">';

				$output .= '<div class="zn_group_header zn_gradient">';
					$output .= '<h4>'.$title.'</h4>';
					// START ACTIONS
					$output .= '<div class="zn_group_actions">';

						$output .= '<span class="zn_group_actions-counter">'.($i+1).'</span>';

						// DELETE BUTTON
						$output .= '<a class="zn_remove" data-tooltip="'.__( 'Delete','zn_framework' ).'"><span class="zn_icon_trash"></span></a>';

						if ( !isset( $option['sortable'] ) || $option['sortable'] == 'true' ) {
							// RE-ORDER BUTTON
							$output .= '<a class="zn_group_handle" data-tooltip="'.__( 'Move','zn_framework' ).'"><span class="zn_icon_order"></span></a>';
						}

						// Clone button
						$output .= '<a class="zn_clone_button"  data-clone="clone" data-tooltip="'.__( 'Clone','zn_framework' ).'"><span class="zn_icon_clone"></span></a>';
						// Edit button
						$output .= '<a class="zn_modal_trigger zn_icon_edit no-scroll" href="#'.$uid.'" data-modal_title="Element options" data-tooltip="'.__( 'Edit','zn_framework' ).'"><span class="zn_icon_edit"></span></a>';
					$output .= '</div>'; // END zn_group_actions
				$output .= '</div>'; // END zn_group_header

				$output .= '<div id="'.$uid.'" class="zn-modal-form zn-modal-group-form zn_hidden no-scroll" >';

					if( isset( $option['subelements']['has_tabs'] ) ) {

						unset( $option['subelements']['has_tabs'] );

						$output .= '<div class="zn-tabs-container">';
							$output .= '<div class="zn-options-tab-header">';
								$tab_num = 0;
								foreach ( $option['subelements'] as $key => $tab) {
									$cls = '';
									if ( $tab_num == 0 ) { $cls = 'zn-tab-active'; }
									$output .= '<a href="#" class="'.$cls.'" data-zntab="'.$key.'">'.$tab['title'].'</a>';
									$tab_num++;
								}

							$output .= "</div>";

							$tab_num = 0;
							foreach ( $option['subelements'] as $key => $tab ) {

								$cls = '';
								if ( $tab_num == 0 ) { $cls = 'zn-tab-active'; }
								$output .= '<div class="zn-options-tab-content zn-tab-key-'.$key.' '.$cls.'">';

									foreach ( $tab['options'] as $key => $value ) {
										$value['is_in_group'] = true;

										// SET THE DEFAULT VALUE
										if( is_array ( $option['std'] ) && isset ( $option['std'][$i][$value['id']] ) ) {
											$value['std'] = $option['std'][$i][$value['id']];
										}

										// Set the proper id
										$value['id'] = $option['id'].'['.$i.']['.$value['id'].']';
										$value['dependency_id'] = $option['id'].'['.$i.']';

										// Generate the options
										$output .= ZNHGFW()->getComponent('html')->zn_render_single_option($value);
									}

								$output .= "</div>";
								$tab_num++;
							}
						$output .= '</div>';

						// Needs to be added because it's unset at the begginning
						$option['subelements']['has_tabs'] = true;
					} else {

						foreach ($option['subelements'] as $key => $value) {

							$value['is_in_group'] = true;

							// SET THE DEFAULT VALUE
							if( is_array ( $option['std'] ) && isset ( $option['std'][$i][$value['id']] ) ) {
								$value['std'] = $option['std'][$i][$value['id']];
							}

							// Set the proper id
							$value['id'] = $option['id'].'['.$i.']['.$value['id'].']';
							$value['dependency_id'] = $option['id'].'['.$i.']';

							// Generate the options
							$output .= ZNHGFW()->getComponent('html')->zn_render_single_option($value);
						}
					}


				$output .= '</div>'; // zn-modal-form
			$output .= '</div>'; // Close zn_group

		}
		if ( !isset( $option['dynamic'] ) ) {
			$output .= '</div>'; // Close zn_innter
			// Clear the std option
			$option['std'] = '';
			$output .= '<div class="zn_add_button zn-btn-done" '.$max_items.' data-zn_data=\''.base64_encode( json_encode( $option ) ).'\' data-type="'.$option['id'].'">Add more</div>';
		}


		return $output;
	}


}

return new ZnHgFw_Html_Group();

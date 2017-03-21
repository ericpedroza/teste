<?php

class ZnHgFw_Html_KallyasForm extends ZnHgFw_BaseFormType{

	var $version;
	var $pages;
	var $slug;

	private function _render(){

		$form = $this->_beforeForm();
		$form .= $this->zn_render_page_options();
		$form .= $this->_afterForm();

		return $form;
	}

	function zn_render_page_options() {

		$output = '';
		$i = 0;

		$output .= '<div class="tab-content">';

		foreach ( $this->options as $slug => $options ) {

			if ( $i === 0 ) {
				$output .= '<div class="tab-pane active" id="'.$slug.'">';
			}
			else {
				$output .= '<div class="tab-pane" id="'.$slug.'">';
			}

			foreach ( $options as $key => $option ) {

				// Set-up the STD for normal options
				$saved_value = zget_option( $option['id'], $this->slug );

				if ( !empty( $saved_value ) || $saved_value === '0' ) {
					$option['std'] = $saved_value;
				}

				// RENDER SINGLE OPTION
				$output .= ZNHGFW()->getComponent('html')->zn_render_single_option($option);

			}

			$output .= '</div>'; // Close tab-pane
			$i++;
		}

		$output .= '</div>'; // END tab-content

		return $output;

	}

	function _beforeForm(){
		$logo_path = CHILD_BASE.'/images/admin_logo.png';
		$logo = THEME_BASE_URI.'/images/admin_logo.png';
		if( file_exists( $logo_path ) ){
			$logo = CHILD_BASE_URI.'/images/admin_logo.png';
		}

		$output = '<div id="zn_theme_admin" class="zn_theme_admin">';
		$output .= '<form id="zn_options_form" class="zn_container" action="#" method="post" >';
		$output .= '<div class="zn_inner zn_row">';
		$output .= '<div class="zn_span2 zn_sidebar">';
		$output .= '<div class="zn_logo">';
		$output .= '<img src="'.$logo.'"/>';
		$output .= '<span>'.__('Version: ', 'zn_framework') .'<strong>'. $this->version.'</strong></span>';
		$output .= '</div>';

			$output .= $this->zn_get_sidebar_menu();

		$output .= '</div>';		// END zn_options_container
		//$output .= '<div class="zn_row">';
		$output .= '<div class="zn_span10 zn_page_content">';

		/* START THE HEADER */
		$output .= '<div class="zn_action zn_header clearfix">';
		$output .= '<a class="zn_admin_button zn_save" href="#">Save options</a>';
		$output .= '</div>'; // END zn_header


		return $output;
	}

	function _afterForm(){
		$output = '';

		/* START THE HEADER */
		$output .= '<div class="zn_action zn_header clearfix">';
		$output .= '<a class="zn_admin_button zn_save" href="#">Save options</a>';
		$output .= '</div>'; // END zn_header

		$output .= '</div>';
		$output .= '</div>';
		//$output .= '</div>';		// END zn_inner
		$output .= '<div class="zn_hidden">';

		$output .= '<input type="hidden" name="zn_option_field" value="'.$this->slug.'">';
		$output .= '<input type="hidden" name="action" value="zn_ajax_callback">';
		$output .= '<input type="hidden" name="zn_action" value="zn_save_options">';

		$output .= '</div>';
		$output .= '</form>';	// END zn_options_form
		$output .= '</div>'; // END zn_theme_admin

		return $output;
	}

	function zn_get_sidebar_menu() {

		$output = '<ul class="wp-ui-primary nav-stacked">';

			foreach ( $this->pages[$this->slug]['submenus'] as $key => $page ) {

				if ($key === 0) {
					$output .= '<li class="wp-ui-highlight" id="'.$page['slug'].'_menu_item"><a href="#'.$page['slug'].'" data-toggle="tab">'.$page['title'].'</a></li>';
				}
				else {
					$output .= '<li id="'.$page['slug'].'_menu_item"><a href="#'.$page['slug'].'" data-toggle="tab">'.$page['title'].'</a></li>';
				}
			}

		$output .= '</ul>';

		return $output;

	}

	function render(){
		// Will output the option content
		return $this->_render();
	}

}

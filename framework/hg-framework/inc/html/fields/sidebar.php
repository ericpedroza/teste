<?php

class ZnHgFw_Html_Sidebar extends ZnHgFw_BaseFieldType{

	public $_sidebars = array();

	function __construct(){
		$this->_getSidebars();
	}

	private function _getSidebars(){
		$sidebars = array();
		// Add the unlimited sidebars
		//  TODO : ZNFWTODO add a filter and remove zget_option
		$unlimited_sidebars = zget_option( 'unlimited_sidebars' , 'unlimited_sidebars' );
		if ( is_array( $unlimited_sidebars ) ) {
			foreach ($unlimited_sidebars as $key => $sidebar) {
				$sidebars[zn_sanitize_widget_id($sidebar['sidebar_name'])] = $sidebar['sidebar_name'];
			}
		}

		$this->_sidebars = $sidebars;
	}

	function render( $value ) {

		if( !empty( $value['supports']['default_sidebar'] ) ){
			$sidebars = array( $value['supports']['default_sidebar'] => 'Default Sidebar' );
		}
		else{
			$sidebars = array( 'default_sidebar' => 'Default Sidebar' );
		}

		if( is_array( $this->_sidebars ) ){
			$sidebars = array_merge( $sidebars, $this->_sidebars );
		}

		// Override default sidebar options
		if( !empty( $value['supports']['sidebar_options'] ) ){
			$sidebar_options = $value['supports']['sidebar_options'];
		}
		else{
			$sidebar_options = array( 'sidebar_right' => 'Right sidebar' , 'sidebar_left' => 'Left sidebar' , 'no_sidebar' => 'No sidebar' );
		}

		if ( !is_array( $value['std'] ) ) { $value['std'] = array(); }
		if ( !isset ( $value['std']['layout'] ) ) { $value['std']['layout'] = ''; }
		if ( !isset ( $value['std']['sidebar'] ) || empty( $value['std']['sidebar'] ) ) { $value['std']['sidebar'] = ''; }

		$output = '';
		$output .= '<div class="zn_row">';

		// Sidebar layout
		$output .= '<div class="zn_span4">';
		$output .= '<label for="'. $value['id'] .'_layout">Sidebar layout</label><select class="select zn_input zn_input_select" name="'.$value['id'].'[layout]" id="'. $value['id'] .'_layout">';
		foreach ( $sidebar_options as $select_ID => $option ) {
			$output .= '<option id="' . $select_ID . '" value="'.$select_ID.'" ' . selected( $value['std']['layout'], $select_ID, false) . ' >'.$option.'</option>';
		}
		$output .= '</select>';
		$output .= '</div>';

		// Sidebar select
		$output .= '<div class="zn_span4">';
		$output .= '<label for="'. $value['id'] .'_sidebar">Sidebar select</label><select class="select zn_input zn_input_select" name="'.$value['id'].'[sidebar]" id="'. $value['id'] .'_sidebar">';
		foreach ( $sidebars as $select_ID => $option ) {
			$output .= '<option id="' . $select_ID . '" value="'.$select_ID.'" ' . selected($value['std']['sidebar'], $select_ID, false) . ' >'.$option.'</option>';
		}
		$output .= '</select>';
		$output .= '</div>';

		$output .= '</div>';

		return $output;

	}

}

return new ZnHgFw_Html_Sidebar();

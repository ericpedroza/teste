<?php

class ZnHgFw_Html_Ajax_Call extends ZnHgFw_BaseFieldType{
	function render($option) {

		$output = '<div class="'.$option['ajax_call_setup']['action'].'_btn zn_admin_button">'.$option['ajax_call_setup']['button_text'].'</div>';
		$output .= '<div class="'.$option['ajax_call_setup']['action'].'_msg_container"></div>';

		return $output;
	}
}

return new ZnHgFw_Html_Ajax_Call();

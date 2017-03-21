<?php

class ZnHgFw_Html_Zn_Message extends ZnHgFw_BaseFieldType{
		function render($option) {

			$message_type = ! empty( $option['supports'] ) ? $option['supports'] : 'ok';
			$output = '<div class="znhtml_message znhtml_message_'.$message_type.'">';
				$output .= '<p>'.$option['name'].'</p>';
				$output .= '<p>'.$option['description'].'</p>';
			$output .= '</div>';

			return $output;
		}




}

return new ZnHgFw_Html_Zn_Message();

<?php

class ZnHgFw_Html_Slider extends ZnHgFw_BaseFieldType{

		function render($option) {

			$step = !empty($option['helpers']['step']) ? $option['helpers']['step'] : '';

			$output  = '<div class="zn_slider clearfix">';
			$output .= '<input type="number" class="zn_input zn_input_text wp-slider-input" name="'.$option['id'].'" value="'.(int)$option['std'].'" min="'.$option['helpers']['min'].'" step="'.$step.'" max="'.$option['helpers']['max'].'" >';
			$output .= '<div class="wp-slider slider-range-max" data-min="'.$option['helpers']['min'].'" data-step="'.$step.'" data-max="'.$option['helpers']['max'].'" data-value="'.(int)$option['std'].'"></div>';
			$output .= '</div>';

			return $output;
		}

}

return new ZnHgFw_Html_Slider();

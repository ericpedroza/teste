<?php

class ZnHgFw_Html_Buttons extends ZnHgFw_BaseFieldType{

		function render($option) {

			$output = '';

			if ( $option['supports'] == 'Checkboxes' ) {
				// Checkboxes

			}
			else {

				// Radios
				$i = 0;
				foreach ( $option['options'] as $key => $soption ) {
					$output .= '<input class="zn_buttons zn_input" type="radio" '. checked( $option['std'] , $key,false) .' id="'.$option['id'].'_'.$i.'" name="'.$option['id'].'" value="'.$key.'" /><label for="'.$option['id'].'_'.$i.'">'.$soption.'</label>';
					$i++;
				}
			}

			return $output;
		}
}

return new ZnHgFw_Html_Buttons();

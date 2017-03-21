<?php

class ZnHgFw_Html_Hidden extends ZnHgFw_BaseFieldType{
	
		function render($option) {
			return '<input type="hidden" name="'.$option['id'].'" value="'.$option['std'].'">';
		}


}

return new ZnHgFw_Html_Hidden();

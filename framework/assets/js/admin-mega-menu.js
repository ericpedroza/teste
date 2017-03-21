(function($)
{
	"use strict";

	$(document).on('change', '.znkl-mega-menu-enable', function( e ){
		var checked = $(this).is(':checked'),
			menu_container = $(this).closest('.menu-item-settings'),
			smart_area_option = menu_container.find('.field-enable-mega-menu-smart-area');

		if( checked ){
			smart_area_option.slideDown();
		}
		else{
			smart_area_option.slideUp();
		}
	})
	$('.znkl-mega-menu-enable').trigger('change');

})(jQuery)

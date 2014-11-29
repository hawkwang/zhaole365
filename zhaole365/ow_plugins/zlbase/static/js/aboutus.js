$(document).ready(function() {

	$(".ow_header").hide();
    $(".ow_page_container").hide();

});

(function( $ ) {'use strict';
	var _elements = {},
	_methods = {
			showAboutus: function()
	        {
	            _elements.aboutus.insertAfter(_elements.header);
	        },
	};

	window.aboutUs = Object.defineProperties({},
	    {
	        init: {
	            value: function()
	            {
	                $.extend(_elements, {
	                    header: $(document.getElementById('zl_ow_site_panel')),
	                    aboutus: $(document.getElementById('aboutus_content')),
	                });
	                
	            	_methods.showAboutus();
	                return;
	            }
	        }
	    });

})(jQuery);
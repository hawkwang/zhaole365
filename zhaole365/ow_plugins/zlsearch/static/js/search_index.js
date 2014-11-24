$(document).ready(function() {
	//init();
	
	// search bar section
	$("#id-simple-location").click(function(event) {
		$("#simple-location").toggle();
		//$("#simple-location").hide();
		$("#simple-radius").hide();
		$("#simple-timerange").hide();
	});
	
	$("#id-simple-radius").click(function(event) {
		$("#simple-radius").toggle();
		$("#simple-location").hide();
		//$("#simple-radius").hide();
		$("#simple-timerange").hide();
	});
	
	$("#id-simple-timerange").click(function(event) {
		$("#simple-timerange").toggle();
		$("#simple-location").hide();
		$("#simple-radius").hide();
		//$("#simple-timerange").hide();
	});
	
	
	// 地点 FIXME － 扩展到全国城市
	$("#simple-location li a").click(function(event) {

		$("#id-simple-location span.value").text($(this).attr('data-copy'));
		$("#id-simple-location").attr('data-value',$(this).attr('data-value'));
		$("#id-simple-location").attr('data-areacode',$(this).attr('data-areacode'));
		
		$("#simple-location").toggle();
		event.preventDefault();
	});
	
	// 获得距离范围
	$("#simple-radius li a").click(function(event) {
		
		$("#id-simple-radius span.value").text($(this).attr('data-copy'));
		$("#id-simple-radius").attr('data-value',$(this).attr('data-value'));
	    
		$("#simple-radius").toggle();
		event.preventDefault();
	});
	
	// 获得时间范围
	$("#simple-timerange li a").click(function(event) {

		$("#id-simple-timerange span.value").text($(this).attr('data-copy'));
		$("#id-simple-timerange").attr('data-value',$(this).attr('data-value'));

		$("#simple-timerange").toggle();
		event.preventDefault();
	});
	
});

function init()
{
    //获取要定位元素距离浏览器顶部的距离
	var navH = $("#findNavBar").offset().top;
	//滚动条事件
	$(window).scroll(function() {
		//获取滚动条的滑动距离
		var scroH = $(this).scrollTop();
		//滚动条的滑动距离大于等于定位元素距离浏览器顶部的距离，就固定，反之就不固定
		if (scroH >= navH) {
			$("#findNavBar").css({
				"position" : "fixed",
				"top" : 0
			});
		} else if (scroH < navH) {
			$("#findNavBar").css({
				"position" : "relative"
			});
		}
	});
}

(function( $ ) {'use strict';
	var _elements = {},
	_methods = {
			showSearchBar: function()
	        {
	            _elements.searchbar.insertAfter(_elements.header);
	        },
	};

	window.searchBar = Object.defineProperties({},
	    {
	        init: {
	            value: function()
	            {
	                $.extend(_elements, {
	                    header: $(document.getElementById('zl_ow_site_panel')),
	                    searchbar: $(document.getElementById('findNavBar')),
	                });
	                
	            	_methods.showSearchBar();
	                return;
	            }
	        }
	    });

})(jQuery);